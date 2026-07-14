<?php

namespace App\Livewire\GstReports;

use App\Models\GstReturnLog;
use App\Services\GstReportService;
use Livewire\Component;

class Index extends Component
{
    public string $period = '';

    public function mount(): void
    {
        $this->period = now()->format('Y-m');
    }

    public function render()
    {
        $tenantId = auth()->user()->tenant_id;

        $gstr1 = app(GstReportService::class)->buildGstr1Data($tenantId, $this->period);
        $gstr3b = app(GstReportService::class)->buildGstr3bData($tenantId, $this->period);

        $logs = GstReturnLog::where('tenant_id', $tenantId)
            ->orderByDesc('exported_at')
            ->limit(10)
            ->get();

        return view('livewire.gst-reports.index', compact('gstr1', 'gstr3b', 'logs'))
            ->layout('layouts.app');
    }
}
