<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'customer_id', 'lead_id', 'reference_document_id', 'conversion_path', 'type',
        'document_number', 'title', 'template_key', 'theme_color', 'currency', 'status', 'is_gst_applicable',
        'issue_date', 'due_date', 'valid_until', 'subtotal', 'discount_amount', 'discount_percent',
        'doc_discount_type', 'doc_discount_value', 'taxable_amount', 'cgst_amount', 'sgst_amount',
        'igst_amount', 'total_tax', 'grand_total', 'total_in_words', 'place_of_supply',
        'customer_gstin', 'customer_name', 'customer_address', 'customer_state', 'customer_phone', 'customer_email',
        'seller_snapshot', 'logo_path', 'signature_data', 'payment_options', 'attachments', 'contact_details', 'additional_info',
        'exchange_rate', 'additional_charges', 'notes', 'terms_conditions', 'advanced_options', 'shipping_details',
        'created_by', 'sent_at', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'is_gst_applicable' => 'boolean',
            'issue_date' => 'date',
            'due_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'taxable_amount' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_amount' => 'decimal:2',
            'total_tax' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'conversion_path' => 'array',
            'seller_snapshot' => 'array',
            'additional_charges' => 'array',
            'advanced_options' => 'array',
            'shipping_details' => 'array',
            'signature_data' => 'array',
            'payment_options' => 'array',
            'attachments' => 'array',
            'contact_details' => 'array',
            'doc_discount_value' => 'decimal:2',
            'exchange_rate' => 'decimal:6',
        ];
    }

    public function childDocuments(): HasMany
    {
        return $this->hasMany(Document::class, 'reference_document_id');
    }

    public function workflowSteps(): array
    {
        return $this->conversion_path ?? [];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function referenceDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'reference_document_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DocumentItem::class)->orderBy('sort_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'quotation' => 'Quotation',
            'proforma' => 'Proforma Invoice',
            'invoice' => 'Tax Invoice',
            default => ucfirst($this->type),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'accepted' => 'indigo',
            'paid' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
