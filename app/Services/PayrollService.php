<?php

namespace App\Services;

use App\Models\EmployeeSalaryProfile;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use App\Models\User;

class PayrollService
{
    public function createRun(int $tenantId, int $month, int $year): PayrollRun
    {
        $existing = PayrollRun::where('tenant_id', $tenantId)
            ->where('month', $month)->where('year', $year)->first();

        if ($existing) {
            return $existing;
        }

        return PayrollRun::create([
            'tenant_id' => $tenantId,
            'month' => $month,
            'year' => $year,
            'title' => date('F Y', mktime(0, 0, 0, $month, 1, $year)).' Payroll',
            'status' => 'draft',
        ]);
    }

    public function generateEntries(PayrollRun $run): PayrollRun
    {
        $employees = User::where('tenant_id', $run->tenant_id)
            ->where('is_super_admin', false)
            ->where('is_active', true)
            ->get();

        $run->entries()->delete();

        $totalGross = $totalDeductions = $totalNet = 0;

        foreach ($employees as $employee) {
            $profile = EmployeeSalaryProfile::firstOrCreate(
                ['tenant_id' => $run->tenant_id, 'user_id' => $employee->id],
                [
                    'basic_salary' => 25000,
                    'hra' => 5000,
                    'allowances' => 2000,
                    'pf_deduction' => 1800,
                    'esi_deduction' => 0,
                    'tds_deduction' => 0,
                ]
            );

            $gross = $profile->grossSalary();
            $deductions = $profile->totalDeductions();
            $net = $profile->netSalary();

            PayrollEntry::create([
                'payroll_run_id' => $run->id,
                'user_id' => $employee->id,
                'basic_salary' => $profile->basic_salary,
                'hra' => $profile->hra,
                'allowances' => $profile->allowances,
                'gross_salary' => $gross,
                'pf_deduction' => $profile->pf_deduction,
                'esi_deduction' => $profile->esi_deduction,
                'tds_deduction' => $profile->tds_deduction,
                'other_deductions' => $profile->other_deductions,
                'total_deductions' => $deductions,
                'net_salary' => $net,
            ]);

            $totalGross += $gross;
            $totalDeductions += $deductions;
            $totalNet += $net;
        }

        $run->update([
            'total_gross' => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net' => $totalNet,
            'status' => 'processed',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);

        return $run->fresh()->load('entries.user');
    }

    public function markPaid(PayrollRun $run): PayrollRun
    {
        $run->update(['status' => 'paid']);

        return $run;
    }
}
