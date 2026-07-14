<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadStage;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class BulkUpload extends Component
{
    use WithFileUploads;

    public $csvFile;
    public array $preview = [];
    public array $importErrors = [];
    public int $imported = 0;

    public function processUpload(): void
    {
        if (! auth()->user()->hasPermission('leads.bulk_upload')) {
            abort(403);
        }

        $this->validate(['csvFile' => 'required|file|mimes:csv,txt|max:5120']);

        $path = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle);
        $headers = array_map(fn ($h) => strtolower(trim($h)), $headers);

        $this->preview = [];
        $this->importErrors = [];
        $this->imported = 0;

        $defaultStage = LeadStage::ensureDefault();

        $rowNum = 1;
        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($row) < 1 || empty(trim($row[0] ?? ''))) {
                continue;
            }

            $data = array_combine($headers, array_pad($row, count($headers), ''));
            $name = trim($data['name'] ?? '');

            if (empty($name)) {
                $this->importErrors[] = "Row {$rowNum}: Name is required";

                continue;
            }

            try {
                $lead = Lead::create([
                    'tenant_id' => auth()->user()->tenant_id,
                    'lead_stage_id' => $defaultStage->id,
                    'assigned_to' => auth()->id(),
                    'created_by' => auth()->id(),
                    'name' => $name,
                    'email' => trim($data['email'] ?? '') ?: null,
                    'phone' => trim($data['phone'] ?? '') ?: null,
                    'company' => trim($data['company'] ?? '') ?: null,
                    'source' => trim($data['source'] ?? 'bulk_upload') ?: 'bulk_upload',
                    'city' => trim($data['city'] ?? '') ?: null,
                    'state' => trim($data['state'] ?? '') ?: null,
                    'priority' => in_array($data['priority'] ?? '', ['low', 'medium', 'high', 'urgent'])
                        ? $data['priority'] : 'medium',
                ]);

                $lead->logActivity('import', 'Lead imported via bulk upload');
                $this->imported++;
                $this->preview[] = $name;
            } catch (\Exception $e) {
                $this->importErrors[] = "Row {$rowNum}: ".$e->getMessage();
            }
        }

        fclose($handle);
        $this->csvFile = null;
    }

    public function render()
    {
        return view('livewire.leads.bulk-upload')->layout('layouts.app');
    }
}
