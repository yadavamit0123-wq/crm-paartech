<div>
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold">Payroll / वेतन</h1>
            <p class="text-gray-500 text-sm">Monthly salary processing</p>
        </div>
        <div class="flex gap-2 items-center">
            <select wire:model.live="selectedMonth" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </select>
            <select wire:model.live="selectedYear" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 text-sm">
                @for($y = now()->year; $y >= now()->year - 2; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
                @endfor
            </select>
            <button wire:click="runPayroll" wire:confirm="Process payroll for this month?" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm">▶ Process Payroll</button>
        </div>
    </div>

    @if($showSalaryForm)
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700 mb-6">
        <h3 class="font-semibold mb-4">Edit Salary Structure</h3>
        <div class="grid md:grid-cols-3 gap-4">
            <input type="number" wire:model="basic_salary" placeholder="Basic Salary" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="hra" placeholder="HRA" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="allowances" placeholder="Allowances" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="pf_deduction" placeholder="PF Deduction" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="esi_deduction" placeholder="ESI Deduction" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input type="number" wire:model="tds_deduction" placeholder="TDS Deduction" class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600">
        </div>
        <div class="flex gap-2 mt-4">
            <button wire:click="saveSalary" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm">Save</button>
            <button wire:click="$set('showSalaryForm', false)" class="px-4 py-2 border rounded-lg text-sm">Cancel</button>
        </div>
    </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            <h3 class="font-semibold mb-4">Employee Salary Profiles</h3>
            <div class="space-y-3">
                @foreach($employees as $emp)
                @php $p = $profiles[$emp->id] ?? null; @endphp
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <div class="font-medium">{{ $emp->name }}</div>
                        <div class="text-xs text-gray-500">{{ $emp->role?->name }} • Net: ₹{{ number_format($p?->netSalary() ?? 0, 0) }}</div>
                    </div>
                    <button wire:click="editSalary({{ $emp->id }})" class="text-sm text-indigo-600">Edit</button>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border dark:border-gray-700">
            @if($currentRun)
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-semibold">{{ $currentRun->periodLabel() }} — {{ ucfirst($currentRun->status) }}</h3>
                <div class="flex gap-2">
                    @if($currentRun->status === 'processed')
                    <button wire:click="markPaid({{ $currentRun->id }})" class="text-sm text-green-600">Mark Paid</button>
                    @endif
                    <a href="{{ route('payroll.pdf', $currentRun) }}" class="text-sm text-red-600">PDF ↓</a>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mb-4 text-center">
                <div class="p-2 bg-green-50 dark:bg-green-900/20 rounded"><div class="text-xs">Gross</div><div class="font-bold">₹{{ number_format($currentRun->total_gross, 0) }}</div></div>
                <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded"><div class="text-xs">Deductions</div><div class="font-bold">₹{{ number_format($currentRun->total_deductions, 0) }}</div></div>
                <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded"><div class="text-xs">Net Pay</div><div class="font-bold">₹{{ number_format($currentRun->total_net, 0) }}</div></div>
            </div>
            <table class="w-full text-xs">
                <thead><tr class="border-b dark:border-gray-700">
                    <th class="py-2 text-left">Employee</th><th class="py-2 text-right">Gross</th><th class="py-2 text-right">Deductions</th><th class="py-2 text-right">Net</th>
                </tr></thead>
                <tbody>
                    @foreach($currentRun->entries as $entry)
                    <tr class="border-b dark:border-gray-700">
                        <td class="py-2">{{ $entry->user->name }}</td>
                        <td class="py-2 text-right">₹{{ number_format($entry->gross_salary, 0) }}</td>
                        <td class="py-2 text-right">₹{{ number_format($entry->total_deductions, 0) }}</td>
                        <td class="py-2 text-right font-semibold">₹{{ number_format($entry->net_salary, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-8 text-gray-500">
                <p>No payroll for this month.</p>
                <p class="text-sm mt-2">Click "Process Payroll" to generate.</p>
            </div>
            @endif
        </div>
    </div>
</div>
