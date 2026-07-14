<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\Tenant;
use Livewire\Component;

class LeadSources extends Component
{
    public array $sources = [];

    public function mount(): void
    {
        $connected = auth()->user()->tenant->settings['connected_sources'] ?? [
            'Google Sheets', 'Facebook Lead Ads', 'Website Form', 'WhatsApp Business', 'CSV Import', 'API Webhook', 'Manual Entry',
        ];

        $all = [
            ['name' => '99Acres', 'icon' => '🏘️', 'desc' => 'Property inquiry sync'],
            ['name' => 'My Operator', 'icon' => '📞', 'desc' => 'Cloud telephony leads'],
            ['name' => 'Zapier', 'icon' => '⚡', 'desc' => 'Connect 5000+ apps'],
            ['name' => 'Acefone', 'icon' => '☎️', 'desc' => 'Virtual phone system'],
            ['name' => 'Pabbly', 'icon' => '🔗', 'desc' => 'Workflow automation'],
            ['name' => 'JustDial', 'icon' => '📞', 'desc' => 'Local business leads'],
            ['name' => 'TradeIndia', 'icon' => '🏢', 'desc' => 'B2B trade inquiries'],
            ['name' => 'Exporters India', 'icon' => '🌏', 'desc' => 'Export/import leads'],
            ['name' => 'Housing', 'icon' => '🏠', 'desc' => 'Real estate inquiries'],
            ['name' => 'Facebook Lead Ads', 'icon' => '📘', 'desc' => 'Meta lead forms'],
            ['name' => 'IndiaMART', 'icon' => '🏭', 'desc' => 'B2B inquiry sync'],
            ['name' => 'Google Sheets', 'icon' => '📊', 'desc' => 'Auto-sync from spreadsheet'],
            ['name' => 'Website Form', 'icon' => '🌐', 'desc' => 'API webhook active'],
            ['name' => 'WhatsApp Business', 'icon' => '💬', 'desc' => 'Inbound chat to lead'],
            ['name' => 'CSV Import', 'icon' => '📤', 'desc' => 'Manual bulk upload', 'route' => 'leads.bulk-upload'],
            ['name' => 'API Webhook', 'icon' => '🔗', 'desc' => 'POST /api/leads/capture', 'route' => 'integrations.index'],
            ['name' => 'Manual Entry', 'icon' => '✏️', 'desc' => 'Add lead from CRM', 'route' => 'leads.create'],
        ];

        $this->sources = array_map(function ($s) use ($connected) {
            $s['status'] = in_array($s['name'], $connected) ? 'connected' : 'available';

            return $s;
        }, $all);
    }

    public function connect(string $name): void
    {
        $tenant = auth()->user()->tenant;
        $settings = $tenant->settings ?? [];
        $connected = $settings['connected_sources'] ?? [];

        if (! in_array($name, $connected)) {
            $connected[] = $name;
            $settings['connected_sources'] = $connected;
            $tenant->update(['settings' => $settings]);
        }

        $this->mount();
        $this->dispatch('notify', message: "{$name} connected successfully!");
    }

    public function testSync(string $name): void
    {
        $stage = LeadStage::ensureDefault();

        Lead::create([
            'tenant_id' => auth()->user()->tenant_id,
            'lead_stage_id' => $stage->id,
            'created_by' => auth()->id(),
            'assigned_to' => auth()->id(),
            'name' => 'Test Lead from '.$name,
            'phone' => '9'.rand(100000000, 999999999),
            'email' => 'test.'.strtolower(str_replace(' ', '', $name)).'@example.com',
            'source' => strtolower(str_replace(' ', '_', $name)),
            'company' => 'Synced via '.$name,
        ]);

        $this->dispatch('notify', message: "Test lead synced from {$name}!");
    }

    public function render()
    {
        return view('livewire.leads.lead-sources')
            ->layout('layouts.app');
    }
}
