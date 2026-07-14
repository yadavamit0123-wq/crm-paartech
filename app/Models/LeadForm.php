<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadForm extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'lead_list_id', 'created_by', 'name', 'description', 'slug', 'leads_count', 'status',
    ];

    public function leadList(): BelongsTo
    {
        return $this->belongsTo(LeadList::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
