<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdCampaign extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'platform', 'name', 'external_campaign_id', 'budget', 'spend',
        'impressions', 'clicks', 'leads_count', 'cost_per_lead', 'status',
        'start_date', 'end_date', 'notes', 'api_meta', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'spend' => 'decimal:2',
            'cost_per_lead' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'api_meta' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function platforms(): array
    {
        return [
            'google' => 'Google Ads',
            'meta' => 'Meta (FB/IG)',
            'whatsapp' => 'WhatsApp Ads',
            'linkedin' => 'LinkedIn Ads',
            'other' => 'Other',
        ];
    }

    public function platformLabel(): string
    {
        return self::platforms()[$this->platform] ?? ucfirst($this->platform);
    }

    public function ctr(): float
    {
        if ($this->impressions === 0) {
            return 0;
        }

        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    public function syncCostPerLead(): void
    {
        if ($this->leads_count > 0) {
            $this->cost_per_lead = round($this->spend / $this->leads_count, 2);
        }
    }
}
