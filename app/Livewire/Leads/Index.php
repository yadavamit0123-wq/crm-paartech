<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\LeadForward;
use App\Models\LeadLabel;
use App\Models\LeadList;
use App\Models\LeadStage;
use App\Models\Product;
use App\Models\User;
use App\Services\LeadQueryService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use WithPagination;

    public string $view = 'list';
    public string $search = '';
    public string $quickFilter = '';
    public string $filterStage = '';
    public string $filterSource = '';
    public string $filterAssignee = '';
    public string $filterLabel = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';
    public string $filterCreatedBy = '';
    public string $filterHasCall = '';
    public string $filterHasTask = '';
    public string $filterServiceType = '';
    public string $filterPriority = '';
    public string $sortBy = 'latest';
    public int $perPage = 50;

    public string $filterCategory = 'basic';
    public bool $showFilterModal = false;
    public bool $showSimpleFilterModal = false;
    public bool $showBulkModal = false;
    public bool $showColumnsModal = false;
    public bool $showLabelModal = false;
    public bool $showLeadListModal = false;
    public bool $showOnboarding = false;
    public string $simpleFilterCondition = 'and';
    public string $newLeadListName = '';
    public ?int $activeLeadListId = null;
    public string $savedFilterName = '';
    public array $savedFilters = [];
    public array $selected = [];
    public bool $selectAll = false;

    public string $bulkAction = '';
    public ?int $bulkStageId = null;
    public ?int $bulkLabelId = null;
    public ?int $bulkAssigneeId = null;

    public string $newLabelName = '';
    public string $newLabelColor = '#6366f1';

    // Row actions: transfer + quotation
    public ?int $actionLeadId = null;
    public bool $showTransferModal = false;
    public ?int $transferTo = null;
    public string $transferNote = '';
    public bool $showQuoteModal = false;
    public array $quoteProducts = [];

    public array $visibleColumns = [
        'client', 'label', 'status', 'actions', 'source', 'created', 'phone', 'assigned', 'service',
    ];

    protected $queryString = ['view', 'search', 'quickFilter', 'filterStage'];

    public function mount(): void
    {
        $saved = session('lead_visible_columns');
        if (is_array($saved) && count($saved)) {
            $this->visibleColumns = $saved;
        }

        $this->savedFilters = session('lead_saved_filters', []);
        $this->showOnboarding = request()->boolean('tour') && ! session('leads_onboarding_dismissed', false);

        $this->ensureDefaultLeadList();
    }

    protected function ensureDefaultLeadList(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('lead_lists')) {
            return;
        }

        $tenantId = auth()->user()->tenant_id;
        if (! LeadList::where('tenant_id', $tenantId)->where('is_default', true)->exists()) {
            LeadList::create([
                'tenant_id' => $tenantId,
                'name' => 'Default leadlist',
                'is_default' => true,
            ]);
        }
    }

    public function dismissOnboarding(): void
    {
        session(['leads_onboarding_dismissed' => true]);
        $this->showOnboarding = false;
    }

    public function selectLeadList(?int $listId): void
    {
        $this->activeLeadListId = $listId;
        $this->resetPage();
    }

    public function createLeadList(): void
    {
        $this->validate(['newLeadListName' => 'required|string|max:80']);
        LeadList::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->newLeadListName,
        ]);
        $this->newLeadListName = '';
        $this->showLeadListModal = false;
        $this->dispatch('notify', message: 'Lead list created');
    }

    public function saveCurrentFilter(): void
    {
        if (! $this->savedFilterName) {
            $this->dispatch('notify', message: 'Enter a filter name', type: 'error');

            return;
        }

        $filters = session('lead_saved_filters', []);
        $filters[] = [
            'name' => $this->savedFilterName,
            'config' => [
                'search' => $this->search,
                'quickFilter' => $this->quickFilter,
                'filterStage' => $this->filterStage,
                'filterSource' => $this->filterSource,
                'filterAssignee' => $this->filterAssignee,
                'filterLabel' => $this->filterLabel,
                'filterDateFrom' => $this->filterDateFrom,
                'filterDateTo' => $this->filterDateTo,
            ],
        ];
        session(['lead_saved_filters' => $filters]);
        $this->savedFilters = $filters;
        $this->savedFilterName = '';
        $this->dispatch('notify', message: 'Filter saved');
    }

    public function applySavedFilter(int $index): void
    {
        $filter = $this->savedFilters[$index] ?? null;
        if (! $filter) {
            return;
        }
        $config = $filter['config'];
        $this->search = $config['search'] ?? '';
        $this->quickFilter = $config['quickFilter'] ?? '';
        $this->filterStage = $config['filterStage'] ?? '';
        $this->filterSource = $config['filterSource'] ?? '';
        $this->filterAssignee = $config['filterAssignee'] ?? '';
        $this->filterLabel = $config['filterLabel'] ?? '';
        $this->filterDateFrom = $config['filterDateFrom'] ?? '';
        $this->filterDateTo = $config['filterDateTo'] ?? '';
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function applyQuickFilter(string $filter): void
    {
        $this->quickFilter = $this->quickFilter === $filter ? '' : $filter;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search', 'quickFilter', 'filterStage', 'filterSource', 'filterAssignee', 'filterLabel',
            'filterDateFrom', 'filterDateTo', 'filterCreatedBy', 'filterHasCall', 'filterHasTask',
            'filterServiceType', 'filterPriority', 'sortBy',
        ]);
        $this->resetPage();
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = $this->getFilteredQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
    }

    public function openBulk(string $action): void
    {
        if (empty($this->selected)) {
            $this->dispatch('notify', message: 'Pehle leads select karein');

            return;
        }
        $this->bulkAction = $action;
        $this->showBulkModal = true;
    }

    public function runBulkAction(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $leads = Lead::whereIn('id', array_map('intval', $this->selected))->get();

        match ($this->bulkAction) {
            'stage' => $this->bulkUpdateStage($leads),
            'label' => $this->bulkUpdateLabel($leads),
            'assign' => $this->bulkAssign($leads),
            'delete' => $this->bulkDelete($leads),
            default => null,
        };

        $this->showBulkModal = false;
        $this->selected = [];
        $this->selectAll = false;
        $this->bulkAction = '';
    }

    protected function bulkUpdateStage($leads): void
    {
        if (! auth()->user()->hasPermission('leads.edit') || ! $this->bulkStageId) {
            return;
        }
        foreach ($leads as $lead) {
            $old = $lead->stage?->name;
            $lead->update(['lead_stage_id' => $this->bulkStageId]);
            $lead->logActivity('stage_change', "Bulk: {$old} → ".LeadStage::find($this->bulkStageId)?->name);
        }
        $this->dispatch('notify', message: count($leads).' leads stage updated');
    }

    protected function bulkUpdateLabel($leads): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            return;
        }
        foreach ($leads as $lead) {
            $lead->update(['lead_label_id' => $this->bulkLabelId ?: null]);
            $label = $this->bulkLabelId ? LeadLabel::find($this->bulkLabelId)?->name : 'None';
            $lead->logActivity('label', "Label updated to {$label}");
        }
        $this->dispatch('notify', message: count($leads).' leads label updated');
    }

    protected function bulkAssign($leads): void
    {
        if (! auth()->user()->hasPermission('leads.edit') || ! $this->bulkAssigneeId) {
            return;
        }
        $user = User::find($this->bulkAssigneeId);
        foreach ($leads as $lead) {
            $lead->update(['assigned_to' => $this->bulkAssigneeId]);
            $lead->logActivity('assigned', "Assigned to {$user?->name}");
        }
        $this->dispatch('notify', message: count($leads).' leads assigned');
    }

    protected function bulkDelete($leads): void
    {
        if (! auth()->user()->hasPermission('leads.delete')) {
            return;
        }
        foreach ($leads as $lead) {
            $lead->logActivity('deleted', 'Bulk deleted');
            $lead->delete();
        }
        $this->dispatch('notify', message: count($leads).' leads deleted');
    }

    public function logQuickCall(int $leadId): void
    {
        $lead = Lead::findOrFail($leadId);
        $lead->update(['last_contacted_at' => now(), 'last_call_at' => now()]);
        $lead->logActivity('call', 'Call initiated from leads list');
    }

    public function logQuickWhatsapp(int $leadId): void
    {
        $lead = Lead::findOrFail($leadId);
        $lead->update(['last_contacted_at' => now()]);
        $lead->logActivity('whatsapp', 'WhatsApp opened from leads list');
    }

    public function deleteLead(int $leadId): void
    {
        if (! auth()->user()->hasPermission('leads.delete')) {
            $this->dispatch('notify', message: 'Delete permission nahi hai', type: 'error');

            return;
        }

        $lead = Lead::findOrFail($leadId);
        $lead->logActivity('deleted', 'Lead deleted from leads list');
        $lead->delete();
        $this->dispatch('notify', message: 'Lead deleted');
    }

    public function openTransfer(int $leadId): void
    {
        if (! auth()->user()->hasPermission('leads.forward')) {
            $this->dispatch('notify', message: 'Forward permission nahi hai', type: 'error');

            return;
        }

        $this->actionLeadId = $leadId;
        $this->transferTo = null;
        $this->transferNote = '';
        $this->showTransferModal = true;
    }

    public function transferLead(): void
    {
        if (! auth()->user()->hasPermission('leads.forward') || ! $this->actionLeadId) {
            return;
        }

        $this->validate([
            'transferTo' => 'required|exists:users,id',
            'transferNote' => 'nullable|string|max:1000',
        ]);

        $lead = Lead::findOrFail($this->actionLeadId);
        $toUser = User::findOrFail($this->transferTo);

        LeadForward::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'from_user_id' => auth()->id(),
            'to_user_id' => $toUser->id,
            'note' => $this->transferNote ?: null,
        ]);

        $lead->update(['assigned_to' => $toUser->id]);
        $lead->logActivity('forward', "Transferred to {$toUser->name}", $this->transferNote ?: null);

        $this->showTransferModal = false;
        $this->actionLeadId = null;
        $this->dispatch('notify', message: "Lead transferred to {$toUser->name}");
    }

    public function openQuote(int $leadId): void
    {
        if (! auth()->user()->hasPermission('documents.create')) {
            $this->dispatch('notify', message: 'Documents create permission nahi hai', type: 'error');

            return;
        }

        $this->actionLeadId = $leadId;
        $this->quoteProducts = [];
        $this->showQuoteModal = true;
    }

    public function createQuotation()
    {
        if (! auth()->user()->hasPermission('documents.create') || ! $this->actionLeadId) {
            return;
        }

        $params = ['lead_id' => $this->actionLeadId, 'type' => 'quotation'];
        $ids = array_filter(array_map('intval', $this->quoteProducts));
        if ($ids) {
            $params['products'] = implode(',', $ids);
        }

        return $this->redirect(route('leads.documents.create', $params));
    }

    public function createLabel(): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            return;
        }
        $this->validate(['newLabelName' => 'required|string|max:50']);
        $slug = Str::slug($this->newLabelName);
        $base = $slug;
        $i = 1;
        while (LeadLabel::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        LeadLabel::create([
            'tenant_id' => auth()->user()->tenant_id,
            'name' => $this->newLabelName,
            'slug' => $slug,
            'color' => $this->newLabelColor,
        ]);
        $this->newLabelName = '';
        $this->showLabelModal = false;
        $this->dispatch('notify', message: 'Label created');
    }

    public function setLeadStage(int $leadId, $stageId): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            $this->dispatch('notify', message: 'Edit permission nahi hai', type: 'error');

            return;
        }
        $stageId = (int) $stageId;
        if (! $stageId || ! LeadStage::whereKey($stageId)->exists()) {
            return;
        }
        $lead = Lead::findOrFail($leadId);
        $old = $lead->stage?->name;
        $lead->update(['lead_stage_id' => $stageId]);
        $lead->logActivity('stage_change', "Status: {$old} → ".LeadStage::find($stageId)?->name);
        $this->dispatch('notify', message: 'Status updated');
    }

    public function setLeadLabel(int $leadId, $labelId): void
    {
        if (! auth()->user()->hasPermission('leads.edit')) {
            return;
        }
        $lead = Lead::findOrFail($leadId);
        $labelId = ($labelId === '' || $labelId === null) ? null : (int) $labelId;
        $lead->update(['lead_label_id' => $labelId]);
        $name = $labelId ? LeadLabel::find($labelId)?->name : 'None';
        $lead->logActivity('label', "Label set to {$name}");
        $this->dispatch('notify', message: 'Label updated');
    }

    public function saveColumns(): void
    {
        session(['lead_visible_columns' => $this->visibleColumns]);
        $this->showColumnsModal = false;
        $this->dispatch('notify', message: 'Columns saved');
    }

    public function exportCsv(): StreamedResponse
    {
        if (! auth()->user()->hasPermission('leads.export')) {
            abort(403);
        }

        $leads = $this->getFilteredQuery()->get();

        return response()->streamDownload(function () use ($leads) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Name', 'Phone', 'Email', 'Company', 'Stage', 'Label', 'Source', 'Service', 'Assigned', 'Created']);
            foreach ($leads as $lead) {
                fputcsv($out, [
                    $lead->id, $lead->name, $lead->phone, $lead->email, $lead->company,
                    $lead->stage?->name, $lead->label?->name, $lead->source, $lead->service_type,
                    $lead->assignee?->name, $lead->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, 'leads-'.date('Y-m-d').'.csv');
    }

    protected function getFilteredQuery()
    {
        $user = auth()->user();
        $service = app(LeadQueryService::class);

        $query = $service->baseQuery(
            $user->tenant_id,
            $user->hasPermission('leads.view_all'),
            $user->id
        );

        $query = $service->applySearch($query, $this->search);
        $query = $service->applyQuickFilter($query, $this->quickFilter, $user->tenant_id);

        if ($this->activeLeadListId) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('leads', 'lead_list_id')) {
                $query->where('lead_list_id', $this->activeLeadListId);
            }
        }

        return $service->applyAdvancedFilters($query, [
            'stage_id' => $this->filterStage,
            'label_id' => $this->filterLabel,
            'source' => $this->filterSource,
            'assigned_to' => $this->filterAssignee,
            'created_by' => $this->filterCreatedBy,
            'service_type' => $this->filterServiceType,
            'has_call' => $this->filterHasCall,
            'has_task' => $this->filterHasTask,
            'priority' => $this->filterPriority,
            'date_from' => $this->filterDateFrom,
            'date_to' => $this->filterDateTo,
            'sort' => $this->sortBy,
        ]);
    }

    public function render()
    {
        $user = auth()->user();
        $service = app(LeadQueryService::class);
        $query = $this->getFilteredQuery();

        $stages = LeadStage::orderBy('sort_order')->get();
        $labels = LeadLabel::orderBy('name')->get();
        $leadLists = LeadList::orderByDesc('is_default')->orderBy('name')->get();
        $employees = User::where('tenant_id', $user->tenant_id)->where('is_active', true)->get();
        $duplicatePhones = $service->getDuplicatePhones($user->tenant_id);

        $stats = [
            'total' => (clone $query)->count(),
        ];

        if ($this->view === 'list') {
            $leads = $query->paginate($this->perPage);
        } else {
            $leads = $query->get()->groupBy('lead_stage_id');
        }

        $quoteModalProducts = ($this->showQuoteModal && Schema::hasTable('products'))
            ? Product::where('is_active', true)->orderBy('name')->get()
            : collect();

        $columnOptions = [
            'client' => 'Client Name',
            'label' => 'Label',
            'status' => 'Status',
            'actions' => 'Actions',
            'source' => 'Source',
            'created' => 'Created',
            'phone' => 'Phone',
            'assigned' => 'Assigned',
            'service' => 'Service Type',
        ];

        return view('livewire.leads.index', compact('stages', 'labels', 'leadLists', 'leads', 'employees', 'stats', 'duplicatePhones', 'columnOptions', 'quoteModalProducts'))
            ->layout('layouts.app');
    }
}
