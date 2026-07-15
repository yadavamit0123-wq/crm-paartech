<?php

namespace App\Livewire\Settings;

use App\Models\Demo;
use App\Support\MeetingTemplates;
use Illuminate\Support\Facades\Schema;
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

    // Bank details (quotation / invoice PDF)
    public string $bankName = '';
    public string $bankAccount = '';
    public string $bankIfsc = '';
    public string $upiId = '';

    public bool $duplicateCheck = true;
    public bool $callLogRestrict = false;
    public bool $leadEditLock = false;
    public string $leadPrefix = 'LD';
    public string $whatsappNumber = '';
    public array $whatsappTemplates = [];

    public string $newTemplate = '';

    // Notifications
    public bool $followupSound = true;

    // Meeting settings
    public string $zoomPersonalLink = '';
    public array $meetingTemplates = [];

    // Zoom Server-to-Server OAuth (live)
    public string $zoomAccountId = '';
    public string $zoomClientId = '';
    public string $zoomClientSecret = '';

    // Google Calendar / Meet OAuth (live)
    public string $googleClientId = '';
    public string $googleClientSecret = '';
    public string $googleRefreshToken = '';

    // Manage Demos
    public string $demoName = '';
    public string $demoUrl = '';
    public string $demoMessage = '';
    public ?int $editingDemoId = null;

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
        $this->bankName = $settings['bank_name'] ?? '';
        $this->bankAccount = $settings['bank_account'] ?? '';
        $this->bankIfsc = $settings['bank_ifsc'] ?? '';
        $this->upiId = $settings['upi_id'] ?? '';
        $this->duplicateCheck = $settings['duplicate_check'] ?? true;
        $this->callLogRestrict = $settings['call_log_restrict'] ?? false;
        $this->leadEditLock = $settings['lead_edit_lock'] ?? false;
        $this->leadPrefix = $settings['lead_prefix'] ?? 'LD';
        $this->whatsappNumber = $settings['whatsapp_number'] ?? '';
        $this->whatsappTemplates = $settings['whatsapp_templates'] ?? [];

        $this->followupSound = $this->resolveFollowupSound($settings);
        $this->zoomPersonalLink = $settings['zoom_personal_link'] ?? '';
        $this->meetingTemplates = MeetingTemplates::forTenant($tenant);

        $this->zoomAccountId = $settings['zoom_account_id'] ?? '';
        $this->zoomClientId = $settings['zoom_client_id'] ?? '';
        $this->zoomClientSecret = $settings['zoom_client_secret'] ?? '';
        $this->googleClientId = $settings['google_client_id'] ?? '';
        $this->googleClientSecret = $settings['google_client_secret'] ?? '';
        $this->googleRefreshToken = $settings['google_refresh_token'] ?? '';
    }

    protected function resolveFollowupSound(array $tenantSettings): bool
    {
        $user = auth()->user();
        if (Schema::hasColumn('users', 'settings')) {
            $userSettings = $user->settings ?? [];
            if (array_key_exists('followup_sound', $userSettings)) {
                return (bool) $userSettings['followup_sound'];
            }
        }

        return (bool) ($tenantSettings['followup_sound'] ?? true);
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

    public function resetMeetingTemplates(): void
    {
        $this->meetingTemplates = MeetingTemplates::defaults();
        $this->dispatch('notify', message: 'Meeting templates reset to defaults — Save karna na bhoolen');
    }

    public function saveDemo(): void
    {
        $this->validate([
            'demoName' => 'required|string|max:100',
            'demoUrl' => 'required|url|max:500',
            'demoMessage' => 'nullable|string|max:2000',
        ]);

        if (! Schema::hasTable('demos')) {
            $this->dispatch('notify', message: 'Demos table abhi ready nahi hai. Server pe chalao: php artisan migrate --force', type: 'error');

            return;
        }

        if ($this->editingDemoId) {
            $demo = Demo::findOrFail($this->editingDemoId);
            $demo->update([
                'name' => $this->demoName,
                'url' => $this->demoUrl,
                'message' => $this->demoMessage ?: null,
            ]);
            $this->dispatch('notify', message: 'Demo updated');
        } else {
            Demo::create([
                'tenant_id' => auth()->user()->tenant_id,
                'name' => $this->demoName,
                'url' => $this->demoUrl,
                'message' => $this->demoMessage ?: null,
                'is_active' => true,
            ]);
            $this->dispatch('notify', message: 'Demo template added');
        }

        $this->reset(['demoName', 'demoUrl', 'demoMessage', 'editingDemoId']);
    }

    public function editDemo(int $demoId): void
    {
        $demo = Demo::findOrFail($demoId);
        $this->editingDemoId = $demo->id;
        $this->demoName = $demo->name;
        $this->demoUrl = $demo->url;
        $this->demoMessage = $demo->message ?? '';
    }

    public function cancelDemoEdit(): void
    {
        $this->reset(['demoName', 'demoUrl', 'demoMessage', 'editingDemoId']);
    }

    public function toggleDemo(int $demoId): void
    {
        $demo = Demo::findOrFail($demoId);
        $demo->update(['is_active' => ! $demo->is_active]);
        $this->dispatch('notify', message: $demo->is_active ? 'Demo activated' : 'Demo deactivated');
    }

    public function deleteDemo(int $demoId): void
    {
        Demo::findOrFail($demoId)->delete();
        if ($this->editingDemoId === $demoId) {
            $this->cancelDemoEdit();
        }
        $this->dispatch('notify', message: 'Demo deleted');
    }

    public function save(): void
    {
        $user = auth()->user();
        $tenant = $user->tenant;

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
                'followup_sound' => $this->followupSound,
                'bank_name' => trim($this->bankName),
                'bank_account' => trim($this->bankAccount),
                'bank_ifsc' => trim($this->bankIfsc),
                'upi_id' => trim($this->upiId),
                'zoom_personal_link' => $this->zoomPersonalLink,
                'meeting_templates' => $this->meetingTemplates,
                'zoom_account_id' => trim($this->zoomAccountId),
                'zoom_client_id' => trim($this->zoomClientId),
                'zoom_client_secret' => trim($this->zoomClientSecret),
                'google_client_id' => trim($this->googleClientId),
                'google_client_secret' => trim($this->googleClientSecret),
                'google_refresh_token' => trim($this->googleRefreshToken),
            ]),
        ]);

        // Per-user preference (users.settings JSON) — column na ho toh tenant fallback kaafi hai
        if (Schema::hasColumn('users', 'settings')) {
            $user->update([
                'settings' => array_merge($user->settings ?? [], [
                    'followup_sound' => $this->followupSound,
                ]),
            ]);
        }

        $this->dispatch('notify', message: 'Settings saved');
    }

    public function render()
    {
        $demos = Schema::hasTable('demos') ? Demo::latest()->get() : collect();
        $templateLabels = MeetingTemplates::labels();
        $meetingStatus = app(\App\Services\MeetingService::class)->status(auth()->user()->tenant);

        return view('livewire.settings.index', compact('demos', 'templateLabels', 'meetingStatus'))
            ->layout('layouts.app');
    }
}
