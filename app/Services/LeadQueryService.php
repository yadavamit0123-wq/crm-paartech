<?php

namespace App\Services;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LeadQueryService
{
    public function baseQuery(int $tenantId, bool $viewAll, int $userId): Builder
    {
        $query = Lead::with(['stage', 'label', 'assignee', 'openTasks']);

        if (! $viewAll) {
            $query->where('assigned_to', $userId);
        }

        return $query;
    }

    public function applySearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('company', 'like', "%{$search}%")
                ->orWhere('service_type', 'like', "%{$search}%");
        });
    }

    public function applyQuickFilter(Builder $query, ?string $quickFilter, ?int $tenantId = null): Builder
    {
        return match ($quickFilter) {
            'untouched' => $query->whereNull('last_contacted_at'),
            'no_call' => $query->whereNull('last_call_at')
                ->whereDoesntHave('activities', fn ($q) => $q->where('type', 'call')),
            'unassigned' => $query->whereNull('assigned_to'),
            'no_task' => $query->whereDoesntHave('tasks', fn ($q) => $q->where('status', 'pending')),
            'stale' => $query->where('created_at', '<', now()->subDays(30))
                ->where(function ($q) {
                    $q->whereNull('last_contacted_at')
                        ->orWhere('last_contacted_at', '<', now()->subDays(30));
                }),
            'duplicates' => $query->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereIn('phone', $this->duplicatePhoneSubquery($tenantId)),
            default => $query,
        };
    }

    public function applyAdvancedFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['stage_id'])) {
            $query->where('lead_stage_id', $filters['stage_id']);
        }
        if (! empty($filters['label_id'])) {
            $query->where('lead_label_id', $filters['label_id']);
        }
        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }
        if (! empty($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }
        if (! empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }
        if (! empty($filters['service_type'])) {
            $query->where('service_type', 'like', '%'.$filters['service_type'].'%');
        }
        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        if (! empty($filters['has_call'])) {
            if ($filters['has_call'] === 'yes') {
                $query->whereNotNull('last_call_at');
            } else {
                $query->whereNull('last_call_at');
            }
        }
        if (! empty($filters['has_task'])) {
            if ($filters['has_task'] === 'yes') {
                $query->whereHas('tasks', fn ($q) => $q->where('status', 'pending'));
            } else {
                $query->whereDoesntHave('tasks', fn ($q) => $q->where('status', 'pending'));
            }
        }
        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['sort'])) {
            match ($filters['sort']) {
                'oldest' => $query->oldest(),
                'name' => $query->orderBy('name'),
                'value' => $query->orderByDesc('value'),
                default => $query->latest(),
            };
        } else {
            $query->latest();
        }

        return $query;
    }

    public function duplicatePhoneSubquery(?int $tenantId = null)
    {
        return function ($sub) use ($tenantId) {
            $sub->select('phone')
                ->from('leads')
                ->whereNotNull('phone')
                ->where('phone', '!=', '')
                ->whereNull('deleted_at')
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->groupBy('phone')
                ->havingRaw('COUNT(*) > 1');
        };
    }

    public function getDuplicatePhones(int $tenantId): Collection
    {
        return DB::table('leads')
            ->where('tenant_id', $tenantId)
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->whereNull('deleted_at')
            ->groupBy('phone')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('phone');
    }

    public function isDuplicatePhone(?string $phone, Collection $duplicatePhones): bool
    {
        return $phone && $duplicatePhones->contains($phone);
    }
}
