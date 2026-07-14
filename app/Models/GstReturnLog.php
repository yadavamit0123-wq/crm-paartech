<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GstReturnLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'return_type', 'period', 'status', 'summary',
        'exported_by', 'exported_at', 'filed_at',
    ];

    protected function casts(): array
    {
        return [
            'summary' => 'array',
            'exported_at' => 'datetime',
            'filed_at' => 'datetime',
        ];
    }

    public function exporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}
