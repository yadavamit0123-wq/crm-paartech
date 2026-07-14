<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMilestone extends Model
{
    protected $fillable = [
        'payment_plan_id', 'name', 'percentage', 'amount', 'trigger_event',
        'sort_order', 'status', 'due_date', 'approved_at', 'approved_by',
        'payment_link', 'payment_qr_url', 'razorpay_order_id', 'razorpay_payment_link_id',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'decimal:2',
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PaymentPlan::class, 'payment_plan_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function canGenerateLink(): bool
    {
        return in_array($this->status, ['approved', 'link_sent', 'paid']);
    }
}
