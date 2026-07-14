<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeadStage;

class CustomerService
{
    public function convertFromLead(Lead $lead): Customer
    {
        $customer = Customer::create([
            'tenant_id' => $lead->tenant_id,
            'lead_id' => $lead->id,
            'name' => $lead->name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company' => $lead->company,
            'gstin' => $lead->gstin,
            'billing_address' => $lead->address,
            'city' => $lead->city,
            'state' => $lead->state,
            'pincode' => $lead->pincode ?? null,
            'notes' => $lead->notes,
            'created_by' => auth()->id(),
        ]);

        $wonStage = LeadStage::where('tenant_id', $lead->tenant_id)->where('is_won', true)->first();

        $lead->update([
            'is_customer' => true,
            'converted_at' => now(),
            'lead_stage_id' => $wonStage?->id ?? $lead->lead_stage_id,
        ]);

        $lead->logActivity('converted', 'Lead converted to customer', "Customer ID: {$customer->id}");

        return $customer;
    }
}
