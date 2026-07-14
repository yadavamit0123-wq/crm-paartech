<?php

namespace App\Livewire\Expenses;

use App\Models\Expense;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterCategory = '';

    public function delete(int $id): void
    {
        if (! auth()->user()->hasPermission('expenses.manage')) {
            return;
        }
        Expense::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Expense deleted');
    }

    public function render()
    {
        $query = Expense::latest('invoice_date');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('vendor_name', 'like', "%{$this->search}%")
                    ->orWhere('invoice_number', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        $expenses = $query->paginate(15);
        $categories = Expense::categories();

        return view('livewire.expenses.index', compact('expenses', 'categories'))
            ->layout('layouts.app');
    }
}
