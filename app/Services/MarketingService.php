<?php

namespace App\Services;

use App\Models\AdCampaign;
use App\Models\Lead;
use App\Models\SeoAudit;
use App\Models\SocialPost;

class MarketingService
{
    public function publishDuePosts(): int
    {
        $posts = SocialPost::withoutGlobalScopes()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($posts as $post) {
            $this->markPublished($post);
            $count++;
        }

        return $count;
    }

    public function markPublished(SocialPost $post): SocialPost
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $post;
    }

    public function syncCampaignLeads(AdCampaign $campaign): AdCampaign
    {
        $sourceMap = [
            'google' => 'google',
            'meta' => 'meta',
            'whatsapp' => 'whatsapp',
        ];

        $source = $sourceMap[$campaign->platform] ?? $campaign->platform;

        $query = Lead::withoutGlobalScopes()
            ->where('tenant_id', $campaign->tenant_id)
            ->where('source', $source);

        if ($campaign->start_date) {
            $query->whereDate('created_at', '>=', $campaign->start_date);
        }
        if ($campaign->end_date) {
            $query->whereDate('created_at', '<=', $campaign->end_date);
        }

        $campaign->leads_count = $query->count();
        $campaign->syncCostPerLead();
        $campaign->save();

        return $campaign;
    }

    public function getMarketingStats(int $tenantId): array
    {
        return [
            'scheduled_posts' => SocialPost::where('tenant_id', $tenantId)->where('status', 'scheduled')->count(),
            'published_posts' => SocialPost::where('tenant_id', $tenantId)->where('status', 'published')->count(),
            'active_campaigns' => AdCampaign::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'total_ad_spend' => AdCampaign::where('tenant_id', $tenantId)->sum('spend'),
            'total_ad_leads' => AdCampaign::where('tenant_id', $tenantId)->sum('leads_count'),
            'latest_seo_score' => SeoAudit::where('tenant_id', $tenantId)->latest()->value('score') ?? 0,
        ];
    }
}
