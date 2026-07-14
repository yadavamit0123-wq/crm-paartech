<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryProfile extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'basic_salary', 'hra', 'allowances',
        'pf_deduction', 'esi_deduction', 'tds_deduction', 'other_deductions',
        'pan', 'bank_account', 'bank_ifsc',
    ];

    protected function casts(): array
    {
        return [
            'basic_salary' => 'decimal:2',
            'hra' => 'decimal:2',
            'allowances' => 'decimal:2',
            'pf_deduction' => 'decimal:2',
            'esi_deduction' => 'decimal:2',
            'tds_deduction' => 'decimal:2',
            'other_deductions' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grossSalary(): float
    {
        return (float) ($this->basic_salary + $this->hra + $this->allowances);
    }

    public function totalDeductions(): float
    {
        return (float) ($this->pf_deduction + $this->esi_deduction + $this->tds_deduction + $this->other_deductions);
    }

    public function netSalary(): float
    {
        return $this->grossSalary() - $this->totalDeductions();
    }
}
