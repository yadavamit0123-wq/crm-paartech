<?php

namespace App\Livewire\Integrations;

use Livewire\Component;

class Index extends Component
{
    public string $whatsapp_token = '';
    public string $whatsapp_phone_number_id = '';
    public string $whatsapp_verify_token = '';
    public string $meta_page_token = '';
    public string $meta_app_secret = '';
    public string $meta_verify_token = '';
    public string $google_ads_webhook_secret = '';
    public string $google_review_link = '';
    public string $google_place_id = '';
    public bool $auto_reply_reviews = true;
    public bool $auto_reply_openai = true;

    public function mount(): void
    {
        if (! auth()->user()->hasPermission('integrations.manage')) {
            abort(403);
        }

        $settings = auth()->user()->tenant?->settings ?? [];

        $this->whatsapp_token = $settings['whatsapp_token'] ?? '';
        $this->whatsapp_phone_number_id = $settings['whatsapp_phone_number_id'] ?? '';
        $this->whatsapp_verify_token = $settings['whatsapp_verify_token'] ?? '';
        $this->meta_page_token = $settings['meta_page_token'] ?? '';
        $this->meta_app_secret = $settings['meta_app_secret'] ?? '';
        $this->meta_verify_token = $settings['meta_verify_token'] ?? '';
        $this->google_ads_webhook_secret = $settings['google_ads_webhook_secret'] ?? '';
        $this->google_review_link = $settings['google_review_link'] ?? '';
        $this->google_place_id = $settings['google_place_id'] ?? '';
        $this->auto_reply_reviews = $settings['auto_reply_reviews'] ?? true;
        $this->auto_reply_openai = $settings['auto_reply_openai'] ?? true;
    }

    public function save(): void
    {
        $tenant = auth()->user()->tenant;
        $settings = array_merge($tenant->settings ?? [], [
            'whatsapp_token' => $this->whatsapp_token,
            'whatsapp_phone_number_id' => $this->whatsapp_phone_number_id,
            'whatsapp_verify_token' => $this->whatsapp_verify_token,
            'meta_page_token' => $this->meta_page_token,
            'meta_app_secret' => $this->meta_app_secret,
            'meta_verify_token' => $this->meta_verify_token,
            'google_ads_webhook_secret' => $this->google_ads_webhook_secret,
            'google_review_link' => $this->google_review_link,
            'google_place_id' => $this->google_place_id,
            'auto_reply_reviews' => $this->auto_reply_reviews,
            'auto_reply_openai' => $this->auto_reply_openai,
        ]);

        $tenant->update(['settings' => $settings]);
        $this->dispatch('notify', message: 'Integration settings saved / Settings save ho gayi');
    }

    public function render()
    {
        $tenant = auth()->user()->tenant;
        $subdomain = $tenant->subdomain;
        $baseUrl = rtrim(config('app.url'), '/');

        $webhooks = [
            'website' => "{$baseUrl}/api/leads/capture",
            'whatsapp' => "{$baseUrl}/api/webhooks/whatsapp/{$subdomain}",
            'meta' => "{$baseUrl}/api/webhooks/meta/{$subdomain}",
            'google' => "{$baseUrl}/api/webhooks/google/{$subdomain}",
        ];

        $embedForm = $this->getEmbedCode($subdomain, $baseUrl);

        $logs = \App\Models\WebhookLog::latest()->limit(15)->get();

        return view('livewire.integrations.index', compact('webhooks', 'embedForm', 'logs'))
            ->layout('layouts.app');
    }

    protected function getEmbedCode(string $subdomain, string $baseUrl): string
    {
        return <<<HTML
<!-- SaaS CRM Lead Form - Paste on your website -->
<form id="crm-lead-form" style="max-width:400px;font-family:sans-serif">
  <input name="name" placeholder="Your Name *" required style="width:100%;padding:10px;margin:5px 0;border:1px solid #ddd;border-radius:6px">
  <input name="phone" placeholder="Phone *" required style="width:100%;padding:10px;margin:5px 0;border:1px solid #ddd;border-radius:6px">
  <input name="email" placeholder="Email" style="width:100%;padding:10px;margin:5px 0;border:1px solid #ddd;border-radius:6px">
  <textarea name="message" placeholder="Message" style="width:100%;padding:10px;margin:5px 0;border:1px solid #ddd;border-radius:6px"></textarea>
  <button type="submit" style="width:100%;padding:12px;background:#4f46e5;color:white;border:none;border-radius:6px;cursor:pointer">Submit</button>
  <p id="crm-form-msg" style="display:none;color:green;margin-top:8px"></p>
</form>
<script>
document.getElementById('crm-lead-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const fd = new FormData(e.target);
  const res = await fetch('{$baseUrl}/api/leads/capture', {
    method: 'POST',
    headers: {'Content-Type':'application/json','Accept':'application/json'},
    body: JSON.stringify({
      tenant_key: '{$subdomain}',
      name: fd.get('name'), phone: fd.get('phone'), email: fd.get('email'),
      message: fd.get('message'), source: 'website'
    })
  });
  const msg = document.getElementById('crm-form-msg');
  msg.style.display = 'block';
  msg.textContent = res.ok ? 'Thank you! We will contact you soon.' : 'Error submitting form.';
  if(res.ok) e.target.reset();
});
</script>
HTML;
    }
}
