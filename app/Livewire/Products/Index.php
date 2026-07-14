<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public string $search = '';
    public string $filterCategory = '';
    public bool $showModal = false;
    public bool $showImportModal = false;
    public ?int $editId = null;
    public string $name = '';
    public string $sku = '';
    public string $description = '';
    public string $category = '';
    public string $unit = 'Nos';
    public string $price = '';
    public string $taxRate = '18';
    public string $hsnSac = '';
    public $image;
    public bool $isActive = true;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $p = Product::findOrFail($id);
        $this->editId = $p->id;
        $this->name = $p->name;
        $this->sku = $p->sku ?? '';
        $this->description = $p->description ?? '';
        $this->category = $p->category ?? '';
        $this->unit = $p->unit ?? 'Nos';
        $this->price = (string) $p->price;
        $this->taxRate = (string) $p->tax_rate;
        $this->hsnSac = $p->hsn_sac ?? '';
        $this->isActive = $p->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:200',
            'price' => 'required|numeric|min:0',
        ]);

        $data = [
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->name,
            'sku' => $this->sku ?: null,
            'description' => $this->description ?: null,
            'category' => $this->category ?: null,
            'unit' => $this->unit ?: 'Nos',
            'price' => $this->price,
            'tax_rate' => $this->taxRate,
            'hsn_sac' => $this->hsnSac ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->image) {
            $data['image_path'] = $this->image->store('products/'.auth()->user()->tenant_id, 'public');
        }

        if ($this->editId) {
            Product::findOrFail($this->editId)->update($data);
        } else {
            Product::create($data);
        }

        $this->showModal = false;
        $this->resetForm();
        $this->dispatch('notify', message: 'Product saved');
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        $this->dispatch('notify', message: 'Product deleted');
    }

    protected function resetForm(): void
    {
        $this->editId = null;
        $this->name = '';
        $this->sku = '';
        $this->description = '';
        $this->category = '';
        $this->unit = 'Nos';
        $this->price = '';
        $this->taxRate = '18';
        $this->hsnSac = '';
        $this->image = null;
        $this->isActive = true;
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")->orWhere('sku', 'like', "%{$this->search}%"))
            ->when($this->filterCategory, fn ($q) => $q->where('category', $this->filterCategory))
            ->latest()
            ->get();

        $categories = Product::whereNotNull('category')->distinct()->pluck('category');

        return view('livewire.products.index', compact('products', 'categories'))
            ->layout('layouts.app');
    }
}
