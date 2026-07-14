<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Social Media / सोशल मीडिया</h1>
            <p class="text-gray-500 text-sm">Schedule posts for Facebook, Instagram, LinkedIn, X, GMB</p>
        </div>
        <button wire:click="$set('showForm', true)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ New Post</button>
    </div>

    @if($showForm)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">Create Post / पोस्ट बनाएं</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <select wire:model="platform" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                @foreach($platforms as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" wire:model="title" placeholder="Title (optional)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <textarea wire:model="content" rows="4" placeholder="Post content *" class="md:col-span-2 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
            <input type="url" wire:model="link_url" placeholder="Link URL (optional)" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="datetime-local" wire:model="scheduled_at" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
        </div>
        <p class="text-xs text-gray-500 mt-2">Schedule time set karo ya khali chhodo draft ke liye. Publish ke liye manually platform par post karein ya "Publish Now" dabayein.</p>
        <div class="flex gap-2 mt-4">
            <button wire:click="save" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Save / Schedule</button>
            <button wire:click="$set('showForm', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
        </div>
    </div>
    @endif

    <div class="flex gap-2 mb-4">
        <select wire:model.live="filterStatus" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
            <option value="">All Status</option>
            @foreach(['draft','scheduled','published','failed'] as $s)
            <option value="{{ $s }}">{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($posts as $post)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700">
            <div class="flex justify-between items-start mb-2">
                <span class="px-2 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded text-xs">{{ $post->platformLabel() }}</span>
                <span class="text-xs px-2 py-0.5 rounded {{ $post->status === 'published' ? 'bg-green-100 text-green-700' : ($post->status === 'scheduled' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100') }}">{{ ucfirst($post->status) }}</span>
            </div>
            @if($post->title)<div class="font-medium text-sm mb-1">{{ $post->title }}</div>@endif
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-3">{{ Str::limit($post->content, 120) }}</p>
            @if($post->scheduled_at)<div class="text-xs text-gray-500 mb-2">📅 {{ $post->scheduled_at->format('d M Y H:i') }}</div>@endif
            @if($post->published_at)<div class="text-xs text-green-600 mb-2">✓ Published {{ $post->published_at->format('d M Y') }}</div>@endif
            <div class="flex gap-2">
                @if($post->status !== 'published')
                <button wire:click="publishNow({{ $post->id }})" class="text-xs text-green-600">Publish Now</button>
                @endif
                <button wire:click="delete({{ $post->id }})" wire:confirm="Delete?" class="text-xs text-red-500">Delete</button>
            </div>
        </div>
        @empty
        <div class="md:col-span-3 text-center py-12 text-gray-500">No posts yet</div>
        @endforelse
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</div>
