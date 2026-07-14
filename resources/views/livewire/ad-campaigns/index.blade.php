<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Ad Campaigns / विज्ञापन</h1>
            <p class="text-gray-500 text-sm">Track Google, Meta, WhatsApp ads & ROI</p>
        </div>
        <button wire:click="$set('showForm', true)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New Campaign</button>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-center">
            <div class="text-xs text-gray-500">Total Spend</div>
            <div class="text-xl font-bold text-red-600">₹{{ number_format($totals['spend'], 0) }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-center">
            <div class="text-xs text-gray-500">Total Leads</div>
            <div class="text-xl font-bold text-green-600">{{ $totals['leads'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-center">
            <div class="text-xs text-gray-500">Total Clicks</div>
            <div class="text-xl font-bold">{{ number_format($totals['clicks']) }}</div>
        </div>
    </div>

    @if($showForm)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">{{ $editingId ? 'Edit' : 'New' }} Campaign</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <select wire:model="platform" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                @foreach($platforms as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" wire:model="name" placeholder="Campaign Name *" class="md:col-span-2 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="text" wire:model="external_campaign_id" placeholder="External Campaign ID (API)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <select wire:model="status" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                @foreach(['draft','active','paused','completed'] as $s)
                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="date" wire:model="start_date" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="date" wire:model="end_date" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="budget" step="0.01" placeholder="Budget ₹" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="spend" step="0.01" placeholder="Spend ₹" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="impressions" placeholder="Impressions" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="clicks" placeholder="Clicks" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="leads_count" placeholder="Leads" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <textarea wire:model="notes" rows="2" placeholder="Notes" class="md:col-span-3 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
        </div>
        <div class="flex gap-2 mt-4">
            <button wire:click="save" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Save Campaign</button>
            <button wire:click="resetForm" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
        </div>
    </div>
    @endif

    <div class="mb-4">
        <select wire:model.live="filterPlatform" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="">All Platforms</option>
            @foreach($platforms as $key => $label)
            <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left">Campaign</th>
                    <th class="px-4 py-3 text-left">Platform</th>
                    <th class="px-4 py-3 text-right">Budget</th>
                    <th class="px-4 py-3 text-right">Spend</th>
                    <th class="px-4 py-3 text-right">Clicks</th>
                    <th class="px-4 py-3 text-right">Leads</th>
                    <th class="px-4 py-3 text-right">CPL</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y dark:divide-gray-700">
                @forelse($campaigns as $campaign)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $campaign->name }}</td>
                    <td class="px-4 py-3">{{ $campaign->platformLabel() }}</td>
                    <td class="px-4 py-3 text-right">₹{{ number_format($campaign->budget, 0) }}</td>
                    <td class="px-4 py-3 text-right">₹{{ number_format($campaign->spend, 0) }}</td>
                    <td class="px-4 py-3 text-right">{{ number_format($campaign->clicks) }} <span class="text-xs text-gray-400">({{ $campaign->ctr() }}%)</span></td>
                    <td class="px-4 py-3 text-right">{{ $campaign->leads_count }}</td>
                    <td class="px-4 py-3 text-right">{{ $campaign->cost_per_lead ? '₹'.number_format($campaign->cost_per_lead, 0) : '-' }}</td>
                    <td class="px-4 py-3"><span class="text-xs px-2 py-0.5 rounded {{ $campaign->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100' }}">{{ ucfirst($campaign->status) }}</span></td>
                    <td class="px-4 py-3">
                        <button wire:click="edit({{ $campaign->id }})" class="text-xs text-indigo-600 mr-2">Edit</button>
                        <button wire:click="syncLeads({{ $campaign->id }})" class="text-xs text-green-600 mr-2">Sync Leads</button>
                        <button wire:click="delete({{ $campaign->id }})" wire:confirm="Delete?" class="text-xs text-red-500">Delete</button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No campaigns yet</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $campaigns->links() }}</div>
    </div>
</div>
