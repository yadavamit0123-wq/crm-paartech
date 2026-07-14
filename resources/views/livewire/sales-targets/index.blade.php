<div>
    @include('layouts.partials.leads-nav')
    <div class="flex justify-between items-center mb-6">
        <div><h1 class="text-2xl font-bold">Sales Targets</h1><p class="text-gray-500 text-sm">Team goals & performance tracking</p></div>
        <button wire:click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">+ Set Target</button>
    </div>
    <div class="flex gap-2 mb-4">
        <select wire:model.live="month" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">@for($m=1;$m<=12;$m++)<option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>@endfor</select>
        <select wire:model.live="year" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">@for($y=now()->year;$y>=now()->year-2;$y--)<option value="{{ $y }}">{{ $y }}</option>@endfor</select>
    </div>
    @foreach($employees as $emp)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border dark:border-gray-700 mb-4">
        <h3 class="font-semibold mb-3">{{ $emp->name }}</h3>
        <div class="grid md:grid-cols-3 gap-3">
            @foreach($targets->get($emp->id, collect()) as $target)
            <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <div class="text-xs text-gray-500">{{ $metrics[$target->metric_type] ?? $target->metric_type }}</div>
                <div class="flex justify-between items-end mt-1">
                    <span class="text-lg font-bold">{{ number_format($target->achieved_value, 0) }}/{{ number_format($target->target_value, 0) }}</span>
                    <span class="text-xs {{ $target->progressPercent() >= 100 ? 'text-green-600' : ($target->progressPercent() >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $target->progressPercent() }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2"><div class="h-1.5 rounded-full {{ $target->progressPercent() >= 100 ? 'bg-green-500' : 'bg-indigo-500' }}" style="width: {{ min(100, $target->progressPercent()) }}%"></div></div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
    @if($showModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 w-full max-w-md space-y-3">
            <h3 class="font-bold">Set Target</h3>
            <select wire:model="userId" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select>
            <select wire:model="metricType" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">@foreach($metrics as $k=>$v)<option value="{{ $k }}">{{ $v }}</option>@endforeach</select>
            <input wire:model="targetValue" type="number" placeholder="Target value" class="w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <div class="flex gap-2"><button wire:click="save" class="flex-1 py-2 bg-indigo-600 text-white rounded-lg">Save</button><button wire:click="$set('showModal', false)" class="flex-1 py-2 border rounded-lg">Cancel</button></div>
        </div>
    </div>
    @endif
</div>
