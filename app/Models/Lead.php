<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'lead_stage_id', 'lead_label_id', 'lead_list_id', 'assigned_to', 'created_by',
        'name', 'email', 'phone', 'alternate_phone', 'company', 'designation',
        'source', 'campaign', 'service_type', 'city', 'state', 'country', 'address',
        'value', 'priority', 'status', 'notes', 'custom_fields',
        'last_contacted_at', 'last_call_at', 'next_follow_up_at', 'converted_at', 'is_customer',
        'company_profile', 'website', 'gstin',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'value' => 'decimal:2',
            'last_contacted_at' => 'datetime',
            'last_call_at' => 'datetime',
            'next_follow_up_at' => 'datetime',
            'converted_at' => 'datetime',
            'is_customer' => 'boolean',
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(LeadStage::class, 'lead_stage_id');
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(LeadLabel::class, 'lead_label_id');
    }

    public function leadList(): BelongsTo
    {
        return $this->belongsTo(LeadList::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function stickyNotes(): HasMany
    {
        return $this->hasMany(LeadNote::class)->where('is_sticky', true);
    }

    // NOTE: naam leadNotes hai kyunki leads table me 'notes' text column already hai —
    // same naam ki relation attribute se clash karti hai ($lead->notes column return karta hai)
    public function leadNotes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(LeadReminder::class);
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(LeadRecording::class);
    }

    public function chatMessages(): HasMany
    {
        return $this->hasMany(LeadChatMessage::class)->oldest();
    }

    public function forwards(): HasMany
    {
        return $this->hasMany(LeadForward::class)->latest();
    }

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(LeadTask::class)->latest();
    }

    public function openTasks(): HasMany
    {
        return $this->hasMany(LeadTask::class)->where('status', 'pending');
    }

    public function hasCallLogged(): bool
    {
        return $this->activities()->where('type', 'call')->exists();
    }

    public function isUntouched(): bool
    {
        return is_null($this->last_contacted_at);
    }

    public function isStale(int $days = 30): bool
    {
        return $this->created_at->lt(now()->subDays($days))
            && (is_null($this->last_contacted_at) || $this->last_contacted_at->lt(now()->subDays($days)));
    }

    public function logActivity(string $type, string $title, ?string $description = null, ?array $meta = null): LeadActivity
    {
        return $this->activities()->create([
            'tenant_id' => $this->tenant_id,
            'user_id' => auth()->id(),
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'meta' => $meta,
        ]);
    }
}
