<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

class Create extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $alternate_phone = '';
    public string $company = '';
    public string $designation = '';
    public string $source = 'manual';
    public string $campaign = '';
    public string $city = '';
    public string $state = '';
    public string $address = '';
    public ?float $value = null;
    public string $priority = 'medium';
    public string $notes = '';
    public string $website = '';
    public ?int $lead_stage_id = null;
    public ?int $assigned_to = null;

    public function mount(): void
    {
        $this->lead_stage_id = LeadStage::ensureDefault()->id;
        $this->assigned_to = auth()->id();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'lead_stage_id' => [
                'required',
                Rule::exists('lead_stages', 'id')->where('tenant_id', auth()->user()->tenant_id),
            ],
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'in:low,medium,high,urgent',
            'source' => 'required|string|max:50',
        ];
    }

    public function save()
    {
        if (! auth()->user()->hasPermission('leads.create')) {
            $this->addError('name', 'Aapke paas lead create karne ki permission nahi hai.');

            return;
        }

        if (! $this->lead_stage_id) {
            $this->lead_stage_id = LeadStage::ensureDefault()->id;
        }

        $this->validate();

        try {
            $lead = Lead::create([
                ...$this->only([
                    'name', 'email', 'phone', 'alternate_phone', 'company', 'designation',
                    'source', 'campaign', 'city', 'state', 'address', 'value', 'priority',
                    'notes', 'website', 'lead_stage_id', 'assigned_to',
                ]),
                'tenant_id' => auth()->user()->tenant_id,
                'created_by' => auth()->id(),
            ]);

            $lead->logActivity('created', 'Lead created manually', "Source: {$this->source}");

            session()->flash('success', 'Lead created successfully / Lead ban gaya');

            return redirect()->route('leads.show', $lead);
        } catch (Throwable $e) {
            report($e);
            $this->addError('name', 'Lead save nahi ho paya. Server par migrate chalayein ya admin se contact karein.');
        }
    }

    public function render()
    {
        $stages = LeadStage::orderBy('sort_order')->get();
        $employees = User::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->get();

        return view('livewire.leads.create', compact('stages', 'employees'))
            ->layout('layouts.app');
    }
}
