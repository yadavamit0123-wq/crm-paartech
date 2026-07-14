<?php

namespace App\Livewire\Marketing;

use App\Services\MarketingService;
use Livewire\Component;

class Dashboard extends Component
{
    public function mount(): void
    {
        if (! auth()->user()->hasPermission('marketing.view')) {
            abort(403);
        }
    }

    public function render()
    {
        $stats = app(MarketingService::class)->getMarketingStats(auth()->user()->tenant_id);

        $upcomingPosts = \App\Models\SocialPost::where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $activeCampaigns = \App\Models\AdCampaign::where('status', 'active')
            ->latest()
            ->limit(5)
            ->get();

        return view('livewire.marketing.dashboard', compact('stats', 'upcomingPosts', 'activeCampaigns'))
            ->layout('layouts.app');
    }
}
