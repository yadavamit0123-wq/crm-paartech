<?php

namespace App\Livewire\CallLogs;

use App\Models\CallLog;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterDirection = '';

    public function render()
    {
        $query = CallLog::with(['lead', 'user'])->latest('called_at');

        if ($this->filterDirection) {
            $query->where('direction', $this->filterDirection);
        }

        $logs = $query->paginate(25);

        $stats = [
            'total' => CallLog::count(),
            'incoming' => CallLog::where('direction', 'incoming')->count(),
            'outgoing' => CallLog::where('direction', 'outgoing')->count(),
            'missed' => CallLog::where('direction', 'missed')->count(),
            'rejected' => CallLog::where('direction', 'rejected')->count(),
        ];

        return view('livewire.call-logs.index', compact('logs', 'stats'))
            ->layout('layouts.app');
    }
}
