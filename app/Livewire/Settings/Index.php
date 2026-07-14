<?php

namespace App\Livewire\Settings;

use App\Models\Tenant;
use Livewire\Component;

class Index extends Component
{
    public string $orgName = '';
    public string $email = '';
    public string $phone = '';
    public string $gstin = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public bool $duplicateCheck = true;
    public bool $callLogRestrict = false;
    public bool $leadEditLock = false;
    public string $leadPrefix = 'LD';
    public string $whatsappNumber = '';
    public array $whatsappTemplates = [];

    public string $newTemplate = '';

    public function mount(): void
    {
        if (! auth()->user()->hasPermission('settings.manage')) {
            abort(403);
        }

        $tenant = auth()->user()->tenant;
        $settings = $tenant->settings ?? [];

        $this->orgName = $tenant->name;
        $this->email = $tenant->email ?? '';
        $this->phone = $tenant->phone ?? '';
        $this->gstin = $tenant->gstin ?? '';
        $this->address = $tenant->address ?? '';
        $this->city = $tenant->city ?? '';
        $this->state = $tenant->state ?? '';
        $this->duplicateCheck = $settings['duplicate_check'] ?? true;
        $this->callLogRestrict = $settings['call_log_restrict'] ?? false;
        $this->leadEditLock = $settings['lead_edit_lock'] ?? false;
        $this->leadPrefix = $settings['lead_prefix'] ?? 'LD';
        $this->whatsappNumber = $settings['whatsapp_number'] ?? '';
        $this->whatsappTemplates = $settings['whatsapp_templates'] ?? [];
    }

    public function addTemplate(): void
    {
        if ($this->newTemplate) {
            $this->whatsappTemplates[] = $this->newTemplate;
            $this->newTemplate = '';
        }
    }

    public function removeTemplate(int $index): void
    {
        unset($this->whatsappTemplates[$index]);
        $this->whatsappTemplates = array_values($this->whatsappTemplates);
    }

    public function save(): void
    {
        $tenant = auth()->user()->tenant;

        $tenant->update([
            'name' => $this->orgName,
            'email' => $this->email,
            'phone' => $this->phone,
            'gstin' => $this->gstin,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'settings' => array_merge($tenant->settings ?? [], [
                'duplicate_check' => $this->duplicateCheck,
                'call_log_restrict' => $this->callLogRestrict,
                'lead_edit_lock' => $this->leadEditLock,
                'lead_prefix' => $this->leadPrefix,
                'whatsapp_number' => $this->whatsappNumber,
                'whatsapp_templates' => $this->whatsappTemplates,
            ]),
        ]);

        $this->dispatch('notify', message: 'Organisation settings saved');
    }

    public function render()
    {
        return view('livewire.settings.index')
            ->layout('layouts.app');
    }
}
