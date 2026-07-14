<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'entity_type', 'label', 'field_key', 'field_type', 'options', 'is_required', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }
}
