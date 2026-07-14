<?php

namespace App\Livewire\SocialPosts;

use App\Models\SocialPost;
use App\Services\MarketingService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $platform = 'facebook';
    public string $title = '';
    public string $content = '';
    public string $link_url = '';
    public string $scheduled_at = '';
    public string $publish_mode = 'manual';
    public bool $showForm = false;
    public string $filterStatus = '';

    public function save(): void
    {
        if (! auth()->user()->hasPermission('social.manage')) {
            abort(403);
        }

        $this->validate([
            'platform' => 'required|in:facebook,instagram,linkedin,twitter,google_business,youtube,other',
            'content' => 'required|string|max:5000',
            'scheduled_at' => 'nullable|date',
        ]);

        $status = $this->scheduled_at ? 'scheduled' : 'draft';

        SocialPost::create([
            'tenant_id' => auth()->user()->tenant_id,
            'platform' => $this->platform,
            'title' => $this->title ?: null,
            'content' => $this->content,
            'link_url' => $this->link_url ?: null,
            'scheduled_at' => $this->scheduled_at ?: null,
            'status' => $status,
            'publish_mode' => $this->publish_mode,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['title', 'content', 'link_url', 'scheduled_at', 'showForm']);
        $this->platform = 'facebook';
        $this->dispatch('notify', message: $status === 'scheduled' ? 'Post scheduled / Post schedule ho gaya' : 'Draft saved');
    }

    public function publishNow(int $id, MarketingService $marketing): void
    {
        $post = SocialPost::findOrFail($id);
        $marketing->markPublished($post);
        $this->dispatch('notify', message: 'Marked as published — ab '.$post->platformLabel().' par manually post karein');
    }

    public function delete(int $id): void
    {
        SocialPost::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Post deleted');
    }

    public function render()
    {
        $query = SocialPost::with('creator')->latest();

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $posts = $query->paginate(12);
        $platforms = SocialPost::platforms();

        return view('livewire.social-posts.index', compact('posts', 'platforms'))
            ->layout('layouts.app');
    }
}
