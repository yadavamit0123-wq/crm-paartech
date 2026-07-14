<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoAudit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'url', 'score', 'checks', 'recommendations', 'meta', 'audited_by',
    ];

    protected function casts(): array
    {
        return [
            'checks' => 'array',
            'recommendations' => 'array',
            'meta' => 'array',
        ];
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function scoreColor(): string
    {
        if ($this->score >= 80) {
            return 'green';
        }
        if ($this->score >= 50) {
            return 'yellow';
        }

        return 'red';
    }
}
