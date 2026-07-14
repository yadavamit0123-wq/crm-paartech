<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Automation extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'trigger_type', 'trigger_config', 'actions', 'day_actions',
        'is_active', 'is_draft', 'runs_count', 'last_run_at', 'completed_count', 'error_count', 'leads_affected',
    ];

    protected function casts(): array
    {
        return [
            'trigger_config' => 'array',
            'actions' => 'array',
            'day_actions' => 'array',
            'is_active' => 'boolean',
            'is_draft' => 'boolean',
            'last_run_at' => 'datetime',
        ];
    }
}
