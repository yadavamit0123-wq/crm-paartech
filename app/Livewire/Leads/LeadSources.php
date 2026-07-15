<?php

namespace App\Livewire\Leads;

use App\Services\LeadIngestService;
use Illuminate\Support\Str;
use Livewire\Component;

class LeadSources extends Component
{
    public array $sources = [];

    // Connect wizard state
    public ?string $wizardSource = null;

    public int $wizardStep = 1;

    // Manage panel state
    public ?string $manageSource = null;

    // Shared config form (wizard step 2 + manage panel edit)
    public array $configForm = [];

    public string $configError = '';

    public ?int $lastTestLeadId = null;

    public function mount(): void
    {
        $this->loadSources();
    }

    /**
     * Master catalog of integrations: connection guide + config fields per source.
     * Sources with a 'route' are built-in CRM pages; sources without 'fields'
     * are webhook-only (koi API key nahi chahiye).
     */
    protected function catalog(): array
    {
        return [
            [
                'key' => '99acres', 'name' => '99Acres', 'icon' => '🏘️', 'desc' => 'Property inquiry sync',
                'requirements' => ['Active 99Acres broker/builder subscription', 'API access enabled by your 99Acres account manager'],
                'steps' => [
                    'Login to your 99acres.com business dashboard.',
                    'Apne Relationship Manager se "CRM / XML API integration" request karein — woh aapko API key denge.',
                    'API key niche paste karke Connect dabayein — naye property inquiries auto-sync honge.',
                ],
                'fields' => [
                    ['key' => 'api_key', 'label' => '99Acres API Key', 'required' => true, 'placeholder' => 'e.g. 99acr_xxxxxxxxxxxx'],
                    ['key' => 'profile_id', 'label' => 'Profile ID (optional)', 'required' => false, 'placeholder' => 'Your 99Acres profile ID'],
                ],
            ],
            [
                'key' => 'my_operator', 'name' => 'My Operator', 'icon' => '📞', 'desc' => 'Cloud telephony leads',
                'requirements' => ['Active MyOperator account (admin access)', 'API token from the MyOperator dashboard'],
                'steps' => [
                    'MyOperator dashboard me admin se login karein.',
                    'Go to Settings → Developers / API → Generate API Token.',
                    'Token (aur Company ID) copy karke niche paste karein.',
                    'MyOperator webhook settings me hamara webhook URL paste karein (Connect ke baad Manage panel me milega).',
                ],
                'fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'required' => true, 'placeholder' => 'MyOperator API token'],
                    ['key' => 'company_id', 'label' => 'Company ID (optional)', 'required' => false, 'placeholder' => 'e.g. 5f8x…'],
                ],
            ],
            [
                'key' => 'zapier', 'name' => 'Zapier', 'icon' => '⚡', 'desc' => 'Connect 5000+ apps',
                'requirements' => ['Zapier account (free plan bhi chalega)', 'Access to the source app you want leads from'],
                'steps' => [
                    'Zapier me ek naya Zap banayein.',
                    'Trigger: apna source app choose karein (e.g. Google Forms, Typeform).',
                    'Action: "Webhooks by Zapier" → POST → hamara webhook URL paste karein.',
                    'Fields map karein — name, phone, email — aur Zap ko ON kar dein.',
                ],
                'fields' => [],
            ],
            [
                'key' => 'acefone', 'name' => 'Acefone', 'icon' => '☎️', 'desc' => 'Virtual phone system',
                'requirements' => ['Acefone account with admin login', 'API access enabled on your plan'],
                'steps' => [
                    'Acefone dashboard me login karein.',
                    'API Connect section me jaakar API token generate/copy karein.',
                    'Token niche paste karein — incoming call leads auto-create honge.',
                ],
                'fields' => [
                    ['key' => 'api_token', 'label' => 'API Token', 'required' => true, 'placeholder' => 'Acefone API token'],
                ],
            ],
            [
                'key' => 'pabbly', 'name' => 'Pabbly', 'icon' => '🔗', 'desc' => 'Workflow automation',
                'requirements' => ['Pabbly Connect account', 'Access to the source app you want leads from'],
                'steps' => [
                    'Pabbly Connect me naya workflow banayein.',
                    'Trigger me apna source app choose karein.',
                    'Action: Webhook / "API by Pabbly" → POST → hamara webhook URL paste karein.',
                    'name, phone, email fields map karke workflow enable karein.',
                ],
                'fields' => [],
            ],
            [
                'key' => 'justdial', 'name' => 'JustDial', 'icon' => '📞', 'desc' => 'Local business leads',
                'requirements' => ['Active JustDial paid listing', '"Lead Push API" activated by JustDial support / account manager'],
                'steps' => [
                    'JustDial support ya apne account manager se "Lead Push API" activate karwayein.',
                    'Unko hamara webhook URL share karein (Connect ke baad Manage panel me milega).',
                    'JustDial se mila API key niche paste karein.',
                ],
                'fields' => [
                    ['key' => 'api_key', 'label' => 'JustDial API Key', 'required' => true, 'placeholder' => 'Key shared by JustDial'],
                    ['key' => 'listing_phone', 'label' => 'Listing Phone Number (optional)', 'required' => false, 'placeholder' => '10-digit listing number'],
                ],
            ],
            [
                'key' => 'tradeindia', 'name' => 'TradeIndia', 'icon' => '🏢', 'desc' => 'B2B trade inquiries',
                'requirements' => ['TradeIndia seller account', 'UserID, Profile ID aur Key (seller dashboard me milte hain)'],
                'steps' => [
                    'TradeIndia seller dashboard me login karein.',
                    'Go to "My Inquiry API" / Settings — UserID, Profile ID aur Key copy karein.',
                    'Teeno values niche paste karke Connect dabayein.',
                ],
                'fields' => [
                    ['key' => 'userid', 'label' => 'UserID', 'required' => true, 'placeholder' => 'TradeIndia UserID'],
                    ['key' => 'profile_id', 'label' => 'Profile ID', 'required' => true, 'placeholder' => 'TradeIndia Profile ID'],
                    ['key' => 'api_key', 'label' => 'Key', 'required' => true, 'placeholder' => 'Inquiry API key'],
                ],
            ],
            [
                'key' => 'exporters_india', 'name' => 'Exporters India', 'icon' => '🌏', 'desc' => 'Export/import leads',
                'requirements' => ['ExportersIndia paid membership', 'Inquiry API key from member dashboard (support se bhi milta hai)'],
                'steps' => [
                    'ExportersIndia member area me login karein.',
                    'Inquiry API section se API key generate karein.',
                    'Key niche paste karke Connect dabayein.',
                ],
                'fields' => [
                    ['key' => 'api_key', 'label' => 'API Key', 'required' => true, 'placeholder' => 'ExportersIndia API key'],
                ],
            ],
            [
                'key' => 'housing', 'name' => 'Housing', 'icon' => '🏠', 'desc' => 'Real estate inquiries',
                'requirements' => ['Housing.com broker/builder account', 'CRM lead API enabled by your account manager'],
                'steps' => [
                    'Housing.com business account me login karein.',
                    'Account manager se "CRM lead API" enable karwayein — Profile ID + API key milega.',
                    'Dono values niche paste karke Connect dabayein.',
                ],
                'fields' => [
                    ['key' => 'profile_id', 'label' => 'Profile ID', 'required' => true, 'placeholder' => 'Housing profile ID'],
                    ['key' => 'api_key', 'label' => 'API Key', 'required' => true, 'placeholder' => 'Housing API key'],
                ],
            ],
            [
                'key' => 'facebook_lead_ads', 'name' => 'Facebook Lead Ads', 'icon' => '📘', 'desc' => 'Meta lead forms',
                'requirements' => ['Facebook Page ka admin access', 'Live Lead Ads form (Instant Form)', 'Meta Business / Developer account'],
                'steps' => [
                    'Meta Business Suite me apna Page select karein.',
                    'Page ID copy karein (Page → About → Page Transparency section).',
                    'Lead Form ID: Ads Manager → Instant Forms se copy karein.',
                    'Access Token: Meta for Developers app se generate karein — "leads_retrieval" permission ke saath.',
                    'Teeno values niche paste karke Connect dabayein.',
                ],
                'fields' => [
                    ['key' => 'page_id', 'label' => 'Page ID', 'required' => true, 'placeholder' => 'e.g. 104235678901234'],
                    ['key' => 'form_id', 'label' => 'Lead Form ID (optional — blank = all forms)', 'required' => false, 'placeholder' => 'Instant Form ID'],
                    ['key' => 'access_token', 'label' => 'Access Token', 'required' => true, 'placeholder' => 'EAAG…'],
                ],
            ],
            [
                'key' => 'indiamart', 'name' => 'IndiaMART', 'icon' => '🏭', 'desc' => 'B2B inquiry sync',
                'requirements' => ['IndiaMART paid seller account', 'CRM (Push) API key — paid sellers ke liye free hai'],
                'steps' => [
                    'IndiaMART seller panel me login karein.',
                    'Settings → Account Settings → "Generate CRM API Key" (Lead Manager Push API).',
                    'Registered mobile number + API key niche paste karke Connect dabayein.',
                ],
                'fields' => [
                    ['key' => 'mobile', 'label' => 'Registered Mobile Number', 'required' => true, 'placeholder' => '10-digit seller mobile'],
                    ['key' => 'api_key', 'label' => 'CRM API Key', 'required' => true, 'placeholder' => 'mRcc…'],
                ],
            ],
            [
                'key' => 'google_sheets', 'name' => 'Google Sheets', 'icon' => '📊', 'desc' => 'Auto-sync from spreadsheet',
                'requirements' => ['Google account', 'Sheet with columns: Name, Phone, Email', 'Link sharing: "Anyone with the link" → Viewer'],
                'steps' => [
                    'Apni Google Sheet kholein jisme leads aate hain.',
                    'Share → "Anyone with the link" → Viewer set karein.',
                    'Sheet ka URL copy karke niche paste karein.',
                    'Connect ke baad naye rows automatically leads ban jayenge.',
                ],
                'fields' => [
                    ['key' => 'sheet_url', 'label' => 'Google Sheet URL', 'required' => true, 'placeholder' => 'https://docs.google.com/spreadsheets/d/…'],
                    ['key' => 'tab_name', 'label' => 'Sheet Tab Name (optional)', 'required' => false, 'placeholder' => 'e.g. Leads'],
                ],
            ],
            [
                'key' => 'website_form', 'name' => 'Website Form', 'icon' => '🌐', 'desc' => 'API webhook active',
                'requirements' => ['Apni website ke code ka access (ya developer ki help)', 'Form with name / phone / email fields'],
                'steps' => [
                    'Apne website form ke submit action se hamare webhook URL par POST karein (fields: name, phone, email, message).',
                    'Ya webhook URL apne developer ko share karein — 5 minute ka kaam hai.',
                    'Ek test submit karke check karein — lead turant CRM me dikhega.',
                ],
                'fields' => [
                    ['key' => 'website_url', 'label' => 'Website URL', 'required' => true, 'placeholder' => 'https://www.yoursite.com'],
                ],
            ],
            [
                'key' => 'whatsapp_business', 'name' => 'WhatsApp Business', 'icon' => '💬', 'desc' => 'Inbound chat to lead',
                'requirements' => ['WhatsApp Business Cloud API account', 'Meta developer app with WhatsApp product added', 'Phone Number ID + permanent access token'],
                'steps' => [
                    'Meta for Developers me apna WhatsApp app kholein.',
                    'WhatsApp → API Setup se Phone Number ID copy karein.',
                    'System User se permanent Access Token generate karein.',
                    'Meta app ke webhook section me hamara webhook URL set karein, phir dono values niche paste karein.',
                ],
                'fields' => [
                    ['key' => 'phone_number_id', 'label' => 'Phone Number ID', 'required' => true, 'placeholder' => 'e.g. 1065…'],
                    ['key' => 'access_token', 'label' => 'Access Token', 'required' => true, 'placeholder' => 'EAAG…'],
                ],
            ],
            [
                'key' => 'csv_import', 'name' => 'CSV Import', 'icon' => '📤', 'desc' => 'Manual bulk upload', 'route' => 'leads.bulk-upload',
                'requirements' => ['CSV/Excel file with at least Name + Phone columns'],
                'steps' => [
                    'Apni lead file CSV format me ready karein.',
                    '"Open" par click karke Bulk Upload page kholein.',
                    'File upload karein, columns map karein — done!',
                ],
                'fields' => [],
            ],
            [
                'key' => 'api_webhook', 'name' => 'API Webhook', 'icon' => '🔗', 'desc' => 'POST /api/leads/capture', 'route' => 'integrations.index',
                'requirements' => ['Developer access to the system that will POST leads'],
                'steps' => [
                    'Niche diya webhook URL copy karein.',
                    'Apne system se POST request bhejein — fields: name, phone, email, source.',
                    'Full API docs ke liye Integrations page kholein ("Open" button).',
                ],
                'fields' => [],
            ],
            [
                'key' => 'manual_entry', 'name' => 'Manual Entry', 'icon' => '✏️', 'desc' => 'Add lead from CRM', 'route' => 'leads.create',
                'requirements' => ['CRM user with "create lead" permission'],
                'steps' => [
                    '"Open" par click karein — Add Lead form khulega.',
                    'Lead details bharein aur save karein — bas itna hi.',
                ],
                'fields' => [],
            ],
        ];
    }

    protected function loadSources(): void
    {
        $tenant = auth()->user()->tenant;
        $settings = $tenant->settings ?? [];
        $state = $settings['lead_sources'] ?? [];
        $token = $this->webhookToken();

        // Legacy support: names in connected_sources count as connected unless
        // an explicit entry exists under lead_sources. First-ever visit keeps
        // the old default set so existing tenants see no regression.
        $legacy = $settings['connected_sources']
            ?? (isset($settings['lead_sources']) ? [] : [
                'Google Sheets', 'Facebook Lead Ads', 'Website Form', 'WhatsApp Business', 'CSV Import', 'API Webhook', 'Manual Entry',
            ]);

        $this->sources = array_map(function ($s) use ($state, $legacy, $token) {
            $entry = $state[$s['key']] ?? null;

            if ($entry !== null) {
                $s['status'] = $entry['status'] ?? 'available';
                $s['config'] = $entry['config'] ?? [];
                $s['sync_enabled'] = (bool) ($entry['sync_enabled'] ?? true);
                $s['connected_at'] = $entry['connected_at'] ?? null;
                $s['last_synced_at'] = $entry['last_synced_at'] ?? null;
            } elseif (in_array($s['name'], $legacy)) {
                $s['status'] = 'connected';
                $s['config'] = [];
                $s['sync_enabled'] = true;
                $s['connected_at'] = null;
                $s['last_synced_at'] = null;
            } else {
                $s['status'] = 'available';
                $s['config'] = [];
                $s['sync_enabled'] = false;
                $s['connected_at'] = null;
                $s['last_synced_at'] = null;
            }

            $s['webhook_url'] = url('/api/leads/ingest/'.$s['key']).'?token='.$token;

            return $s;
        }, $this->catalog());
    }

    protected function webhookToken(): string
    {
        $tenant = auth()->user()->tenant;
        $settings = $tenant->settings ?? [];

        if (empty($settings['lead_sources_token'])) {
            $settings['lead_sources_token'] = Str::random(32);
            $tenant->update(['settings' => $settings]);
        }

        return $settings['lead_sources_token'];
    }

    public function findSource(?string $key): ?array
    {
        if ($key === null) {
            return null;
        }

        foreach ($this->sources as $source) {
            if ($source['key'] === $key) {
                return $source;
            }
        }

        return null;
    }

    protected function persistState(string $key, array $changes): void
    {
        $tenant = auth()->user()->tenant;
        $settings = $tenant->settings ?? [];
        $state = $settings['lead_sources'] ?? [];

        $state[$key] = array_merge($state[$key] ?? [], $changes);
        $settings['lead_sources'] = $state;

        // Keep the legacy connected_sources name list in sync. When the key
        // was never persisted, seed it from currently connected names so the
        // old default set is not lost on first write.
        $source = $this->findSource($key);
        if ($source) {
            $legacy = $settings['connected_sources']
                ?? array_values(array_map(
                    fn ($s) => $s['name'],
                    array_filter($this->sources, fn ($s) => ($s['status'] ?? '') === 'connected')
                ));
            $isConnecting = ($changes['status'] ?? null) === 'connected';
            $isDisconnecting = ($changes['status'] ?? null) === 'disconnected';

            if ($isConnecting && ! in_array($source['name'], $legacy)) {
                $legacy[] = $source['name'];
            } elseif ($isDisconnecting) {
                $legacy = array_values(array_diff($legacy, [$source['name']]));
            }

            $settings['connected_sources'] = $legacy;
        }

        $tenant->update(['settings' => $settings]);
    }

    // ── Connect wizard ──────────────────────────────────────────────

    public function openWizard(string $key): void
    {
        $source = $this->findSource($key);
        if (! $source) {
            return;
        }

        $this->manageSource = null;
        $this->wizardSource = $key;
        $this->wizardStep = 1;
        $this->configError = '';
        $this->fillConfigForm($source);
    }

    public function wizardNext(): void
    {
        $this->wizardStep = 2;
    }

    public function wizardBack(): void
    {
        $this->wizardStep = 1;
        $this->configError = '';
    }

    public function wizardConnect(): void
    {
        $source = $this->findSource($this->wizardSource);
        if (! $source) {
            return;
        }

        if (! $this->validateConfig($source)) {
            return;
        }

        $this->persistState($source['key'], [
            'status' => 'connected',
            'config' => array_map(fn ($v) => trim((string) $v), $this->configForm),
            'sync_enabled' => true,
            'connected_at' => now()->toDateTimeString(),
        ]);

        $this->loadSources();
        $this->wizardStep = 3;
        $this->dispatch('notify', message: $source['name'].' connected successfully! 🎉', type: 'success');
    }

    public function closeWizard(): void
    {
        $this->wizardSource = null;
        $this->wizardStep = 1;
        $this->configError = '';
    }

    public function manageFromWizard(): void
    {
        $key = $this->wizardSource;
        $this->closeWizard();
        if ($key) {
            $this->openManage($key);
        }
    }

    // ── Manage panel ────────────────────────────────────────────────

    public function openManage(string $key): void
    {
        $source = $this->findSource($key);
        if (! $source || $source['status'] !== 'connected') {
            return;
        }

        $this->wizardSource = null;
        $this->manageSource = $key;
        $this->configError = '';
        $this->lastTestLeadId = null;
        $this->fillConfigForm($source);
    }

    public function closeManage(): void
    {
        $this->manageSource = null;
        $this->configError = '';
        $this->lastTestLeadId = null;
    }

    public function saveConfig(): void
    {
        $source = $this->findSource($this->manageSource);
        if (! $source) {
            return;
        }

        if (! $this->validateConfig($source)) {
            return;
        }

        $this->persistState($source['key'], [
            'config' => array_map(fn ($v) => trim((string) $v), $this->configForm),
        ]);

        $this->loadSources();
        $this->dispatch('notify', message: $source['name'].' config saved!', type: 'success');
    }

    public function toggleSync(string $key): void
    {
        $source = $this->findSource($key);
        if (! $source || $source['status'] !== 'connected') {
            return;
        }

        $enabled = ! $source['sync_enabled'];
        $this->persistState($key, ['sync_enabled' => $enabled, 'status' => 'connected']);
        $this->loadSources();

        $this->dispatch('notify', message: $source['name'].' sync '.($enabled ? 'ON — naye leads aayenge.' : 'paused — sync ruk gaya.'), type: 'success');
    }

    public function disconnect(string $key): void
    {
        $source = $this->findSource($key);
        if (! $source) {
            return;
        }

        // Config is kept so reconnect is one click; sync stops immediately.
        $this->persistState($key, [
            'status' => 'disconnected',
            'sync_enabled' => false,
        ]);

        if ($this->manageSource === $key) {
            $this->closeManage();
        }

        $this->loadSources();
        $this->dispatch('notify', message: $source['name'].' disconnected — sync band ho gaya.', type: 'success');
    }

    public function sendTestLead(string $key): void
    {
        $source = $this->findSource($key);
        if (! $source || $source['status'] !== 'connected') {
            return;
        }

        $tenant = auth()->user()->tenant;

        $lead = app(LeadIngestService::class)->ingest($tenant, [
            'name' => 'Test Lead — '.$source['name'],
            'phone' => '9'.rand(100000000, 999999999),
            'email' => 'test.'.$source['key'].'.'.now()->timestamp.'@example.com',
            'company' => 'Test — '.$source['name'],
            'notes' => '⚠️ TEST LEAD — Lead Sources page se generate kiya gaya ('.$source['name'].' integration test). Safe to delete.',
        ], $source['key']);

        $this->persistState($key, ['last_synced_at' => now()->toDateTimeString()]);
        $this->loadSources();

        $this->lastTestLeadId = $lead->id;
        $this->dispatch('notify', message: 'Test lead created from '.$source['name'].'!', type: 'success');
    }

    // ── Helpers ─────────────────────────────────────────────────────

    protected function fillConfigForm(array $source): void
    {
        $this->configForm = [];
        foreach ($source['fields'] as $field) {
            $this->configForm[$field['key']] = $source['config'][$field['key']] ?? '';
        }
    }

    protected function validateConfig(array $source): bool
    {
        foreach ($source['fields'] as $field) {
            if (($field['required'] ?? false) && trim((string) ($this->configForm[$field['key']] ?? '')) === '') {
                $this->configError = $field['label'].' zaroori hai — please fill karein.';

                return false;
            }
        }

        $this->configError = '';

        return true;
    }

    public function render()
    {
        return view('livewire.leads.lead-sources', [
            'wizard' => $this->findSource($this->wizardSource),
            'manage' => $this->findSource($this->manageSource),
        ])->layout('layouts.app');
    }
}
