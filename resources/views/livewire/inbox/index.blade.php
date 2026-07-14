<div>
    @include('layouts.partials.leads-nav')

    <div class="flex gap-2 mb-4">
        <button wire:click="$set('view', 'setup')" class="px-4 py-2 rounded-lg text-sm {{ $view === 'setup' ? 'bg-indigo-600 text-white' : 'border' }}">Setup</button>
        <button wire:click="$set('view', 'inbox')" class="px-4 py-2 rounded-lg text-sm {{ $view === 'inbox' ? 'bg-indigo-600 text-white' : 'border' }}">Inbox</button>
    </div>

    @if($view === 'setup' || !$whatsappConnected)
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">WhatsApp Business API Setup</h1>
        <p class="text-gray-500 text-sm mb-6">Connect your messaging channels to receive leads in one inbox</p>

        <div class="grid md:grid-cols-3 gap-4 mb-8">
            @foreach([
                ['name' => 'WhatsApp', 'icon' => '💬', 'color' => 'green', 'connected' => $whatsappConnected],
                ['name' => 'Instagram', 'icon' => '📸', 'color' => 'pink', 'connected' => false],
                ['name' => 'Messenger', 'icon' => '💭', 'color' => 'blue', 'connected' => false],
            ] as $channel)
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border dark:border-gray-700 shadow-sm">
                <div class="text-3xl mb-3">{{ $channel['icon'] }}</div>
                <h3 class="font-semibold">{{ $channel['name'] }}</h3>
                @if($channel['connected'])
                <span class="inline-flex items-center gap-1 text-xs text-green-600 mt-2">✓ Connected</span>
                @else
                <button wire:click="connectWhatsapp" class="mt-3 px-4 py-2 bg-{{ $channel['color'] }}-600 text-white rounded-lg text-sm">Connect {{ $channel['name'] }}</button>
                @endif
            </div>
            @endforeach
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border dark:border-gray-700">
            <h3 class="font-semibold mb-4">Setup Steps</h3>
            @foreach([
                ['step' => 1, 'title' => 'Connect API', 'desc' => 'Link your WhatsApp Business API credentials', 'done' => $whatsappConnected],
                ['step' => 2, 'title' => 'Add Payment Method', 'desc' => 'Add billing for WhatsApp conversation charges', 'done' => false],
                ['step' => 3, 'title' => 'Verify Business', 'desc' => 'Complete Meta business verification', 'done' => false],
            ] as $s)
            <div class="flex items-center gap-4 py-3 border-b dark:border-gray-700 last:border-0">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold {{ $s['done'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $s['done'] ? '✓' : $s['step'] }}</div>
                <div><div class="font-medium text-sm">{{ $s['title'] }}</div><div class="text-xs text-gray-500">{{ $s['desc'] }}</div></div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($view === 'inbox' && $whatsappConnected)
    <div class="h-[calc(100vh-14rem)] flex bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 overflow-hidden">
    {{-- Conversation List --}}
    <div class="w-full md:w-96 border-r dark:border-gray-700 flex flex-col">
        <div class="p-4 border-b dark:border-gray-700">
            <h1 class="text-xl font-bold">WhatsApp Inbox</h1>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search conversations..." class="mt-2 w-full px-3 py-2 border rounded-lg text-sm dark:bg-gray-700 dark:border-gray-600">
        </div>
        <div class="flex-1 overflow-y-auto">
            @forelse($conversations as $conv)
            <button wire:click="selectConversation({{ $conv->id }})" class="w-full text-left p-4 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 {{ $activeConversationId === $conv->id ? 'bg-indigo-50 dark:bg-indigo-900/20' : '' }}">
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-2">
                        @if($conv->is_pinned)<span class="text-xs">📌</span>@endif
                        <span class="font-semibold text-sm">{{ $conv->contact_name }}</span>
                    </div>
                    @if($conv->unread_count)<span class="bg-green-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $conv->unread_count }}</span>@endif
                </div>
                <div class="text-xs text-gray-500 mt-1">{{ $conv->phone }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $conv->last_message_at?->diffForHumans() }}</div>
            </button>
            @empty
            <p class="p-4 text-gray-500 text-sm">No conversations yet</p>
            @endforelse
        </div>
    </div>

    {{-- Chat Panel --}}
    <div class="flex-1 flex flex-col hidden md:flex">
        @if($active)
        <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center bg-green-50 dark:bg-green-900/10">
            <div>
                <h2 class="font-bold">{{ $active->contact_name }}</h2>
                <p class="text-sm text-gray-500">{{ $active->phone }} @if($active->assignee) • Assigned: {{ $active->assignee->name }}@endif</p>
            </div>
            <div class="flex gap-2">
                <button wire:click="togglePin({{ $active->id }})" class="px-3 py-1 border rounded text-sm">{{ $active->is_pinned ? 'Unpin' : 'Pin' }}</button>
                @if($active->lead)<a href="{{ route('leads.show', $active->lead) }}" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">View Lead</a>@endif
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-[#e5ddd5] dark:bg-gray-900">
            @foreach($active->messages->reverse() as $msg)
            <div class="flex {{ $msg->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-xs px-3 py-2 rounded-lg text-sm {{ $msg->direction === 'outbound' ? 'bg-green-100' : 'bg-white dark:bg-gray-800' }} shadow">
                    {{ $msg->body }}
                    <div class="text-xs text-gray-400 mt-1">{{ $msg->created_at->format('H:i') }}</div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="p-4 border-t dark:border-gray-700 flex gap-2">
            <input type="text" wire:model="replyMessage" wire:keydown.enter="sendReply" placeholder="Type a message..." class="flex-1 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <button wire:click="sendReply" class="px-4 py-2 bg-green-600 text-white rounded-lg">Send</button>
        </div>
        @else
        <div class="flex-1 flex items-center justify-center text-gray-400">
            <div class="text-center"><div class="text-4xl mb-2">💬</div>Select a conversation</div>
        </div>
        @endif
    </div>
    </div>
    @endif
</div>
