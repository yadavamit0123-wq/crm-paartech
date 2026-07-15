<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Demo extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'url', 'message', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
