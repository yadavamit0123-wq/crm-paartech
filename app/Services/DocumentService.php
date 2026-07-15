<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentItem;
use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Tenant;
use App\Support\AmountInWords;
use Illuminate\Support\Facades\Schema;

class DocumentService
{
    public function templates(): array
    {
        return config('document-templates', []);
    }

    public function generateNumber(string $type, int $tenantId): string
    {
        $prefix = match ($type) {
            'quotation' => 'QUO',
            'proforma' => 'PRO',
            'invoice' => 'INV',
            default => 'DOC',
        };

        $year = date('Y');
        $pattern = "{$prefix}-{$year}-%";

        $last = Document::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('document_number', 'like', $pattern)
            ->orderByDesc('id')
            ->value('document_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('%s-%s-%03d', $prefix, $year, $seq);
    }

    public function resolveDocumentNumber(string $type, int $tenantId, ?string $custom = null): string
    {
        if ($custom && trim($custom) !== '') {
            $custom = strtoupper(trim($custom));
            $exists = Document::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('document_number', $custom)
                ->exists();
            if (! $exists) {
                return $custom;
            }
        }

        return $this->generateNumber($type, $tenantId);
    }

    public function calculateLineItem(array $item, bool $isGst, bool $isInterState): array
    {
        $qty = (float) ($item['quantity'] ?? 1);
        $rate = (float) ($item['rate'] ?? 0);
        $discountType = $item['discount_type'] ?? 'fixed';
        $discountPercent = (float) ($item['discount_percent'] ?? 0);
        $discountFixed = (float) ($item['discount_amount'] ?? 0);
        $gstRate = (float) ($item['gst_rate'] ?? 18);

        $gross = $qty * $rate;
        $discountAmount = $discountType === 'percent'
            ? round($gross * ($discountPercent / 100), 2)
            : round(min($discountFixed, $gross), 2);
        $taxable = round(max($gross - $discountAmount, 0), 2);

        $cgstAmount = $sgstAmount = $igstAmount = 0;
        $cgstRate = $sgstRate = $igstRate = 0;

        if ($isGst) {
            if ($isInterState) {
                $igstRate = $gstRate;
                $igstAmount = round($taxable * ($gstRate / 100), 2);
            } else {
                $cgstRate = $sgstRate = $gstRate / 2;
                $cgstAmount = $sgstAmount = round($taxable * ($cgstRate / 100), 2);
            }
        }

        $lineTotal = round($taxable + $cgstAmount + $sgstAmount + $igstAmount, 2);

        return [
            'description' => $item['description'] ?? '',
            'long_description' => $item['long_description'] ?? null,
            'group_name' => $item['group_name'] ?? null,
            'hsn_sac' => $item['hsn_sac'] ?? null,
            'quantity' => $qty,
            'unit' => $item['unit'] ?? 'Nos',
            'rate' => $rate,
            'discount_type' => $discountType,
            'discount_percent' => $discountType === 'percent' ? $discountPercent : 0,
            'discount_amount' => $discountAmount,
            'taxable_amount' => $taxable,
            'gst_rate' => $gstRate,
            'cgst_rate' => $cgstRate,
            'sgst_rate' => $sgstRate,
            'igst_rate' => $igstRate,
            'cgst_amount' => $cgstAmount,
            'sgst_amount' => $sgstAmount,
            'igst_amount' => $igstAmount,
            'line_total' => $lineTotal,
            'sort_order' => $item['sort_order'] ?? 0,
            'image_path' => $item['image_path'] ?? null,
        ];
    }

    public function calculateDocumentTotals(array $lineItems, array $options = []): array
    {
        $subtotal = collect($lineItems)->sum(fn ($i) => $i['quantity'] * $i['rate']);
        $lineDiscount = collect($lineItems)->sum('discount_amount');
        $taxable = collect($lineItems)->sum('taxable_amount');
        $cgst = collect($lineItems)->sum('cgst_amount');
        $sgst = collect($lineItems)->sum('sgst_amount');
        $igst = collect($lineItems)->sum('igst_amount');
        $totalTax = $cgst + $sgst + $igst;

        $docDiscountType = $options['doc_discount_type'] ?? 'percent';
        $docDiscountValue = (float) ($options['doc_discount_value'] ?? 0);
        $docDiscount = $docDiscountType === 'percent'
            ? round($taxable * ($docDiscountValue / 100), 2)
            : round(min($docDiscountValue, $taxable), 2);

        $additional = collect($options['additional_charges'] ?? [])->sum(fn ($c) => (float) ($c['amount'] ?? 0));

        $grandTotal = round(max($taxable - $docDiscount, 0) + $totalTax + $additional, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($lineDiscount + $docDiscount, 2),
            'discount_percent' => $docDiscountType === 'percent' ? $docDiscountValue : 0,
            'doc_discount_type' => $docDiscountType,
            'doc_discount_value' => $docDiscountValue,
            'taxable_amount' => round($taxable - $docDiscount, 2),
            'cgst_amount' => round($cgst, 2),
            'sgst_amount' => round($sgst, 2),
            'igst_amount' => round($igst, 2),
            'total_tax' => round($totalTax, 2),
            'grand_total' => $grandTotal,
            'total_in_words' => AmountInWords::currency($grandTotal, $options['currency'] ?? 'INR'),
        ];
    }

    public function previewTotals(array $items, bool $isGst, Tenant $tenant, ?string $customerState, array $options = []): array
    {
        $options['currency'] ??= 'INR';
        $isInterState = $this->isInterState($tenant, null, $customerState);
        $calculated = [];
        foreach ($items as $idx => $item) {
            if (empty(trim($item['description'] ?? ''))) {
                continue;
            }
            $item['sort_order'] = $idx;
            $calculated[] = $this->calculateLineItem($item, $isGst, $isInterState);
        }

        return $this->calculateDocumentTotals($calculated, $options);
    }

    public function isInterState(Tenant $tenant, ?Customer $customer, ?string $customerState = null): bool
    {
        $tenantState = strtolower(trim($tenant->state ?? ''));
        $custState = strtolower(trim($customerState ?? $customer?->state ?? ''));

        if (empty($tenantState) || empty($custState)) {
            return false;
        }

        return $tenantState !== $custState;
    }

    public function createDocument(array $data, array $items, Tenant $tenant): Document
    {
        $customer = isset($data['customer_id']) ? Customer::find($data['customer_id']) : null;
        $leadId = $this->resolveLeadId($data, $tenant);

        $isInterState = $this->isInterState($tenant, $customer, $data['customer_state'] ?? null);
        $isGst = (bool) ($data['is_gst_applicable'] ?? true);

        $calculatedItems = $this->prepareItems($items, $isGst, $isInterState);
        $totals = $this->calculateDocumentTotals($calculatedItems, [
            'doc_discount_type' => $data['doc_discount_type'] ?? 'percent',
            'doc_discount_value' => $data['doc_discount_value'] ?? 0,
            'additional_charges' => $data['additional_charges'] ?? [],
            'currency' => $data['currency'] ?? 'INR',
        ]);

        $templateKey = $data['template_key'] ?? 'classic_purple';
        $templates = $this->templates();
        $themeColor = $data['theme_color'] ?? ($templates[$templateKey]['color'] ?? '#7c3aed');

        $conversionPath = $this->buildConversionPath($data, $leadId, $data['type']);

        $document = Document::create($this->filterDocumentPayload([
            'tenant_id' => $tenant->id,
            'customer_id' => $data['customer_id'] ?? null,
            'lead_id' => $leadId,
            'reference_document_id' => $data['reference_document_id'] ?? null,
            'conversion_path' => $conversionPath,
            'type' => $data['type'],
            'document_number' => $this->resolveDocumentNumber($data['type'], $tenant->id, $data['document_number'] ?? null),
            'title' => $data['title'] ?? null,
            'template_key' => $templateKey,
            'theme_color' => $themeColor,
            'currency' => $data['currency'] ?? 'INR',
            'exchange_rate' => $data['exchange_rate'] ?? null,
            'status' => 'draft',
            'is_gst_applicable' => $isGst,
            'issue_date' => $data['issue_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            ...$totals,
            'place_of_supply' => $data['place_of_supply'] ?? $data['customer_state'] ?? $customer?->state ?? $tenant->state,
            'customer_gstin' => $data['customer_gstin'] ?? $customer?->gstin,
            'customer_name' => $data['customer_name'] ?? $customer?->name,
            'customer_address' => $data['customer_address'] ?? $customer?->billing_address,
            'customer_state' => $data['customer_state'] ?? $customer?->state,
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'seller_snapshot' => $data['seller_snapshot'] ?? $this->sellerSnapshot($tenant),
            'logo_path' => $data['logo_path'] ?? null,
            'signature_data' => $data['signature_data'] ?? null,
            'payment_options' => $data['payment_options'] ?? null,
            'attachments' => $data['attachments'] ?? null,
            'contact_details' => $data['contact_details'] ?? null,
            'additional_info' => $data['additional_info'] ?? null,
            'additional_charges' => $data['additional_charges'] ?? null,
            'notes' => $data['notes'] ?? null,
            'terms_conditions' => $data['terms_conditions'] ?? $this->defaultTerms(),
            'advanced_options' => $data['advanced_options'] ?? null,
            'shipping_details' => $data['shipping_details'] ?? null,
            'created_by' => auth()->id(),
        ]));

        foreach ($calculatedItems as $item) {
            $document->items()->create($this->filterItemPayload($item));
        }

        $this->logLeadActivity($document, 'created');

        return $document->load('items');
    }

    public function updateDocument(Document $document, array $data, array $items): Document
    {
        $tenant = $document->tenant;
        $customer = isset($data['customer_id']) ? Customer::find($data['customer_id']) : null;
        $leadId = $this->resolveLeadId($data, $tenant, $document->lead_id);

        $isInterState = $this->isInterState($tenant, $customer, $data['customer_state'] ?? $document->customer_state);
        $isGst = (bool) ($data['is_gst_applicable'] ?? $document->is_gst_applicable);

        $calculatedItems = $this->prepareItems($items, $isGst, $isInterState);
        $totals = $this->calculateDocumentTotals($calculatedItems, [
            'doc_discount_type' => $data['doc_discount_type'] ?? $document->doc_discount_type,
            'doc_discount_value' => $data['doc_discount_value'] ?? $document->doc_discount_value,
            'additional_charges' => $data['additional_charges'] ?? $document->additional_charges ?? [],
            'currency' => $data['currency'] ?? $document->currency ?? 'INR',
        ]);

        $templateKey = $data['template_key'] ?? $document->template_key;
        $templates = $this->templates();
        $themeColor = $data['theme_color'] ?? ($templates[$templateKey]['color'] ?? $document->theme_color);

        $document->update($this->filterDocumentPayload([
            'customer_id' => $data['customer_id'] ?? $document->customer_id,
            'lead_id' => $leadId,
            'type' => $data['type'] ?? $document->type,
            'title' => $data['title'] ?? $document->title,
            'template_key' => $templateKey,
            'theme_color' => $themeColor,
            'currency' => $data['currency'] ?? $document->currency,
            'exchange_rate' => $data['exchange_rate'] ?? $document->exchange_rate,
            'is_gst_applicable' => $isGst,
            'issue_date' => $data['issue_date'] ?? $document->issue_date,
            'due_date' => $data['due_date'] ?? $document->due_date,
            'valid_until' => $data['valid_until'] ?? $document->valid_until,
            ...$totals,
            'place_of_supply' => $data['place_of_supply'] ?? $document->place_of_supply,
            'customer_gstin' => $data['customer_gstin'] ?? $document->customer_gstin,
            'customer_name' => $data['customer_name'] ?? $document->customer_name,
            'customer_address' => $data['customer_address'] ?? $document->customer_address,
            'customer_state' => $data['customer_state'] ?? $document->customer_state,
            'customer_phone' => $data['customer_phone'] ?? $document->customer_phone,
            'customer_email' => $data['customer_email'] ?? $document->customer_email,
            'logo_path' => $data['logo_path'] ?? $document->logo_path,
            // null allowed — checkbox off pe signature clear hona chahiye (?? null ko ignore karta tha)
            'signature_data' => array_key_exists('signature_data', $data) ? $data['signature_data'] : $document->signature_data,
            'payment_options' => array_key_exists('payment_options', $data) ? $data['payment_options'] : $document->payment_options,
            'attachments' => $data['attachments'] ?? $document->attachments,
            'contact_details' => array_key_exists('contact_details', $data) ? $data['contact_details'] : $document->contact_details,
            'additional_info' => $data['additional_info'] ?? $document->additional_info,
            'additional_charges' => $data['additional_charges'] ?? $document->additional_charges,
            'notes' => array_key_exists('notes', $data) ? $data['notes'] : $document->notes,
            'terms_conditions' => array_key_exists('terms_conditions', $data) ? $data['terms_conditions'] : $document->terms_conditions,
            'advanced_options' => $data['advanced_options'] ?? $document->advanced_options,
            'shipping_details' => array_key_exists('shipping_details', $data) ? $data['shipping_details'] : $document->shipping_details,
        ]));

        $document->items()->delete();
        foreach ($calculatedItems as $item) {
            $document->items()->create($this->filterItemPayload($item));
        }

        $this->logLeadActivity($document->fresh(), 'updated');

        return $document->fresh(['items']);
    }

    public function convertDocument(Document $source, string $targetType): Document
    {
        if (! $this->canConvert($source, $targetType)) {
            throw new \InvalidArgumentException("Cannot convert {$source->type} to {$targetType}");
        }

        $items = $source->items->map(fn ($item) => [
            'description' => $item->description,
            'long_description' => $item->long_description,
            'group_name' => $item->group_name,
            'image_path' => $item->image_path,
            'hsn_sac' => $item->hsn_sac,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'rate' => $item->rate,
            'discount_type' => $item->discount_type ?? 'fixed',
            'discount_percent' => $item->discount_percent,
            'discount_amount' => $item->discount_amount,
            'gst_rate' => $item->gst_rate,
        ])->toArray();

        $path = $source->conversion_path ?? [];
        $path[] = [
            'step' => count($path) + 1,
            'action' => 'converted_to',
            'from_type' => $source->type,
            'from_id' => $source->id,
            'from_number' => $source->document_number,
            'to_type' => $targetType,
            'at' => now()->toIso8601String(),
            'by' => auth()->id(),
        ];

        $newDoc = $this->createDocument([
            'type' => $targetType,
            'customer_id' => $source->customer_id,
            'lead_id' => $source->lead_id,
            'reference_document_id' => $source->id,
            'conversion_path' => $path,
            'is_gst_applicable' => $source->is_gst_applicable,
            'template_key' => $source->template_key,
            'theme_color' => $source->theme_color,
            'currency' => $source->currency,
            'exchange_rate' => $source->exchange_rate,
            'title' => $source->title,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'valid_until' => $targetType === 'quotation' ? now()->addDays(30)->toDateString() : null,
            'customer_gstin' => $source->customer_gstin,
            'customer_name' => $source->customer_name,
            'customer_address' => $source->customer_address,
            'customer_state' => $source->customer_state,
            'customer_phone' => $source->customer_phone,
            'customer_email' => $source->customer_email,
            'place_of_supply' => $source->place_of_supply,
            'logo_path' => $source->logo_path,
            'signature_data' => $source->signature_data,
            'payment_options' => $source->payment_options,
            'attachments' => $source->attachments,
            'contact_details' => $source->contact_details,
            'additional_info' => $source->additional_info,
            'shipping_details' => $source->shipping_details,
            'notes' => $source->notes,
            'terms_conditions' => $source->terms_conditions,
            'advanced_options' => $source->advanced_options,
            'doc_discount_type' => $source->doc_discount_type,
            'doc_discount_value' => $source->doc_discount_value,
            'additional_charges' => $source->additional_charges,
        ], $items, $source->tenant);

        if ($targetType === 'proforma') {
            $source->update(['status' => 'accepted']);
        }
        if ($targetType === 'invoice' && $source->type === 'quotation') {
            $source->update(['status' => 'accepted']);
        }

        $this->logLeadActivity($newDoc, 'converted', $source);

        return $newDoc;
    }

    public function duplicateDocument(Document $source): Document
    {
        $items = $source->items->map(fn ($item) => [
            'description' => $item->description,
            'long_description' => $item->long_description,
            'group_name' => $item->group_name,
            'image_path' => $item->image_path,
            'hsn_sac' => $item->hsn_sac,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'rate' => $item->rate,
            'discount_type' => $item->discount_type ?? 'fixed',
            'discount_percent' => $item->discount_percent,
            'discount_amount' => $item->discount_amount,
            'gst_rate' => $item->gst_rate,
        ])->toArray();

        $path = $source->conversion_path ?? [];
        $path[] = [
            'step' => count($path) + 1,
            'action' => 'duplicated_from',
            'from_id' => $source->id,
            'from_number' => $source->document_number,
            'at' => now()->toIso8601String(),
            'by' => auth()->id(),
        ];

        return $this->createDocument([
            'type' => $source->type,
            'customer_id' => $source->customer_id,
            'lead_id' => $source->lead_id,
            'reference_document_id' => $source->id,
            'conversion_path' => $path,
            'is_gst_applicable' => $source->is_gst_applicable,
            'template_key' => $source->template_key,
            'theme_color' => $source->theme_color,
            'currency' => $source->currency,
            'exchange_rate' => $source->exchange_rate,
            'title' => $source->title,
            'issue_date' => now()->toDateString(),
            'due_date' => $source->due_date?->toDateString(),
            'valid_until' => $source->valid_until?->toDateString(),
            'customer_gstin' => $source->customer_gstin,
            'customer_name' => $source->customer_name,
            'customer_address' => $source->customer_address,
            'customer_state' => $source->customer_state,
            'customer_phone' => $source->customer_phone,
            'customer_email' => $source->customer_email,
            'place_of_supply' => $source->place_of_supply,
            'logo_path' => $source->logo_path,
            'signature_data' => $source->signature_data,
            'payment_options' => $source->payment_options,
            'attachments' => $source->attachments,
            'contact_details' => $source->contact_details,
            'additional_info' => $source->additional_info,
            'shipping_details' => $source->shipping_details,
            'notes' => $source->notes,
            'terms_conditions' => $source->terms_conditions,
            'advanced_options' => $source->advanced_options,
            'doc_discount_type' => $source->doc_discount_type,
            'doc_discount_value' => $source->doc_discount_value,
            'additional_charges' => $source->additional_charges,
        ], $items, $source->tenant);
    }

    protected function filterDocumentPayload(array $data): array
    {
        return collect($data)->filter(fn ($_, $key) => Schema::hasColumn('documents', $key))->all();
    }

    protected function filterItemPayload(array $data): array
    {
        return collect($data)->filter(fn ($_, $key) => Schema::hasColumn('document_items', $key))->all();
    }

    public function canConvert(Document $source, string $targetType): bool
    {
        return match ($targetType) {
            'proforma' => $source->type === 'quotation',
            'invoice' => in_array($source->type, ['quotation', 'proforma'], true),
            default => false,
        };
    }

    protected function prepareItems(array $items, bool $isGst, bool $isInterState): array
    {
        $calculatedItems = [];
        foreach ($items as $idx => $item) {
            if (empty(trim($item['description'] ?? ''))) {
                continue;
            }
            $item['sort_order'] = $idx;
            $calculatedItems[] = $this->calculateLineItem($item, $isGst, $isInterState);
        }

        return $calculatedItems;
    }

    protected function resolveLeadId(array $data, Tenant $tenant, ?int $existingLeadId = null): ?int
    {
        if (! empty($data['lead_id'])) {
            return (int) $data['lead_id'];
        }

        if ($existingLeadId) {
            return $existingLeadId;
        }

        if (empty($data['customer_name']) && empty($data['customer_phone']) && empty($data['customer_email'])) {
            return null;
        }

        $stage = LeadStage::ensureDefault($tenant->id);

        $lead = Lead::create([
            'tenant_id' => $tenant->id,
            'lead_stage_id' => $stage->id,
            'created_by' => auth()->id(),
            'assigned_to' => auth()->id(),
            'name' => $data['customer_name'] ?? 'Document Lead',
            'phone' => $data['customer_phone'] ?? null,
            'email' => $data['customer_email'] ?? null,
            'gstin' => $data['customer_gstin'] ?? null,
            'address' => $data['customer_address'] ?? null,
            'state' => $data['customer_state'] ?? null,
            'source' => 'document',
            'company' => $data['customer_name'] ?? null,
        ]);

        return $lead->id;
    }

    protected function buildConversionPath(array $data, ?int $leadId, string $type): array
    {
        if (! empty($data['conversion_path'])) {
            return $data['conversion_path'];
        }

        $path = [];
        if ($leadId) {
            $path[] = ['step' => 1, 'action' => 'from_lead', 'lead_id' => $leadId, 'at' => now()->toIso8601String()];
        }
        if (! empty($data['reference_document_id'])) {
            $ref = Document::find($data['reference_document_id']);
            $path[] = [
                'step' => count($path) + 1,
                'action' => 'from_document',
                'document_id' => $ref?->id,
                'document_type' => $ref?->type,
                'document_number' => $ref?->document_number,
                'at' => now()->toIso8601String(),
            ];
        }
        $path[] = [
            'step' => count($path) + 1,
            'action' => 'created',
            'type' => $type,
            'at' => now()->toIso8601String(),
            'by' => auth()->id(),
        ];

        return $path;
    }

    protected function sellerSnapshot(Tenant $tenant): array
    {
        return [
            'name' => $tenant->name,
            'address' => $tenant->address,
            'city' => $tenant->city,
            'state' => $tenant->state,
            'pincode' => $tenant->pincode,
            'gstin' => $tenant->gstin,
            'phone' => $tenant->phone,
            'email' => $tenant->email,
        ];
    }

    protected function logLeadActivity(Document $document, string $action, ?Document $source = null): void
    {
        if (! $document->lead_id) {
            return;
        }

        $lead = Lead::find($document->lead_id);
        if (! $lead) {
            return;
        }

        $label = $document->typeLabel();
        $title = match ($action) {
            'created' => "{$label} created: {$document->document_number}",
            'updated' => "{$label} updated: {$document->document_number}",
            'converted' => "{$label} converted from {$source?->typeLabel()} {$source?->document_number}",
            default => "{$label}: {$document->document_number}",
        };

        $lead->logActivity('document', $title, 'Amount: ₹'.number_format($document->grand_total, 2));
    }

    public function defaultTerms(): string
    {
        return "1. Payment: 60% advance, 30% on delivery, 10% final.\n2. Domain & logo to be provided by client.\n3. Subject to local jurisdiction.";
    }
}
