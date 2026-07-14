<?php

namespace App\Livewire\AdCampaigns;

use App\Models\AdCampaign;
use App\Services\MarketingService;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public ?int $editingId = null;
    public string $platform = 'google';
    public string $name = '';
    public string $external_campaign_id = '';
    public float $budget = 0;
    public float $spend = 0;
    public int $impressions = 0;
    public int $clicks = 0;
    public int $leads_count = 0;
    public string $status = 'draft';
    public string $start_date = '';
    public string $end_date = '';
    public string $notes = '';
    public string $filterPlatform = '';

    public function save(): void
    {
        if (! auth()->user()->hasPermission('ads.manage')) {
            abort(403);
        }

        $this->validate([
            'platform' => 'required|in:google,meta,whatsapp,linkedin,other',
            'name' => 'required|string|max:255',
            'budget' => 'nullable|numeric|min:0',
            'spend' => 'nullable|numeric|min:0',
        ]);

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'platform' => $this->platform,
            'name' => $this->name,
            'external_campaign_id' => $this->external_campaign_id ?: null,
            'budget' => $this->budget,
            'spend' => $this->spend,
            'impressions' => $this->impressions,
            'clicks' => $this->clicks,
            'leads_count' => $this->leads_count,
            'status' => $this->status,
            'start_date' => $this->start_date ?: null,
            'end_date' => $this->end_date ?: null,
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
        ];

        if ($this->editingId) {
            $campaign = AdCampaign::findOrFail($this->editingId);
            unset($data['created_by'], $data['tenant_id']);
            $campaign->update($data);
        } else {
            $campaign = AdCampaign::create($data);
        }

        $campaign->syncCostPerLead();
        $campaign->save();

        $this->resetForm();
        $this->dispatch('notify', message: 'Campaign saved / Campaign save ho gaya');
    }

    public function edit(int $id): void
    {
        $c = AdCampaign::findOrFail($id);
        $this->editingId = $c->id;
        $this->platform = $c->platform;
        $this->name = $c->name;
        $this->external_campaign_id = $c->external_campaign_id ?? '';
        $this->budget = (float) $c->budget;
        $this->spend = (float) $c->spend;
        $this->impressions = $c->impressions;
        $this->clicks = $c->clicks;
        $this->leads_count = $c->leads_count;
        $this->status = $c->status;
        $this->start_date = $c->start_date?->format('Y-m-d') ?? '';
        $this->end_date = $c->end_date?->format('Y-m-d') ?? '';
        $this->notes = $c->notes ?? '';
        $this->showForm = true;
    }

    public function syncLeads(int $id, MarketingService $marketing): void
    {
        $campaign = AdCampaign::findOrFail($id);
        $marketing->syncCampaignLeads($campaign);
        $this->dispatch('notify', message: "Leads synced: {$campaign->leads_count} from CRM");
    }

    public function delete(int $id): void
    {
        AdCampaign::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Campaign deleted');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'platform', 'name', 'external_campaign_id', 'budget', 'spend', 'impressions', 'clicks', 'leads_count', 'status', 'start_date', 'end_date', 'notes', 'showForm']);
        $this->platform = 'google';
        $this->status = 'draft';
    }

    public function render()
    {
        $query = AdCampaign::latest();
        if ($this->filterPlatform) {
            $query->where('platform', $this->filterPlatform);
        }

        $campaigns = $query->paginate(10);
        $platforms = AdCampaign::platforms();
        $totals = [
            'spend' => AdCampaign::sum('spend'),
            'leads' => AdCampaign::sum('leads_count'),
            'clicks' => AdCampaign::sum('clicks'),
        ];

        return view('livewire.ad-campaigns.index', compact('campaigns', 'platforms', 'totals'))
            ->layout('layouts.app');
    }
}
