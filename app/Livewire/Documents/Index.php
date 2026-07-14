<?php

namespace App\Livewire\Documents;

use App\Models\Customer;
use App\Models\Document;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterType = '';
    public string $filterStatus = '';
    public string $search = '';

    public function render()
    {
        if (! Schema::hasTable('documents')) {
            $documents = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

            return view('livewire.documents.index', compact('documents'))
                ->layout('layouts.app');
        }

        $query = Document::with(['customer', 'creator', 'referenceDocument'])->latest();

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('document_number', 'like', "%{$this->search}%")
                    ->orWhere('customer_name', 'like', "%{$this->search}%");
            });
        }

        $documents = $query->paginate(15);

        return view('livewire.documents.index', compact('documents'))
            ->layout('layouts.app');
    }
}
