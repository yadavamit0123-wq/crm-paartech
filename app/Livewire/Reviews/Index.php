<?php

namespace App\Livewire\Reviews;

use App\Models\GoogleReview;
use App\Services\ReviewReplyService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $reviewer_name = '';
    public int $rating = 5;
    public string $review_text = '';
    public bool $showAddForm = false;
    public ?int $editingReplyId = null;
    public string $editReplyText = '';

    public function addReview(ReviewReplyService $replyService): void
    {
        $this->validate([
            'reviewer_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:2000',
        ]);

        $review = GoogleReview::create([
            'tenant_id' => auth()->user()->tenant_id,
            'reviewer_name' => $this->reviewer_name,
            'rating' => $this->rating,
            'review_text' => $this->review_text,
        ]);

        if (auth()->user()->tenant->settings['auto_reply_reviews'] ?? true) {
            $replyService->processNewReview($review);
        } else {
            $review->update(['sentiment' => $replyService->detectSentiment($this->rating, $this->review_text)]);
        }

        $this->reset(['reviewer_name', 'rating', 'review_text', 'showAddForm']);
        $this->rating = 5;
        $this->dispatch('notify', message: 'Review added with auto-reply / Review add ho gaya');
    }

    public function regenerateReply(int $reviewId, ReviewReplyService $replyService): void
    {
        $review = GoogleReview::findOrFail($reviewId);
        $reply = $replyService->generateReply($review, auth()->user()->tenant);
        $review->update([
            'reply_text' => $reply,
            'auto_replied' => true,
            'reply_sent_at' => now(),
        ]);
        $this->dispatch('notify', message: 'Reply regenerated');
    }

    public function editReply(int $reviewId): void
    {
        $review = GoogleReview::findOrFail($reviewId);
        $this->editingReplyId = $reviewId;
        $this->editReplyText = $review->reply_text ?? '';
    }

    public function saveReply(): void
    {
        GoogleReview::findOrFail($this->editingReplyId)->update([
            'reply_text' => $this->editReplyText,
            'reply_sent_at' => now(),
        ]);
        $this->editingReplyId = null;
        $this->dispatch('notify', message: 'Reply saved');
    }

    public function render()
    {
        $reviews = GoogleReview::latest()->paginate(10);
        $stats = [
            'total' => GoogleReview::count(),
            'avg' => round(GoogleReview::avg('rating') ?? 0, 1),
            'replied' => GoogleReview::whereNotNull('reply_text')->count(),
        ];

        return view('livewire.reviews.index', compact('reviews', 'stats'))
            ->layout('layouts.app');
    }
}
