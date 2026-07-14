<div>
    @include('layouts.partials.leads-nav')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
        <div><h1 class="text-2xl font-bold">Broadcasts / Campaigns</h1><p class="text-gray-500 text-sm">Bulk WhatsApp & Email campaigns</p></div>
        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search campaigns..." class="px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
            <button wire:click="$set('showLimitsModal', true)" class="px-3 py-2 border rounded-lg text-sm">📊 Service Limits</button>
            <button wire:click="$refresh" class="px-3 py-2 border rounded-lg text-sm">🔄 Refresh</button>
            <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New Campaign</button>
        </div>
    </div>

    {{-- Campaign Metrics --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border dark:border-gray-700">
            <div class="text-xs text-gray-500">Emails Sent</div>
            <div class="text-2xl font-bold text-indigo-600">{{ $metrics['emails_sent'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border dark:border-gray-700">
            <div class="text-xs text-gray-500">Emails Delivered</div>
            <div class="text-2xl font-bold text-green-600">{{ $metrics['emails_delivered'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border dark:border-gray-700">
            <div class="text-xs text-gray-500">WhatsApp Sent</div>
            <div class="text-2xl font-bold text-green-600">{{ $metrics['whatsapp_sent'] }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border dark:border-gray-700">
            <div class="text-xs text-gray-500">Last 7 Days</div>
            <div class="text-2xl font-bold text-purple-600">{{ $metrics['last_7_days'] }}</div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-2 mb-4">
        <button wire:click="$set('tab', 'current')" class="px-4 py-2 rounded-lg text-sm {{ $tab === 'current' ? 'bg-indigo-600 text-white' : 'border' }}">Current Campaigns</button>
        <button wire:click="$set('tab', 'scheduled')" class="px-4 py-2 rounded-lg text-sm {{ $tab === 'scheduled' ? 'bg-indigo-600 text-white' : 'border' }}">Scheduled Campaigns</button>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700"><tr>
                <th class="px-4 py-3 text-left">Name</th><th class="px-4 py-3 text-left">Channel</th><th class="px-4 py-3 text-left">Recipients</th>
                <th class="px-4 py-3 text-left">Status</th><th class="px-4 py-3 text-left">Delivered</th><th class="px-4 py-3 text-left">Actions</th>
            </tr></thead>
            <tbody>
            @forelse($broadcasts as $b)
            <tr class="border-t dark:border-gray-700">
                <td class="px-4 py-3 font-medium">{{ $b->name }}</td>
                <td class="px-4 py-3">{{ ucfirst($b->channel) }}</td>
                <td class="px-4 py-3">{{ $b->total_recipients }}</td>
                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs bg-gray-100">{{ ucfirst($b->status) }}</span></td>
                <td class="px-4 py-3">{{ $b->delivered_count }} / {{ $b->opened_count }} opened</td>
                <td class="px-4 py-3">@if($b->status !== 'sent')<button wire:click="sendNow({{ $b->id }})" wire:confirm="Send now?" class="text-indigo-600 text-sm">Send Now</button>@endif</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-500">No campaigns in this tab</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Service Limits Modal --}}
    @if($showLimitsModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-sm p-6">
            <h3 class="font-bold mb-4">Service Limits</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg"><span>💬 WhatsApp</span><span class="font-semibold">1,000 / month</span></div>
                <div class="flex justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"><span>✉️ Email</span><span class="font-semibold">5,000 / month</span></div>
                <div class="text-xs text-gray-500">Used: WhatsApp {{ $metrics['whatsapp_sent'] }}, Email {{ $metrics['emails_sent'] }}</div>
            </div>
            <button wire:click="$set('showLimitsModal', false)" class="w-full mt-4 py-2 border rounded-lg text-sm">Close</button>
        </div>
    </div>
    @endif

    {{-- Create Wizard --}}
    @if($showWizard)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl w-full max-w-lg p-6">
            <div class="flex gap-2 mb-6">
                @foreach([1=>'Details',2=>'Template',3=>'Recipients',4=>'Schedule'] as $step => $label)
                <div class="flex-1 text-center text-xs {{ $wizardStep >= $step ? 'text-indigo-600 font-semibold' : 'text-gray-400' }}">{{ $label }}</div>
                @endforeach
            </div>

            @if($wizardStep === 1)
            <h3 class="font-bold mb-4">Step 1: Campaign Details</h3>
            <div class="space-y-3">
                <select wire:model="channel" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"><option value="whatsapp">WhatsApp</option><option value="email">Email</option></select>
                <input wire:model="name" placeholder="Campaign name *" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="sendFromPhone" placeholder="Send from phone (WhatsApp)" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <input wire:model="replyBot" placeholder="Reply bot (optional)" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            </div>
            @elseif($wizardStep === 2)
            <h3 class="font-bold mb-4">Step 2: Template</h3>
            <select wire:model="templateId" class="w-full px-3 py-2 border rounded-lg mb-3 dark:bg-gray-700 dark:border-gray-600"><option value="">Select template</option>@foreach($templates as $t)<option value="{{ $t->id }}">{{ $t->name }}</option>@endforeach</select>
            <textarea wire:model="message" rows="4" placeholder="Or write custom message" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            @elseif($wizardStep === 3)
            <h3 class="font-bold mb-4">Step 3: Recipients</h3>
            <select wire:model="recipientFilter" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                <option value="all">All leads with phone/email</option>
                <option value="hot">Hot leads only</option>
                <option value="new">New leads only</option>
            </select>
            <p class="text-sm text-gray-500 mt-2">Estimated recipients: {{ \App\Models\Lead::whereNotNull('phone')->count() }}</p>
            @else
            <h3 class="font-bold mb-4">Step 4: Schedule</h3>
            <input type="datetime-local" wire:model="scheduleAt" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <p class="text-xs text-gray-500 mt-2">Leave empty to save as draft</p>
            @endif

            <div class="flex gap-2 mt-6">
                @if($wizardStep > 1)<button wire:click="$set('wizardStep', {{ $wizardStep - 1 }})" class="px-4 py-2 border rounded-lg text-sm">Back</button>@endif
                @if($wizardStep < 4)
                <button wire:click="nextStep" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg text-sm">Next</button>
                @else
                <button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg text-sm">Create Campaign</button>
                @endif
                <button wire:click="$set('showWizard', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>
