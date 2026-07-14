<div>
    @include('layouts.partials.leads-nav')

    <div class="mb-4">
        <h1 class="text-2xl font-bold">AI Assistant</h1>
        <p class="text-gray-500 text-sm">Draft WhatsApp messages, emails & follow-up scripts instantly</p>
    </div>

    <div class="flex gap-2 mb-4">
        @foreach(['whatsapp' => 'WhatsApp', 'email' => 'Email', 'follow_up' => 'Follow-up Script'] as $key => $label)
        <button wire:click="$set('context', '{{ $key }}')" class="px-3 py-1.5 rounded-lg text-sm {{ $context === $key ? 'bg-indigo-600 text-white' : 'border' }}">{{ $label }}</button>
        @endforeach
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border dark:border-gray-700 flex flex-col h-[500px]">
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            @foreach($messages as $msg)
            <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] px-4 py-3 rounded-xl text-sm whitespace-pre-wrap {{ $msg['role'] === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700' }}">
                    {{ $msg['content'] }}
                </div>
            </div>
            @endforeach
        </div>
        <div class="p-4 border-t dark:border-gray-700 flex gap-2">
            <input type="text" wire:model="prompt" wire:keydown.enter="send" placeholder="e.g. Write follow-up for GST filing lead..." class="flex-1 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <button wire:click="send" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Send</button>
        </div>
    </div>
</div>
