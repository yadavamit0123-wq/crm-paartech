<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadList extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'is_default', 'filter_config',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'filter_config' => 'array',
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function forms(): HasMany
    {
        return $this->hasMany(LeadForm::class);
    }
}
