<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'vendor_name', 'vendor_gstin', 'invoice_number', 'invoice_date',
        'category', 'description', 'is_gst_applicable', 'taxable_amount',
        'cgst_amount', 'sgst_amount', 'igst_amount', 'total_amount', 'gst_rate',
        'payment_status', 'payment_method', 'bill_path', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'is_gst_applicable' => 'boolean',
            'taxable_amount' => 'decimal:2',
            'cgst_amount' => 'decimal:2',
            'sgst_amount' => 'decimal:2',
            'igst_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'gst_rate' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function categories(): array
    {
        return [
            'general' => 'General',
            'office' => 'Office Supplies',
            'rent' => 'Rent',
            'utilities' => 'Utilities',
            'travel' => 'Travel',
            'marketing' => 'Marketing',
            'software' => 'Software/SaaS',
            'professional' => 'Professional Services',
            'raw_material' => 'Raw Material',
            'other' => 'Other',
        ];
    }
}
