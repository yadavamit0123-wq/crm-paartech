<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'lead_id', 'customer_id', 'created_by', 'order_number', 'status',
        'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items->sum('total');
        $tax = $this->items->sum(fn ($i) => $i->total * ($i->tax_rate / 100));
        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => round($tax, 2),
            'total_amount' => round($subtotal + $tax - $this->discount_amount, 2),
        ]);
    }
}
