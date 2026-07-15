<?php

namespace App\Livewire\Documents;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Lead;
use App\Models\Product;
use App\Services\DocumentService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public ?int $documentId = null;
    public string $document_number = '';
    public string $type = 'quotation';
    public bool $is_gst_applicable = true;
    public string $template_key = 'classic_purple';
    public string $theme_color = '#7c3aed';
    public string $currency = 'INR';
    public ?float $exchange_rate = null;
    public ?int $customer_id = null;
    public ?int $lead_id = null;
    public ?int $reference_document_id = null;
    public string $title = '';
    public string $customer_name = '';
    public string $customer_gstin = '';
    public string $customer_address = '';
    public string $customer_state = '';
    public string $customer_phone = '';
    public string $customer_email = '';
    public string $issue_date = '';
    public string $due_date = '';
    public string $valid_until = '';
    public string $notes = '';
    public string $terms_conditions = '';
    public string $additional_info = '';
    public array $items = [];
    public string $productSearch = '';
    public string $doc_discount_type = 'percent';
    public ?float $doc_discount_value = 0;
    public array $additional_charges = [];
    public bool $showShipping = false;
    public string $shipping_address = '';
    public bool $showSignature = false;
    public string $signature_name = '';
    public string $signature_title = '';
    public $signatureUpload;
    public ?string $signature_image_path = null;
    public $stampUpload;
    public ?string $stamp_image_path = null;
    public bool $showPaymentLink = false;
    public string $payment_link = '';
    public bool $showUpi = false;
    public string $payment_upi = '';
    public bool $showQr = false;
    public $qrUpload;
    public ?string $qr_image_path = null;
    public bool $showContactDetails = false;
    public string $contact_person = '';
    public string $contact_phone = '';
    public string $contact_email = '';
    public $logoUpload;
    public ?string $logo_path = null;
    public array $attachmentUploads = [];
    public array $savedAttachments = [];
    public array $itemImageUploads = [];
    public string $activeGroup = '';
    public array $advanced_options = [
        'show_description_full_width' => true,
        'hide_place_of_supply' => false,
        'show_tax_summary' => true,
        'summarise_quantity' => false,
        'show_bank_details' => true,
        'show_powered_by_nexpaar' => true,
    ];

    public function mount(?Document $document = null): void
    {
        $this->issue_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(6)->format('Y-m-d');
        $this->valid_until = now()->addDays(30)->format('Y-m-d');
        $this->terms_conditions = app(DocumentService::class)->defaultTerms();

        if ($document && $document->exists) {
            $this->loadDocument($document);

            return;
        }

        $this->items = [$this->blankItem()];
        $this->applyRequestPrefill();
    }

    protected function applyRequestPrefill(): void
    {
        if (request('type') && in_array(request('type'), ['quotation', 'proforma', 'invoice'], true)) {
            $this->type = request('type');
        }
        if (request('from_document')) {
            $this->prefillFromDocument((int) request('from_document'), request('type'));
        }
        if (request('lead_id')) {
            $this->prefillFromLead((int) request('lead_id'));
        }
        if (request('customer_id')) {
            $this->prefillFromCustomer((int) request('customer_id'));
        }
        if (request('template')) {
            $this->setTemplate(request('template'));
        }
        if (request('products')) {
            $this->prefillProducts((string) request('products'));
        }
    }

    protected function prefillProducts(string $csv): void
    {
        $ids = array_values(array_filter(array_map('intval', explode(',', $csv))));
        if (empty($ids)) {
            return;
        }

        $products = Product::whereIn('id', $ids)->where('is_active', true)->get()->keyBy('id');
        $added = false;

        foreach ($ids as $id) {
            $product = $products->get($id);
            if (! $product) {
                continue;
            }
            $this->items[] = [
                'description' => $product->name,
                'long_description' => $product->description ?? '',
                'group_name' => $product->category ?? '',
                'image_path' => null,
                'hsn_sac' => $product->hsn_sac ?? '998314',
                'quantity' => 1,
                'unit' => $product->unit ?? 'Nos',
                'rate' => (float) $product->price,
                'discount_type' => 'fixed',
                'discount_percent' => 0,
                'discount_amount' => 0,
                'gst_rate' => (float) $product->tax_rate,
                'expanded' => true,
            ];
            $added = true;
        }

        if ($added) {
            // Shuruaati blank line item hata do — quote seedha products ke saath khule
            $this->items = array_values(array_filter(
                $this->items,
                fn ($item) => filled(trim($item['description'] ?? ''))
            ));
        }
    }

    protected function blankItem(): array
    {
        return [
            'description' => '',
            'long_description' => '',
            'group_name' => '',
            'image_path' => null,
            'hsn_sac' => '998314',
            'quantity' => 1,
            'unit' => 'Nos',
            'rate' => 0,
            'discount_type' => 'fixed',
            'discount_percent' => 0,
            'discount_amount' => 0,
            'gst_rate' => 18,
            'expanded' => true,
        ];
    }

    protected function loadDocument(Document $document): void
    {
        $this->documentId = $document->id;
        $this->document_number = $document->document_number;
        $this->type = $document->type;
        $this->is_gst_applicable = $document->is_gst_applicable;
        $this->template_key = $document->template_key ?? 'classic_purple';
        $this->theme_color = $document->theme_color ?? '#7c3aed';
        $this->currency = $document->currency ?? 'INR';
        $this->exchange_rate = $document->exchange_rate ? (float) $document->exchange_rate : null;
        $this->customer_id = $document->customer_id;
        $this->lead_id = $document->lead_id;
        $this->reference_document_id = $document->reference_document_id;
        $this->title = $document->title ?? '';
        $this->customer_name = $document->customer_name ?? '';
        $this->customer_gstin = $document->customer_gstin ?? '';
        $this->customer_address = $document->customer_address ?? '';
        $this->customer_state = $document->customer_state ?? '';
        $this->customer_phone = $document->customer_phone ?? '';
        $this->customer_email = $document->customer_email ?? '';
        $this->issue_date = $document->issue_date->format('Y-m-d');
        $this->due_date = $document->due_date?->format('Y-m-d') ?? '';
        $this->valid_until = $document->valid_until?->format('Y-m-d') ?? '';
        $this->notes = $document->notes ?? '';
        $this->terms_conditions = $document->terms_conditions ?? '';
        $this->additional_info = $document->additional_info ?? '';
        $this->doc_discount_type = $document->doc_discount_type ?? 'percent';
        $this->doc_discount_value = (float) ($document->doc_discount_value ?? 0);
        $this->additional_charges = $document->additional_charges ?? [];
        $loadedOpts = $document->advanced_options ?? [];
        // Old key → Nexpaar spelling
        if (array_key_exists('show_powered_by_nexpar', $loadedOpts) && ! array_key_exists('show_powered_by_nexpaar', $loadedOpts)) {
            $loadedOpts['show_powered_by_nexpaar'] = (bool) $loadedOpts['show_powered_by_nexpar'];
        }
        $this->advanced_options = array_merge($this->advanced_options, $loadedOpts);
        $this->shipping_address = $document->shipping_details['address'] ?? '';
        $this->showShipping = ! empty($this->shipping_address);
        $this->logo_path = $document->logo_path;
        $this->savedAttachments = $document->attachments ?? [];
        $sig = $document->signature_data ?? [];
        $this->showSignature = ! empty($sig);
        $this->signature_name = $sig['name'] ?? '';
        $this->signature_title = $sig['title'] ?? '';
        $this->signature_image_path = $sig['signature_image'] ?? null;
        $this->stamp_image_path = $sig['stamp_image'] ?? null;
        $pay = $document->payment_options ?? [];
        $this->showPaymentLink = ! empty($pay['link']);
        $this->payment_link = $pay['link'] ?? '';
        $this->showUpi = ! empty($pay['upi']);
        $this->payment_upi = $pay['upi'] ?? '';
        $this->showQr = ! empty($pay['qr_image']);
        $this->qr_image_path = $pay['qr_image'] ?? null;
        $contact = $document->contact_details ?? [];
        $this->showContactDetails = ! empty($contact);
        $this->contact_person = $contact['person'] ?? '';
        $this->contact_phone = $contact['phone'] ?? '';
        $this->contact_email = $contact['email'] ?? '';

        $this->items = $document->items->map(fn ($item) => [
            'description' => $item->description,
            'long_description' => $item->long_description ?? '',
            'group_name' => $item->group_name ?? '',
            'image_path' => $item->image_path,
            'hsn_sac' => $item->hsn_sac ?? '998314',
            'quantity' => (float) $item->quantity,
            'unit' => $item->unit,
            'rate' => (float) $item->rate,
            'discount_type' => $item->discount_type ?? 'fixed',
            'discount_percent' => (float) $item->discount_percent,
            'discount_amount' => (float) $item->discount_amount,
            'gst_rate' => (float) $item->gst_rate,
            'expanded' => false,
        ])->toArray() ?: [$this->blankItem()];
    }

    protected function prefillFromLead(int $leadId): void
    {
        $lead = Lead::find($leadId);
        if (! $lead) {
            return;
        }
        $this->lead_id = $lead->id;
        $this->customer_name = $lead->name;
        $this->customer_gstin = $lead->gstin ?? '';
        $this->customer_address = $lead->address ?? '';
        $this->customer_state = $lead->state ?? '';
        $this->customer_phone = $lead->phone ?? '';
        $this->customer_email = $lead->email ?? '';
        $this->title = $lead->service_type ?: $lead->company ?: '';
        if ($lead->customer) {
            $this->customer_id = $lead->customer->id;
        }
    }

    protected function prefillFromCustomer(int $customerId): void
    {
        $customer = Customer::find($customerId);
        if (! $customer) {
            return;
        }
        $this->customer_id = $customer->id;
        $this->customer_name = $customer->name;
        $this->customer_gstin = $customer->gstin ?? '';
        $this->customer_address = $customer->billing_address ?? '';
        $this->customer_state = $customer->state ?? '';
        $this->customer_phone = $customer->phone ?? '';
        $this->customer_email = $customer->email ?? '';
    }

    protected function prefillFromDocument(int $docId, ?string $targetType): void
    {
        $source = Document::with('items')->find($docId);
        if (! $source) {
            return;
        }
        $this->reference_document_id = $source->id;
        $this->loadDocument($source);
        $this->documentId = null;
        $this->document_number = '';
        $this->type = $targetType && in_array($targetType, ['quotation', 'proforma', 'invoice'], true)
            ? $targetType
            : ($source->type === 'quotation' ? 'proforma' : 'invoice');
    }

    public function setTemplate(string $key): void
    {
        $templates = config('document-templates', []);
        if (! isset($templates[$key])) {
            $this->dispatch('notify', message: 'Template not found', type: 'error');

            return;
        }
        $this->template_key = $key;
        $this->theme_color = $templates[$key]['color'];
        $this->dispatch('notify', message: 'Template: '.$templates[$key]['name']);
    }

    public function updatedTemplateKey(string $value): void
    {
        $this->setTemplate($value);
    }

    public function updatedLogoUpload(): void
    {
        if (! $this->logoUpload) {
            return;
        }

        try {
            $this->validate([
                'logoUpload' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            ]);

            if ($this->logo_path) {
                Storage::disk('public')->delete($this->logo_path);
            }

            $path = $this->logoUpload->store(
                'documents/logos/'.auth()->user()->tenant_id,
                'public'
            );
            $this->logo_path = $path;
            $this->logoUpload = null;
            $this->dispatch('notify', message: 'Logo uploaded successfully');
        } catch (\Throwable $e) {
            $this->logoUpload = null;
            $this->dispatch('notify', message: 'Logo upload failed: '.$e->getMessage(), type: 'error');
        }
    }

    public function removeLogo(): void
    {
        if ($this->logo_path) {
            Storage::disk('public')->delete($this->logo_path);
        }
        $this->logo_path = null;
    }

    protected function storeImageUpload($file, string $folder): ?string
    {
        try {
            validator(
                ['file' => $file],
                ['file' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120']
            )->validate();

            return $file->store('documents/'.$folder.'/'.auth()->user()->tenant_id, 'public');
        } catch (\Throwable $e) {
            $this->dispatch('notify', message: 'Upload failed: image (JPG/PNG, max 5MB) hi allowed hai', type: 'error');

            return null;
        }
    }

    public function updatedSignatureUpload(): void
    {
        if (! $this->signatureUpload) {
            return;
        }
        if ($path = $this->storeImageUpload($this->signatureUpload, 'signatures')) {
            if ($this->signature_image_path) {
                Storage::disk('public')->delete($this->signature_image_path);
            }
            $this->signature_image_path = $path;
            $this->dispatch('notify', message: 'Signature image uploaded');
        }
        $this->signatureUpload = null;
    }

    public function removeSignatureImage(): void
    {
        if ($this->signature_image_path) {
            Storage::disk('public')->delete($this->signature_image_path);
        }
        $this->signature_image_path = null;
    }

    public function updatedStampUpload(): void
    {
        if (! $this->stampUpload) {
            return;
        }
        if ($path = $this->storeImageUpload($this->stampUpload, 'stamps')) {
            if ($this->stamp_image_path) {
                Storage::disk('public')->delete($this->stamp_image_path);
            }
            $this->stamp_image_path = $path;
            $this->dispatch('notify', message: 'Company stamp uploaded');
        }
        $this->stampUpload = null;
    }

    public function removeStampImage(): void
    {
        if ($this->stamp_image_path) {
            Storage::disk('public')->delete($this->stamp_image_path);
        }
        $this->stamp_image_path = null;
    }

    public function updatedQrUpload(): void
    {
        if (! $this->qrUpload) {
            return;
        }
        if ($path = $this->storeImageUpload($this->qrUpload, 'qr')) {
            if ($this->qr_image_path) {
                Storage::disk('public')->delete($this->qr_image_path);
            }
            $this->qr_image_path = $path;
            $this->showQr = true;
            $this->dispatch('notify', message: 'Payment QR uploaded');
        }
        $this->qrUpload = null;
    }

    public function removeQrImage(): void
    {
        if ($this->qr_image_path) {
            Storage::disk('public')->delete($this->qr_image_path);
        }
        $this->qr_image_path = null;
    }

    public function updatedAttachmentUploads(): void
    {
        try {
            $this->validate([
                'attachmentUploads.*' => 'file|max:10240',
            ]);

            foreach ($this->attachmentUploads as $file) {
                if (! $file) {
                    continue;
                }
                $path = $file->store('documents/attachments/'.auth()->user()->tenant_id, 'public');
                $this->savedAttachments[] = [
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'size' => $file->getSize(),
                ];
            }
            $this->attachmentUploads = [];
            $this->dispatch('notify', message: 'Attachment added');
        } catch (\Throwable $e) {
            $this->attachmentUploads = [];
            $this->dispatch('notify', message: 'Attachment failed: max 10MB per file', type: 'error');
        }
    }

    public function removeAttachment(int $index): void
    {
        if (isset($this->savedAttachments[$index]['path'])) {
            Storage::disk('public')->delete($this->savedAttachments[$index]['path']);
        }
        unset($this->savedAttachments[$index]);
        $this->savedAttachments = array_values($this->savedAttachments);
    }

    public function updatedItemImageUploads($value, $key): void
    {
        if (! $value) {
            return;
        }
        $this->validate(["itemImageUploads.{$key}" => 'nullable|image|max:2048']);
        if (isset($this->items[$key]['image_path'])) {
            Storage::disk('public')->delete($this->items[$key]['image_path']);
        }
        $path = $value->store('documents/items/'.auth()->user()->tenant_id, 'public');
        $this->items[$key]['image_path'] = $path;
        unset($this->itemImageUploads[$key]);
        $this->dispatch('notify', message: 'Item image added');
    }

    public function removeItemImage(int $index): void
    {
        if (isset($this->items[$index]['image_path'])) {
            Storage::disk('public')->delete($this->items[$index]['image_path']);
            $this->items[$index]['image_path'] = null;
        }
    }

    public function addItem(?string $group = null): void
    {
        $item = $this->blankItem();
        $item['group_name'] = $group ?? $this->activeGroup;
        $this->items[] = $item;
    }

    public function addGroup(): void
    {
        $name = 'Group '.(count(array_unique(array_filter(array_column($this->items, 'group_name')))) + 1);
        $this->activeGroup = $name;
        $this->addItem($name);
        $this->dispatch('notify', message: "Group '{$name}' added");
    }

    public function removeItem(int $index): void
    {
        if (isset($this->items[$index]['image_path'])) {
            Storage::disk('public')->delete($this->items[$index]['image_path']);
        }
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        if (empty($this->items)) {
            $this->items = [$this->blankItem()];
        }
    }

    public function moveItemUp(int $index): void
    {
        if ($index <= 0) {
            return;
        }
        [$this->items[$index - 1], $this->items[$index]] = [$this->items[$index], $this->items[$index - 1]];
    }

    public function moveItemDown(int $index): void
    {
        if ($index >= count($this->items) - 1) {
            return;
        }
        [$this->items[$index + 1], $this->items[$index]] = [$this->items[$index], $this->items[$index + 1]];
    }

    public function duplicateItem(int $index): void
    {
        $copy = $this->items[$index];
        $copy['expanded'] = false;
        array_splice($this->items, $index + 1, 0, [$copy]);
    }

    public function toggleItemExpanded(int $index): void
    {
        $this->items[$index]['expanded'] = ! ($this->items[$index]['expanded'] ?? false);
    }

    public function addCharge(): void
    {
        $this->additional_charges[] = ['label' => 'Additional Charge', 'amount' => 0];
    }

    public function removeCharge(int $index): void
    {
        unset($this->additional_charges[$index]);
        $this->additional_charges = array_values($this->additional_charges);
    }

    public function updatedCustomerId($value): void
    {
        if ($value) {
            $this->prefillFromCustomer((int) $value);
        }
    }

    public function addProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->items[] = [
            'description' => $product->name,
            'long_description' => $product->description ?? '',
            'group_name' => $product->category ?? $this->activeGroup,
            'image_path' => null,
            'hsn_sac' => $product->hsn_sac ?? '998314',
            'quantity' => 1,
            'unit' => $product->unit ?? 'Nos',
            'rate' => (float) $product->price,
            'discount_type' => 'fixed',
            'discount_percent' => 0,
            'discount_amount' => 0,
            'gst_rate' => (float) $product->tax_rate,
            'expanded' => true,
        ];
        $this->productSearch = '';
        $this->dispatch('notify', message: 'Product added to quote');
    }

    public function getPreviewTotalsProperty(): array
    {
        return app(DocumentService::class)->previewTotals(
            $this->items,
            $this->is_gst_applicable,
            auth()->user()->tenant,
            $this->customer_state ?: null,
            [
                'doc_discount_type' => $this->doc_discount_type,
                'doc_discount_value' => $this->doc_discount_value ?? 0,
                'additional_charges' => $this->additional_charges,
                'currency' => $this->currency,
            ]
        );
    }

    public function save(DocumentService $documentService)
    {
        if (! auth()->user()->hasPermission('documents.create')) {
            abort(403);
        }

        try {
            $document = $this->persistDocument($documentService);
        } catch (ValidationException $e) {
            $this->dispatch('notify', message: 'Please fill required fields (client name + at least one item).', type: 'error');
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', message: 'Save failed: '.$e->getMessage(), type: 'error');

            return;
        }

        session()->flash('success', $this->documentId ? 'Document updated successfully' : 'Document created successfully');
        $this->dispatch('notify', message: 'Document saved');

        return redirect()->route('leads.documents.show', $document);
    }

    public function saveAndPreview(DocumentService $documentService)
    {
        if (! auth()->user()->hasPermission('documents.create')) {
            abort(403);
        }

        try {
            $document = $this->persistDocument($documentService);
        } catch (ValidationException $e) {
            $this->dispatch('notify', message: 'Please fill required fields before preview.', type: 'error');
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', message: 'Save failed: '.$e->getMessage(), type: 'error');

            return;
        }

        session()->flash('success', 'Document saved — opening PDF preview');
        $this->dispatch('open-url', url: route('leads.documents.pdf', $document));

        return redirect()->route('leads.documents.show', $document);
    }

    public function saveAndDownload(DocumentService $documentService)
    {
        if (! auth()->user()->hasPermission('documents.create')) {
            abort(403);
        }

        try {
            $document = $this->persistDocument($documentService);
        } catch (ValidationException $e) {
            $this->dispatch('notify', message: 'Please fill required fields before download.', type: 'error');
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('notify', message: 'Save failed: '.$e->getMessage(), type: 'error');

            return;
        }

        session()->flash('success', 'Document saved — downloading PDF');

        return redirect()->route('leads.documents.download', $document);
    }

    protected function validatedItems(): array
    {
        $items = array_values(array_filter(
            $this->items,
            fn ($item) => filled(trim($item['description'] ?? ''))
        ));

        if (empty($items)) {
            throw ValidationException::withMessages([
                'items' => 'Add at least one line item with a name.',
            ]);
        }

        return $items;
    }

    protected function persistDocument(DocumentService $documentService): Document
    {
        $items = $this->validatedItems();

        $this->validate([
            'type' => 'required|in:quotation,proforma,invoice',
            'customer_name' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'document_number' => 'nullable|string|max:50',
            'exchange_rate' => 'nullable|numeric|min:0',
            'logoUpload' => 'nullable|image|max:5120',
        ]);

        validator(['items' => $items], [
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.rate' => 'required|numeric|min:0',
        ])->validate();

        $payload = $this->buildPayload();

        if ($this->documentId) {
            $document = Document::findOrFail($this->documentId);
            $document = $documentService->updateDocument($document, $payload, $items);
        } else {
            $document = $documentService->createDocument($payload, $items, auth()->user()->tenant);
        }

        // Prevent duplicate documents if the user clicks Save/Download again
        $this->documentId = $document->id;
        $this->document_number = $document->document_number;

        return $document;
    }

    public function getLogoPreviewUrlProperty(): ?string
    {
        if ($this->logo_path) {
            return Storage::disk('public')->url($this->logo_path);
        }

        if ($this->logoUpload) {
            try {
                return $this->logoUpload->temporaryUrl();
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    protected function buildPayload(): array
    {
        return [
            'document_number' => $this->document_number ?: null,
            'type' => $this->type,
            'customer_id' => $this->customer_id,
            'lead_id' => $this->lead_id,
            'reference_document_id' => $this->reference_document_id,
            'title' => $this->title ?: null,
            'template_key' => $this->template_key,
            'theme_color' => $this->theme_color,
            'currency' => $this->currency,
            'exchange_rate' => $this->exchange_rate,
            'is_gst_applicable' => filter_var($this->is_gst_applicable, FILTER_VALIDATE_BOOLEAN),
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date ?: null,
            'valid_until' => $this->valid_until ?: null,
            'customer_name' => $this->customer_name,
            'customer_gstin' => $this->customer_gstin ?: null,
            'customer_address' => $this->customer_address ?: null,
            'customer_state' => $this->customer_state ?: null,
            'customer_phone' => $this->customer_phone ?: null,
            'customer_email' => $this->customer_email ?: null,
            'logo_path' => $this->logo_path,
            'signature_data' => $this->showSignature ? array_filter([
                'name' => $this->signature_name,
                'title' => $this->signature_title,
                'signature_image' => $this->signature_image_path,
                'stamp_image' => $this->stamp_image_path,
            ]) : null,
            'payment_options' => array_filter([
                'link' => $this->showPaymentLink ? trim($this->payment_link) : null,
                'upi' => $this->showUpi ? trim($this->payment_upi) : null,
                'qr_image' => $this->showQr ? $this->qr_image_path : null,
            ]) ?: null,
            'attachments' => count($this->savedAttachments) ? $this->savedAttachments : null,
            'contact_details' => $this->showContactDetails ? [
                'person' => $this->contact_person,
                'phone' => $this->contact_phone,
                'email' => $this->contact_email,
            ] : null,
            'additional_info' => $this->additional_info ?: null,
            'notes' => $this->notes ?: null,
            'terms_conditions' => $this->terms_conditions ?: null,
            'doc_discount_type' => $this->doc_discount_type,
            'doc_discount_value' => $this->doc_discount_value ?? 0,
            'additional_charges' => $this->additional_charges,
            'advanced_options' => $this->advanced_options,
            'shipping_details' => $this->showShipping ? ['address' => $this->shipping_address] : null,
        ];
    }

    public function render()
    {
        $customers = Customer::orderBy('name')->get();
        $leads = Lead::latest()->limit(50)->get(['id', 'name', 'phone']);
        $products = Product::query()
            ->when($this->productSearch, fn ($q) => $q->where('name', 'like', "%{$this->productSearch}%"))
            ->where('is_active', true)
            ->limit(8)
            ->get();

        $templates = config('document-templates', []);
        $totals = $this->previewTotals;
        $typeLabels = ['quotation' => 'Quotation', 'proforma' => 'Proforma Invoice', 'invoice' => 'Tax Invoice'];
        $lastNumber = Schema::hasTable('documents')
            ? Document::where('type', $this->type)->latest('id')->value('document_number')
            : null;
        $tenant = auth()->user()->tenant;
        $suggestedNumber = Schema::hasTable('documents')
            ? app(DocumentService::class)->generateNumber($this->type, $tenant->id)
            : 'QUO-'.date('Y').'-001';

        return view('livewire.documents.create', compact(
            'customers', 'leads', 'products', 'templates', 'totals', 'typeLabels', 'lastNumber', 'tenant', 'suggestedNumber'
        ))->layout('layouts.app');
    }
}
