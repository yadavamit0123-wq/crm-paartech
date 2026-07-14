<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialPost extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'platform', 'title', 'content', 'media_url', 'link_url',
        'scheduled_at', 'published_at', 'status', 'publish_mode', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function platforms(): array
    {
        return [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'linkedin' => 'LinkedIn',
            'twitter' => 'X (Twitter)',
            'google_business' => 'Google My Business',
            'youtube' => 'YouTube',
            'other' => 'Other',
        ];
    }

    public function platformLabel(): string
    {
        return self::platforms()[$this->platform] ?? ucfirst($this->platform);
    }
}
