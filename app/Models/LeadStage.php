<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStage extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'slug', 'color', 'sort_order', 'is_won', 'is_lost', 'is_default'];

    protected function casts(): array
    {
        return [
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'lead_stage_id');
    }

    public static function ensureDefault(?int $tenantId = null): self
    {
        $tenantId ??= auth()->user()?->tenant_id;

        $query = static::query();
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $stage = (clone $query)->where('is_default', true)->first()
            ?? (clone $query)->orderBy('sort_order')->first();

        if ($stage) {
            return $stage;
        }

        return static::create([
            'tenant_id' => $tenantId,
            'name' => 'New',
            'slug' => 'new',
            'color' => '#6366f1',
            'sort_order' => 1,
            'is_default' => true,
        ]);
    }
}
