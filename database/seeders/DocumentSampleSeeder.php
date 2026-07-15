<?php

namespace Database\Seeders;

use App\Models\Document;
use App\Models\Lead;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DocumentService;
use Illuminate\Database\Seeder;

class DocumentSampleSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('subdomain', 'demo')->first();
        if (! $tenant) {
            return;
        }

        $admin = User::where('tenant_id', $tenant->id)->where('email', 'admin@demo.com')->first();
        $lead = Lead::where('tenant_id', $tenant->id)->where('email', 'rajesh@example.com')->first();

        if (Document::where('tenant_id', $tenant->id)->where('document_number', 'QUO-'.date('Y').'-001')->exists()) {
            return;
        }

        auth()->login($admin);

        app(DocumentService::class)->createDocument([
            'type' => 'quotation',
            'document_number' => 'QUO-'.date('Y').'-001',
            'lead_id' => $lead?->id,
            'title' => 'GST Filing & Compliance Package',
            'template_key' => 'classic_purple',
            'theme_color' => '#7c3aed',
            'currency' => 'INR',
            'is_gst_applicable' => true,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'customer_name' => $lead?->name ?? 'Rajesh Kumar',
            'customer_address' => 'Tech Solutions, MG Road, Bangalore',
            'customer_state' => 'Karnataka',
            'customer_phone' => $lead?->phone ?? '9988776655',
            'customer_email' => $lead?->email ?? 'rajesh@example.com',
            'customer_gstin' => '29AABCT1234F1Z5',
            'signature_data' => ['name' => 'Demo Admin', 'title' => 'Director'],
            'contact_details' => ['person' => 'Demo Admin', 'phone' => '9876543210', 'email' => 'admin@demo.com'],
            'additional_info' => 'Payment via NEFT/RTGS. GST extra as applicable.',
            'advanced_options' => [
                'show_description_full_width' => true,
                'hide_place_of_supply' => false,
                'show_tax_summary' => true,
                'summarise_quantity' => false,
                'show_bank_details' => true,
                'show_powered_by_nexpaar' => true,
            ],
            'notes' => 'Thank you for your business!',
        ], [
            [
                'description' => 'GST Filing Service (Monthly)',
                'long_description' => '<p>Complete GSTR-1, GSTR-3B filing with reconciliation.</p>',
                'group_name' => 'Compliance Services',
                'hsn_sac' => '998314',
                'quantity' => 12,
                'unit' => 'Months',
                'rate' => 2499,
                'discount_type' => 'percent',
                'discount_percent' => 10,
                'gst_rate' => 18,
            ],
            [
                'description' => 'Annual Compliance Review',
                'long_description' => '<p>Year-end GST audit and compliance check.</p>',
                'group_name' => 'Compliance Services',
                'hsn_sac' => '998314',
                'quantity' => 1,
                'unit' => 'Year',
                'rate' => 5000,
                'discount_type' => 'fixed',
                'discount_amount' => 0,
                'gst_rate' => 18,
            ],
        ], $tenant);
    }
}
