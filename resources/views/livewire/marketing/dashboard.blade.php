<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Marketing / मार्केटिंग</h1>
        <p class="text-gray-500 text-sm">Social media, SEO & Ads overview</p>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Scheduled Posts</div>
            <div class="text-2xl font-bold text-indigo-600">{{ $stats['scheduled_posts'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Published</div>
            <div class="text-2xl font-bold text-green-600">{{ $stats['published_posts'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Active Campaigns</div>
            <div class="text-2xl font-bold">{{ $stats['active_campaigns'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Ad Spend</div>
            <div class="text-2xl font-bold text-red-600">₹{{ number_format($stats['total_ad_spend'], 0) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">Ad Leads</div>
            <div class="text-2xl font-bold">{{ $stats['total_ad_leads'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700">
            <div class="text-xs text-gray-500">SEO Score</div>
            <div class="text-2xl font-bold text-yellow-500">{{ $stats['latest_seo_score'] }}/100</div>
        </div>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @if(auth()->user()->hasPermission('social.manage'))
        <a href="{{ route('social-posts.index') }}" class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700 hover:border-indigo-500 transition">
            <div class="text-2xl mb-2">📱</div><div class="font-semibold">Social Posts</div><div class="text-xs text-gray-500">Schedule & publish</div>
        </a>
        @endif
        @if(auth()->user()->hasPermission('seo.audit'))
        <a href="{{ route('seo-audit.index') }}" class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700 hover:border-indigo-500 transition">
            <div class="text-2xl mb-2">🔍</div><div class="font-semibold">SEO Audit</div><div class="text-xs text-gray-500">Website analysis</div>
        </a>
        @endif
        @if(auth()->user()->hasPermission('ads.manage'))
        <a href="{{ route('ad-campaigns.index') }}" class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700 hover:border-indigo-500 transition">
            <div class="text-2xl mb-2">📢</div><div class="font-semibold">Ad Campaigns</div><div class="text-xs text-gray-500">Track ROI</div>
        </a>
        @endif
        @if(auth()->user()->hasPermission('integrations.manage'))
        <a href="{{ route('integrations.index') }}" class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700 hover:border-indigo-500 transition">
            <div class="text-2xl mb-2">🔗</div><div class="font-semibold">Integrations</div><div class="text-xs text-gray-500">Lead webhooks</div>
        </a>
        @endif
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">Upcoming Posts</h3>
            @forelse($upcomingPosts as $post)
            <div class="flex justify-between py-2 border-b dark:border-gray-700 last:border-0 text-sm">
                <span>{{ $post->platformLabel() }} — {{ Str::limit($post->content, 40) }}</span>
                <span class="text-gray-500">{{ $post->scheduled_at?->format('d M H:i') }}</span>
            </div>
            @empty
            <p class="text-gray-500 text-sm">No scheduled posts</p>
            @endforelse
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">Active Ad Campaigns</h3>
            @forelse($activeCampaigns as $campaign)
            <div class="flex justify-between py-2 border-b dark:border-gray-700 last:border-0 text-sm">
                <span>{{ $campaign->platformLabel() }} — {{ $campaign->name }}</span>
                <span class="text-gray-500">₹{{ number_format($campaign->spend, 0) }} • {{ $campaign->leads_count }} leads</span>
            </div>
            @empty
            <p class="text-gray-500 text-sm">No active campaigns</p>
            @endforelse
        </div>
    </div>
</div>
