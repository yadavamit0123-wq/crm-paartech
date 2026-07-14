<?php

namespace App\Livewire\Payroll;

use App\Models\EmployeeSalaryProfile;
use App\Models\PayrollRun;
use App\Models\User;
use App\Services\PayrollService;
use Livewire\Component;

class Index extends Component
{
    public int $selectedMonth;
    public int $selectedYear;
    public bool $showSalaryForm = false;
    public ?int $editUserId = null;
    public float $basic_salary = 0;
    public float $hra = 0;
    public float $allowances = 0;
    public float $pf_deduction = 0;
    public float $esi_deduction = 0;
    public float $tds_deduction = 0;

    public function mount(): void
    {
        $this->selectedMonth = (int) now()->format('m');
        $this->selectedYear = (int) now()->format('Y');
    }

    public function editSalary(int $userId): void
    {
        $profile = EmployeeSalaryProfile::where('user_id', $userId)->first();
        $this->editUserId = $userId;
        $this->basic_salary = (float) ($profile->basic_salary ?? 25000);
        $this->hra = (float) ($profile->hra ?? 5000);
        $this->allowances = (float) ($profile->allowances ?? 2000);
        $this->pf_deduction = (float) ($profile->pf_deduction ?? 1800);
        $this->esi_deduction = (float) ($profile->esi_deduction ?? 0);
        $this->tds_deduction = (float) ($profile->tds_deduction ?? 0);
        $this->showSalaryForm = true;
    }

    public function saveSalary(): void
    {
        EmployeeSalaryProfile::updateOrCreate(
            ['tenant_id' => auth()->user()->tenant_id, 'user_id' => $this->editUserId],
            [
                'basic_salary' => $this->basic_salary,
                'hra' => $this->hra,
                'allowances' => $this->allowances,
                'pf_deduction' => $this->pf_deduction,
                'esi_deduction' => $this->esi_deduction,
                'tds_deduction' => $this->tds_deduction,
            ]
        );

        $this->showSalaryForm = false;
        $this->dispatch('notify', message: 'Salary profile updated');
    }

    public function runPayroll(PayrollService $payrollService): void
    {
        if (! auth()->user()->hasPermission('payroll.manage')) {
            abort(403);
        }

        $run = $payrollService->createRun(auth()->user()->tenant_id, $this->selectedMonth, $this->selectedYear);
        $payrollService->generateEntries($run);
        $this->dispatch('notify', message: 'Payroll processed for '.$run->periodLabel());
    }

    public function markPaid(int $runId, PayrollService $payrollService): void
    {
        $run = PayrollRun::findOrFail($runId);
        $payrollService->markPaid($run);
        $this->dispatch('notify', message: 'Payroll marked as paid');
    }

    public function render()
    {
        $employees = User::with('role')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('is_super_admin', false)
            ->where('is_active', true)
            ->get();

        $profiles = EmployeeSalaryProfile::where('tenant_id', auth()->user()->tenant_id)
            ->get()->keyBy('user_id');

        $currentRun = PayrollRun::with('entries.user')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->where('month', $this->selectedMonth)
            ->where('year', $this->selectedYear)
            ->first();

        $pastRuns = PayrollRun::where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('year')->orderByDesc('month')
            ->limit(6)->get();

        return view('livewire.payroll.index', compact('employees', 'profiles', 'currentRun', 'pastRuns'))
            ->layout('layouts.app');
    }
}
