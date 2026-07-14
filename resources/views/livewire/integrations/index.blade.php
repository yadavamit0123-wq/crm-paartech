<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Integrations / इंटीग्रेशन</h1>
        <p class="text-gray-500 text-sm">Connect Meta, Google, WhatsApp & Website leads</p>
    </div>

    {{-- Webhook URLs --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">Webhook URLs (copy to respective platforms)</h3>
        <div class="space-y-3">
            @foreach(['website' => 'Website Form API (POST JSON)', 'whatsapp' => 'WhatsApp Cloud API', 'meta' => 'Meta Lead Ads (Facebook/Instagram)', 'google' => 'Google Ads Lead Form'] as $key => $label)
            <div>
                <label class="text-xs text-gray-500 font-medium">{{ $label }}</label>
                <div class="flex gap-2 mt-1">
                    <input type="text" readonly value="{{ $webhooks[$key] }}" class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border rounded-lg text-sm font-mono">
                    <button type="button" onclick="navigator.clipboard.writeText('{{ $webhooks[$key] }}')" class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm">Copy</button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Website Embed --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-2">Website Embed Code / वेबसाइट फॉर्म</h3>
        <p class="text-sm text-gray-500 mb-3">Is code ko apni website ke HTML me paste karein</p>
        <textarea readonly rows="12" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-900 border rounded-lg text-xs font-mono">{{ $embedForm }}</textarea>
    </div>

    {{-- Settings Form --}}
    <form wire:submit="save" class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">API Credentials / क्रेडेंशियल</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <div class="md:col-span-2"><h4 class="text-sm font-medium text-green-600 mb-2">WhatsApp Cloud API</h4></div>
            <input type="text" wire:model="whatsapp_token" placeholder="WhatsApp Access Token" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <input type="text" wire:model="whatsapp_phone_number_id" placeholder="Phone Number ID" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <input type="text" wire:model="whatsapp_verify_token" placeholder="Verify Token (custom string)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm md:col-span-2">

            <div class="md:col-span-2 mt-2"><h4 class="text-sm font-medium text-blue-600 mb-2">Meta (Facebook/Instagram) Lead Ads</h4></div>
            <input type="text" wire:model="meta_page_token" placeholder="Page Access Token" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <input type="text" wire:model="meta_app_secret" placeholder="App Secret" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <input type="text" wire:model="meta_verify_token" placeholder="Verify Token" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm md:col-span-2">

            <div class="md:col-span-2 mt-2"><h4 class="text-sm font-medium text-red-600 mb-2">Google Ads + Reviews</h4></div>
            <input type="text" wire:model="google_ads_webhook_secret" placeholder="Google Ads Webhook Secret" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <input type="text" wire:model="google_place_id" placeholder="Google Place ID" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <input type="url" wire:model="google_review_link" placeholder="Google Review Link (full URL)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm md:col-span-2">

            <div class="md:col-span-2 flex gap-4 mt-2">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="auto_reply_reviews"> Auto-reply to reviews</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" wire:model="auto_reply_openai"> Use OpenAI for replies</label>
            </div>
        </div>
        <button type="submit" class="mt-4 px-6 py-2 bg-indigo-600 text-white rounded-lg">Save Settings</button>
    </form>

    {{-- Webhook Logs --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
        <h3 class="font-semibold mb-4">Recent Webhook Activity</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b dark:border-gray-700">
                    <th class="py-2 text-left">Time</th><th class="py-2 text-left">Source</th><th class="py-2 text-left">Event</th><th class="py-2 text-left">Status</th><th class="py-2 text-left">Lead</th>
                </tr></thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">{{ $log->created_at->format('d M H:i') }}</td>
                        <td class="py-2"><span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs">{{ $log->source }}</span></td>
                        <td class="py-2">{{ $log->event }}</td>
                        <td class="py-2">
                            <span class="text-xs {{ $log->status === 'processed' ? 'text-green-600' : ($log->status === 'failed' ? 'text-red-600' : 'text-gray-500') }}">{{ $log->status }}</span>
                        </td>
                        <td class="py-2">@if($log->lead_id)<a href="{{ route('leads.show', $log->lead_id) }}" class="text-indigo-600">#{{ $log->lead_id }}</a>@else - @endif</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-500">No webhook activity yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
