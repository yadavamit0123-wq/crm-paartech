<?php

namespace App\Livewire\SeoAudit;

use App\Models\SeoAudit as SeoAuditModel;
use App\Services\SeoAuditService;
use Livewire\Component;

class Index extends Component
{
    public string $url = '';
    public ?SeoAuditModel $latestAudit = null;
    public bool $auditing = false;

    public function mount(): void
    {
        $this->latestAudit = SeoAuditModel::latest()->first();
        $this->url = auth()->user()->tenant?->settings['website_url']
            ?? (auth()->user()->tenant?->custom_domain ? 'https://'.auth()->user()->tenant->custom_domain : '');
    }

    public function runAudit(SeoAuditService $service): void
    {
        if (! auth()->user()->hasPermission('seo.audit')) {
            abort(403);
        }

        if (! str_starts_with($this->url, 'http')) {
            $this->url = 'https://'.$this->url;
        }

        $this->validate(['url' => 'required|url']);

        $this->auditing = true;
        $this->latestAudit = $service->audit($this->url, auth()->user()->tenant_id);
        $this->auditing = false;

        $this->dispatch('notify', message: "SEO audit complete — Score: {$this->latestAudit->score}/100");
    }

    public function loadAudit(int $id): void
    {
        $this->latestAudit = SeoAuditModel::findOrFail($id);
        $this->url = $this->latestAudit->url;
    }

    public function render()
    {
        $history = SeoAuditModel::latest()->limit(10)->get();

        return view('livewire.seo-audit.index', compact('history'))
            ->layout('layouts.app');
    }
}
