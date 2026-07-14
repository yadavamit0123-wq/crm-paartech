<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'source', 'event', 'payload', 'status', 'lead_id', 'error_message',
    ];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
