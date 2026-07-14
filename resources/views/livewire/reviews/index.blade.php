<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Google Reviews / गूगल रिव्यू</h1>
            <p class="text-gray-500 text-sm">Review requests & auto-replies</p>
        </div>
        <button wire:click="$set('showAddForm', true)" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Add Review</button>
    </div>

    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-center">
            <div class="text-2xl font-bold">{{ $stats['total'] }}</div>
            <div class="text-xs text-gray-500">Total Reviews</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-center">
            <div class="text-2xl font-bold text-yellow-500">⭐ {{ $stats['avg'] }}</div>
            <div class="text-xs text-gray-500">Average Rating</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border dark:border-gray-700 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['replied'] }}</div>
            <div class="text-xs text-gray-500">Replied</div>
        </div>
    </div>

    @if($showAddForm)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">Add Review (manual entry / test auto-reply)</h3>
        <div class="grid md:grid-cols-2 gap-4">
            <input type="text" wire:model="reviewer_name" placeholder="Reviewer Name" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <select wire:model="rating" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
                @for($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }} Stars</option>
                @endfor
            </select>
            <textarea wire:model="review_text" rows="3" placeholder="Review text..." class="md:col-span-2 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600"></textarea>
        </div>
        <div class="flex gap-2 mt-4">
            <button wire:click="addReview" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">Save + Auto Reply</button>
            <button wire:click="$set('showAddForm', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
        </div>
    </div>
    @endif

    <div class="space-y-4">
        @foreach($reviews as $review)
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <div class="flex justify-between items-start gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold">{{ $review->reviewer_name }}</span>
                        <span class="text-yellow-500">{{ str_repeat('⭐', $review->rating) }}</span>
                        <span class="text-xs px-2 py-0.5 rounded {{ $review->sentiment === 'positive' ? 'bg-green-100 text-green-700' : ($review->sentiment === 'negative' ? 'bg-red-100 text-red-700' : 'bg-gray-100') }}">{{ ucfirst($review->sentiment) }}</span>
                    </div>
                    @if($review->review_text)<p class="text-sm text-gray-600 dark:text-gray-300 mb-3">"{{ $review->review_text }}"</p>@endif

                    @if($editingReplyId === $review->id)
                    <textarea wire:model="editReplyText" rows="3" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm mb-2"></textarea>
                    <button wire:click="saveReply" class="text-sm text-green-600 mr-3">Save Reply</button>
                    <button wire:click="$set('editingReplyId', null)" class="text-sm text-gray-500">Cancel</button>
                    @elseif($review->reply_text)
                    <div class="p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border-l-4 border-indigo-500">
                        <div class="text-xs text-indigo-600 font-medium mb-1">{{ $review->auto_replied ? '🤖 Auto Reply' : 'Reply' }} • {{ $review->reply_sent_at?->format('d M Y') }}</div>
                        <p class="text-sm">{{ $review->reply_text }}</p>
                    </div>
                    @endif
                </div>
                <div class="flex flex-col gap-1">
                    <button wire:click="regenerateReply({{ $review->id }})" class="text-xs text-indigo-600 whitespace-nowrap">🔄 Regenerate</button>
                    <button wire:click="editReply({{ $review->id }})" class="text-xs text-gray-600 whitespace-nowrap">✏️ Edit Reply</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $reviews->links() }}</div>
</div>
