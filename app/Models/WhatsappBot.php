<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class WhatsappBot extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'flow_data', 'field_mapping',
        'trigger_keyword', 'is_active', 'new_leads_only', 'sessions_count',
    ];

    protected function casts(): array
    {
        return [
            'flow_data' => 'array',
            'field_mapping' => 'array',
            'is_active' => 'boolean',
            'new_leads_only' => 'boolean',
        ];
    }
}
