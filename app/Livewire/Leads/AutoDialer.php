<?php

namespace App\Livewire\Leads;

use App\Models\CallLog;
use App\Models\Lead;
use Livewire\Component;
use Livewire\WithPagination;

class AutoDialer extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $activeLeadId = null;

    public function startCall(int $leadId): void
    {
        $lead = Lead::findOrFail($leadId);
        $this->activeLeadId = $leadId;

        CallLog::create([
            'tenant_id' => auth()->user()->tenant_id,
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'phone' => $lead->phone,
            'direction' => 'outgoing',
            'duration_seconds' => 0,
            'called_at' => now(),
        ]);

        $lead->update(['last_contacted_at' => now(), 'last_call_at' => now()]);
        $lead->logActivity('call', 'Auto-dialer call initiated');

        $this->dispatch('notify', message: 'Call logged for '.$lead->name);
    }

    public function render()
    {
        $user = auth()->user();
        $query = Lead::whereNotNull('phone')->where('phone', '!=', '');

        if (! $user->hasPermission('leads.view_all')) {
            $query->where('assigned_to', $user->id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        $leads = $query->with('stage')->latest()->paginate(20);

        return view('livewire.leads.auto-dialer', compact('leads'))
            ->layout('layouts.app');
    }
}
