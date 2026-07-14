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
    public $importFile;
    public array $importErrors = [];
    public int $importedCount = 0;

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

    public function openImport(): void
    {
        $this->importFile = null;
        $this->importErrors = [];
        $this->importedCount = 0;
        $this->showImportModal = true;
    }

    public function import(): void
    {
        $this->validate(['importFile' => 'required|file|mimes:csv,txt|max:5120']);

        $this->importErrors = [];
        $this->importedCount = 0;

        $handle = fopen($this->importFile->getRealPath(), 'r');
        $headers = fgetcsv($handle);

        if ($headers === false) {
            fclose($handle);
            $this->importErrors[] = 'The file is empty';

            return;
        }

        $headers = array_map(fn ($h) => strtolower(trim($h)), $headers);

        if (! in_array('name', $headers)) {
            fclose($handle);
            $this->importErrors[] = 'Missing required column: name';

            return;
        }

        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < 1 || empty(trim($row[0] ?? ''))) {
                continue;
            }

            $data = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), ''));
            $name = trim($data['name'] ?? '');

            if (empty($name)) {
                $this->importErrors[] = "Row {$rowNum}: Name is required";

                continue;
            }

            $price = trim($data['price'] ?? '');
            if ($price === '' || ! is_numeric($price)) {
                $this->importErrors[] = "Row {$rowNum}: Valid price is required";

                continue;
            }

            $gstRate = trim($data['gst_rate'] ?? '');

            try {
                Product::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'name' => $name,
                    'sku' => trim($data['sku'] ?? '') ?: null,
                    'price' => $price,
                    'tax_rate' => is_numeric($gstRate) ? $gstRate : 18,
                    'hsn_sac' => trim($data['hsn_sac'] ?? '') ?: null,
                    'category' => trim($data['category'] ?? '') ?: null,
                    'unit' => trim($data['unit'] ?? '') ?: 'Nos',
                    'description' => trim($data['description'] ?? '') ?: null,
                    'is_active' => true,
                ]);
                $this->importedCount++;
            } catch (\Exception $e) {
                $this->importErrors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);
        $this->importFile = null;
        $this->dispatch('notify', message: $this->importedCount.' products imported');
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
