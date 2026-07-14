<?php

namespace Database\Seeders;

use App\Models\AdCampaign;
use App\Models\Automation;
use App\Models\Broadcast;
use App\Models\CallLog;
use App\Models\CrmTask;
use App\Models\Expense;
use App\Models\Lead;
use App\Models\LeadForm;
use App\Models\LeadLabel;
use App\Models\LeadList;
use App\Models\LeadStage;
use App\Models\MessageTemplate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\SalesTarget;
use App\Models\SeoAudit;
use App\Models\SocialPost;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use App\Models\WhatsappBot;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedSubscriptionPlans();
        $this->seedSuperAdmin();
        $this->seedDemoTenant();
    }

    protected function seedPermissions(): void
    {
        $permissions = [
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'group' => 'dashboard'],
            ['name' => 'View Own Leads', 'slug' => 'leads.view_own', 'group' => 'leads'],
            ['name' => 'View All Leads', 'slug' => 'leads.view_all', 'group' => 'leads'],
            ['name' => 'Create Leads', 'slug' => 'leads.create', 'group' => 'leads'],
            ['name' => 'Edit Leads', 'slug' => 'leads.edit', 'group' => 'leads'],
            ['name' => 'Delete Leads', 'slug' => 'leads.delete', 'group' => 'leads'],
            ['name' => 'Forward Leads', 'slug' => 'leads.forward', 'group' => 'leads'],
            ['name' => 'Export Leads', 'slug' => 'leads.export', 'group' => 'leads'],
            ['name' => 'Bulk Upload Leads', 'slug' => 'leads.bulk_upload', 'group' => 'leads'],
            ['name' => 'Manage Stages', 'slug' => 'stages.manage', 'group' => 'leads'],
            ['name' => 'Manage Employees', 'slug' => 'employees.manage', 'group' => 'team'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group' => 'settings'],
            ['name' => 'Manage Tenants', 'slug' => 'tenants.manage', 'group' => 'platform'],
            ['name' => 'View Documents', 'slug' => 'documents.view', 'group' => 'documents'],
            ['name' => 'Create Documents', 'slug' => 'documents.create', 'group' => 'documents'],
            ['name' => 'Edit Documents', 'slug' => 'documents.edit', 'group' => 'documents'],
            ['name' => 'Send Documents', 'slug' => 'documents.send', 'group' => 'documents'],
            ['name' => 'View Customers', 'slug' => 'customers.view', 'group' => 'customers'],
            ['name' => 'Manage Customers', 'slug' => 'customers.manage', 'group' => 'customers'],
            ['name' => 'View Payments', 'slug' => 'payments.view', 'group' => 'payments'],
            ['name' => 'Manage Payments', 'slug' => 'payments.manage', 'group' => 'payments'],
            ['name' => 'View Accounting', 'slug' => 'accounting.view', 'group' => 'accounting'],
            ['name' => 'Manage Expenses', 'slug' => 'expenses.manage', 'group' => 'accounting'],
            ['name' => 'Manage Payroll', 'slug' => 'payroll.manage', 'group' => 'accounting'],
            ['name' => 'Export GST Reports', 'slug' => 'gst.export', 'group' => 'accounting'],
            ['name' => 'Manage Integrations', 'slug' => 'integrations.manage', 'group' => 'integrations'],
            ['name' => 'Manage Reviews', 'slug' => 'reviews.manage', 'group' => 'integrations'],
            ['name' => 'View Marketing', 'slug' => 'marketing.view', 'group' => 'marketing'],
            ['name' => 'Manage Social Posts', 'slug' => 'social.manage', 'group' => 'marketing'],
            ['name' => 'Run SEO Audit', 'slug' => 'seo.audit', 'group' => 'marketing'],
            ['name' => 'Manage Ad Campaigns', 'slug' => 'ads.manage', 'group' => 'marketing'],
            ['name' => 'View Inbox', 'slug' => 'inbox.view', 'group' => 'communication'],
            ['name' => 'Manage Templates', 'slug' => 'templates.manage', 'group' => 'communication'],
            ['name' => 'Manage Broadcasts', 'slug' => 'broadcasts.manage', 'group' => 'communication'],
            ['name' => 'Manage Automations', 'slug' => 'automations.manage', 'group' => 'communication'],
            ['name' => 'Manage Bots', 'slug' => 'bots.manage', 'group' => 'communication'],
            ['name' => 'View Tasks', 'slug' => 'tasks.view', 'group' => 'tasks'],
            ['name' => 'Manage Products', 'slug' => 'products.manage', 'group' => 'sales'],
            ['name' => 'View Orders', 'slug' => 'orders.view', 'group' => 'sales'],
            ['name' => 'Create Orders', 'slug' => 'orders.create', 'group' => 'sales'],
            ['name' => 'View Reports', 'slug' => 'reports.view', 'group' => 'reports'],
            ['name' => 'Manage Sales Targets', 'slug' => 'targets.manage', 'group' => 'reports'],
            ['name' => 'Manage Visiting Cards', 'slug' => 'visiting_cards.manage', 'group' => 'tools'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['slug' => $perm['slug']], $perm);
        }
    }

    protected function seedSubscriptionPlans(): void
    {
        SubscriptionPlan::firstOrCreate(['slug' => 'starter'], [
            'name' => 'Starter',
            'description' => 'CRM + 5 users + 1000 leads',
            'price_monthly' => 999,
            'price_yearly' => 9999,
            'max_users' => 5,
            'max_leads' => 1000,
            'features' => ['crm', 'leads', 'reminders'],
            'sort_order' => 1,
        ]);

        SubscriptionPlan::firstOrCreate(['slug' => 'growth'], [
            'name' => 'Growth',
            'description' => 'CRM + Marketing + 15 users',
            'price_monthly' => 2999,
            'price_yearly' => 29999,
            'max_users' => 15,
            'max_leads' => 10000,
            'features' => ['crm', 'leads', 'reminders', 'invoices', 'marketing'],
            'sort_order' => 2,
        ]);

        SubscriptionPlan::firstOrCreate(['slug' => 'enterprise'], [
            'name' => 'Enterprise',
            'description' => 'Full suite + unlimited users',
            'price_monthly' => 9999,
            'price_yearly' => 99999,
            'max_users' => 999,
            'max_leads' => 999999,
            'features' => ['all'],
            'sort_order' => 3,
        ]);
    }

    protected function seedSuperAdmin(): void
    {
        User::firstOrCreate(['email' => 'admin@platform.com'], [
            'name' => 'Super Admin',
            'password' => Hash::make('Admin@123'),
            'is_super_admin' => true,
            'is_active' => true,
        ]);
    }

    protected function seedDemoTenant(): void
    {
        $tenant = Tenant::firstOrCreate(['subdomain' => 'demo'], [
            'name' => 'Demo Company Pvt Ltd',
            'email' => 'admin@demo.com',
            'phone' => '9876543210',
            'company_type' => 'pvt_ltd',
            'gstin' => '29AABCU9603R1ZM',
            'address' => '123 Business Park, MG Road',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'pincode' => '560001',
            'is_active' => true,
            'settings' => [
                'bank_name' => 'HDFC Bank',
                'bank_account' => '50200012345678',
                'bank_ifsc' => 'HDFC0001234',
                'upi_id' => 'demo@hdfcbank',
                'google_review_link' => 'https://g.page/r/demo/review',
                'whatsapp_verify_token' => 'crm_verify_demo',
                'meta_verify_token' => 'crm_meta_demo',
                'auto_reply_reviews' => true,
                'whatsapp_connected' => true,
                'connected_sources' => ['Facebook Lead Ads', 'IndiaMART', 'JustDial', 'Zapier', 'Website Form', 'WhatsApp Business', 'CSV Import'],
                'whatsapp_templates' => [
                    'Hi {{name}}, thank you for your inquiry! How can we help you today?',
                    'Hello {{name}}, this is a reminder about your pending follow-up with us.',
                    'Dear {{name}}, we have a special offer for you. Reply to know more!',
                ],
            ],
        ]);

        $plan = SubscriptionPlan::where('slug', 'growth')->first();
        TenantSubscription::firstOrCreate(
            ['tenant_id' => $tenant->id, 'subscription_plan_id' => $plan->id],
            [
                'billing_cycle' => 'yearly',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
                'is_active' => true,
                'amount_paid' => 29999,
            ]
        );

        $allPermissions = Permission::all();
        $roleConfigs = [
            'admin' => ['name' => 'Admin', 'perms' => $allPermissions->pluck('slug')->toArray()],
            'manager' => ['name' => 'Manager', 'perms' => ['dashboard.view', 'leads.view_all', 'leads.create', 'leads.edit', 'leads.delete', 'leads.forward', 'leads.export', 'leads.bulk_upload', 'stages.manage', 'employees.manage', 'documents.view', 'documents.create', 'documents.send', 'customers.view', 'customers.manage', 'payments.view', 'payments.manage', 'accounting.view', 'expenses.manage', 'integrations.manage', 'reviews.manage', 'marketing.view', 'social.manage', 'seo.audit', 'ads.manage', 'inbox.view', 'templates.manage', 'broadcasts.manage', 'automations.manage', 'bots.manage', 'tasks.view', 'products.manage', 'orders.view', 'orders.create', 'reports.view', 'targets.manage', 'visiting_cards.manage', 'settings.manage']],
            'senior' => ['name' => 'Senior', 'perms' => ['dashboard.view', 'leads.view_all', 'leads.create', 'leads.edit', 'leads.forward', 'leads.export', 'leads.bulk_upload', 'documents.view', 'documents.create', 'customers.view', 'payments.view', 'inbox.view', 'templates.manage', 'tasks.view', 'orders.view', 'orders.create', 'products.manage', 'reports.view']],
            'junior' => ['name' => 'Junior', 'perms' => ['dashboard.view', 'leads.view_own', 'leads.create', 'leads.edit', 'leads.forward', 'tasks.view', 'inbox.view']],
            'accountant' => ['name' => 'Accountant', 'perms' => ['dashboard.view', 'leads.view_all', 'documents.view', 'documents.create', 'documents.send', 'customers.view', 'payments.view', 'payments.manage', 'accounting.view', 'expenses.manage', 'payroll.manage', 'gst.export']],
            'hr' => ['name' => 'HR', 'perms' => ['dashboard.view', 'employees.manage']],
            'marketing' => ['name' => 'Marketing', 'perms' => ['dashboard.view', 'leads.view_all', 'leads.create', 'leads.edit', 'integrations.manage', 'reviews.manage', 'marketing.view', 'social.manage', 'seo.audit', 'ads.manage', 'templates.manage', 'broadcasts.manage', 'automations.manage', 'bots.manage']],
        ];

        $roles = [];
        foreach ($roleConfigs as $slug => $config) {
            $role = Role::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $slug],
                ['name' => $config['name'], 'is_system' => true]
            );
            $permIds = Permission::whereIn('slug', $config['perms'])->pluck('id');
            $role->permissions()->sync($permIds);
            $roles[$slug] = $role;
        }

        $admin = User::firstOrCreate(['email' => 'admin@demo.com'], [
            'tenant_id' => $tenant->id,
            'role_id' => $roles['admin']->id,
            'name' => 'Demo Admin',
            'phone' => '9876543210',
            'password' => Hash::make('Demo@123'),
            'is_active' => true,
        ]);

        $senior = User::firstOrCreate(['email' => 'senior@demo.com'], [
            'tenant_id' => $tenant->id,
            'role_id' => $roles['senior']->id,
            'name' => 'Senior Executive',
            'phone' => '9876543211',
            'password' => Hash::make('Demo@123'),
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'junior@demo.com'], [
            'tenant_id' => $tenant->id,
            'role_id' => $roles['junior']->id,
            'name' => 'Junior Executive',
            'phone' => '9876543212',
            'password' => Hash::make('Demo@123'),
            'is_active' => true,
        ]);

        User::firstOrCreate(['email' => 'marketing@demo.com'], [
            'tenant_id' => $tenant->id,
            'role_id' => $roles['marketing']->id,
            'name' => 'Marketing Executive',
            'phone' => '9876543213',
            'password' => Hash::make('Demo@123'),
            'is_active' => true,
        ]);

        $stages = [
            ['name' => 'New', 'slug' => 'new', 'color' => '#6366f1', 'sort_order' => 1, 'is_default' => true],
            ['name' => 'Contacted', 'slug' => 'contacted', 'color' => '#8b5cf6', 'sort_order' => 2],
            ['name' => 'Qualified', 'slug' => 'qualified', 'color' => '#a855f7', 'sort_order' => 3],
            ['name' => 'Demo Scheduled', 'slug' => 'demo', 'color' => '#d946ef', 'sort_order' => 4],
            ['name' => 'Negotiation', 'slug' => 'negotiation', 'color' => '#f59e0b', 'sort_order' => 5],
            ['name' => 'Won', 'slug' => 'won', 'color' => '#22c55e', 'sort_order' => 6, 'is_won' => true],
            ['name' => 'Lost', 'slug' => 'lost', 'color' => '#ef4444', 'sort_order' => 7, 'is_lost' => true],
        ];

        $stageModels = [];
        foreach ($stages as $stage) {
            $stageModels[$stage['slug']] = LeadStage::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $stage['slug']],
                array_merge($stage, ['tenant_id' => $tenant->id])
            );
        }

        $labelConfigs = [
            ['name' => 'Hot', 'slug' => 'hot', 'color' => '#ef4444'],
            ['name' => 'Warm', 'slug' => 'warm', 'color' => '#f59e0b'],
            ['name' => 'Cold', 'slug' => 'cold', 'color' => '#3b82f6'],
            ['name' => 'VIP', 'slug' => 'vip', 'color' => '#8b5cf6'],
        ];
        $labelModels = [];
        foreach ($labelConfigs as $label) {
            $labelModels[$label['slug']] = LeadLabel::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $label['slug']],
                array_merge($label, ['tenant_id' => $tenant->id])
            );
        }

        $sampleLeads = [
            ['name' => 'Rajesh Kumar', 'email' => 'rajesh@example.com', 'phone' => '9988776655', 'company' => 'Tech Solutions', 'source' => 'website', 'stage' => 'new', 'label' => 'hot', 'service_type' => 'GST Filing', 'assigned' => $senior->id],
            ['name' => 'Priya Sharma', 'email' => 'priya@example.com', 'phone' => '9988776644', 'company' => 'Digital Marketing Co', 'source' => 'meta', 'stage' => 'contacted', 'label' => 'warm', 'service_type' => 'Website Design', 'assigned' => $senior->id],
            ['name' => 'Amit Patel', 'email' => 'amit@example.com', 'phone' => '9988776633', 'company' => 'Patel Industries', 'source' => 'google', 'stage' => 'qualified', 'label' => 'vip', 'service_type' => 'Company Registration', 'assigned' => $admin->id],
            ['name' => 'Sneha Reddy', 'email' => 'sneha@example.com', 'phone' => '9988776622', 'company' => 'Reddy Exports', 'source' => 'whatsapp', 'stage' => 'demo', 'label' => 'hot', 'service_type' => 'Export License', 'assigned' => $admin->id],
            ['name' => 'Vikram Singh', 'email' => 'vikram@example.com', 'phone' => '9988776611', 'company' => 'Singh Enterprises', 'source' => 'referral', 'stage' => 'negotiation', 'label' => 'cold', 'service_type' => 'Trademark', 'assigned' => $senior->id],
            ['name' => 'Unassigned Lead', 'email' => 'unassigned@example.com', 'phone' => '9988776600', 'company' => 'New Startup', 'source' => 'phone_book', 'stage' => 'new', 'label' => 'cold', 'service_type' => 'Company Registration', 'assigned' => null],
        ];

        foreach ($sampleLeads as $leadData) {
            $stage = $stageModels[$leadData['stage']];
            $label = $labelModels[$leadData['label']];
            $assigned = $leadData['assigned'] ?? null;
            unset($leadData['stage'], $leadData['label'], $leadData['assigned']);

            $lead = Lead::firstOrCreate(
                ['tenant_id' => $tenant->id, 'email' => $leadData['email']],
                array_merge($leadData, [
                    'tenant_id' => $tenant->id,
                    'lead_stage_id' => $stage->id,
                    'lead_label_id' => $label->id,
                    'assigned_to' => $assigned,
                    'created_by' => $admin->id,
                    'priority' => 'medium',
                    'city' => 'Bangalore',
                    'state' => 'Karnataka',
                ])
            );

            $lead->logActivity('created', 'Lead created', 'Sample lead from seeder');
        }

        Expense::firstOrCreate(
            ['tenant_id' => $tenant->id, 'invoice_number' => 'EXP-001'],
            [
                'vendor_name' => 'Office Supplies Co',
                'vendor_gstin' => '29AABCT1234F1Z5',
                'invoice_date' => now()->subDays(10),
                'category' => 'office',
                'description' => 'Office stationery and supplies',
                'is_gst_applicable' => true,
                'taxable_amount' => 5000,
                'cgst_amount' => 450,
                'sgst_amount' => 450,
                'igst_amount' => 0,
                'total_amount' => 5900,
                'gst_rate' => 18,
                'payment_status' => 'paid',
                'payment_method' => 'bank',
                'created_by' => $admin->id,
            ]
        );

        Expense::firstOrCreate(
            ['tenant_id' => $tenant->id, 'invoice_number' => 'EXP-002'],
            [
                'vendor_name' => 'Cloud Hosting Pvt Ltd',
                'vendor_gstin' => '27AABCH5678G1Z9',
                'invoice_date' => now()->subDays(5),
                'category' => 'software',
                'description' => 'Monthly cloud hosting',
                'is_gst_applicable' => true,
                'taxable_amount' => 3000,
                'cgst_amount' => 0,
                'sgst_amount' => 0,
                'igst_amount' => 540,
                'total_amount' => 3540,
                'gst_rate' => 18,
                'payment_status' => 'paid',
                'payment_method' => 'bank',
                'created_by' => $admin->id,
            ]
        );

        SocialPost::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Demo Launch Post'],
            [
                'platform' => 'instagram',
                'content' => '🚀 Demo Company ab naye CRM ke saath! Contact karein aaj hi.',
                'link_url' => 'https://demo.example.com',
                'scheduled_at' => now()->addDays(2)->setTime(10, 0),
                'status' => 'scheduled',
                'publish_mode' => 'manual',
                'created_by' => $admin->id,
            ]
        );

        SocialPost::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Welcome LinkedIn Post'],
            [
                'platform' => 'linkedin',
                'content' => 'Excited to share our new CRM platform for Indian businesses.',
                'published_at' => now()->subDays(3),
                'status' => 'published',
                'publish_mode' => 'manual',
                'created_by' => $admin->id,
            ]
        );

        SeoAudit::firstOrCreate(
            ['tenant_id' => $tenant->id, 'url' => 'https://demo.example.com'],
            [
                'score' => 72,
                'checks' => [
                    ['name' => 'Page Title', 'passed' => true, 'detail' => 'Title found (45 chars)'],
                    ['name' => 'Meta Description', 'passed' => true, 'detail' => 'Description found'],
                    ['name' => 'H1 Tag', 'passed' => true, 'detail' => 'Single H1 found'],
                    ['name' => 'Mobile Viewport', 'passed' => true, 'detail' => 'Viewport meta present'],
                    ['name' => 'Image Alt Tags', 'passed' => false, 'detail' => '2 images missing alt'],
                ],
                'recommendations' => [
                    'Add alt text to all images for accessibility and SEO.',
                    'Add structured data (JSON-LD) for local business.',
                ],
                'meta' => ['load_time' => 0.85],
                'audited_by' => $admin->id,
            ]
        );

        AdCampaign::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Google Search - CRM Keywords'],
            [
                'platform' => 'google',
                'external_campaign_id' => 'GADS-12345',
                'budget' => 50000,
                'spend' => 12500,
                'impressions' => 45000,
                'clicks' => 890,
                'leads_count' => 34,
                'cost_per_lead' => 367.65,
                'status' => 'active',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(30),
                'created_by' => $admin->id,
            ]
        );

        AdCampaign::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Meta Lead Ads - Bangalore'],
            [
                'platform' => 'meta',
                'budget' => 30000,
                'spend' => 8200,
                'impressions' => 120000,
                'clicks' => 2100,
                'leads_count' => 18,
                'cost_per_lead' => 455.56,
                'status' => 'active',
                'start_date' => now()->subDays(15),
                'created_by' => $admin->id,
            ]
        );

        $this->seedPhase6Modules($tenant, $admin, $senior);
        $this->call(DocumentSampleSeeder::class);
    }

    protected function seedPhase6Modules(Tenant $tenant, User $admin, User $senior): void
    {
        $lead = Lead::where('tenant_id', $tenant->id)->first();

        $defaultList = LeadList::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Default leadlist'],
            ['is_default' => true]
        );

        Lead::where('tenant_id', $tenant->id)->whereNull('lead_list_id')->update(['lead_list_id' => $defaultList->id]);

        LeadForm::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Website Contact Form'],
            ['lead_list_id' => $defaultList->id, 'created_by' => $admin->id, 'description' => 'Main website inquiry form', 'slug' => 'website-contact', 'leads_count' => 4, 'status' => 'active']
        );

        LeadForm::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Landing Page Form'],
            ['lead_list_id' => $defaultList->id, 'created_by' => $admin->id, 'description' => 'Google Ads landing page', 'slug' => 'landing-page', 'leads_count' => 2, 'status' => 'active']
        );

        MessageTemplate::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Welcome WhatsApp'],
            ['channel' => 'whatsapp', 'body' => 'Hi {{name}}, thank you for contacting us! How can we help?', 'is_active' => true]
        );

        $conv = WhatsappConversation::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '9988776655'],
            ['lead_id' => $lead?->id, 'assigned_to' => $senior->id, 'contact_name' => 'Rajesh Kumar', 'last_message_at' => now()->subMinutes(5), 'unread_count' => 1]
        );

        WhatsappMessage::firstOrCreate(
            ['conversation_id' => $conv->id, 'body' => 'Hi, I need GST filing service'],
            ['tenant_id' => $tenant->id, 'direction' => 'inbound', 'message_type' => 'text']
        );

        Broadcast::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'New Year Offer'],
            ['channel' => 'whatsapp', 'status' => 'sent', 'sent_at' => now()->subDays(2), 'total_recipients' => 6, 'delivered_count' => 6, 'opened_count' => 4, 'created_by' => $admin->id]
        );

        Automation::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Drip Follow-up Sequence'],
            ['trigger_type' => 'no_call_24h', 'actions' => [['type' => 'send_whatsapp', 'message' => 'Day 1 reminder'], ['type' => 'send_email', 'message' => 'Day 3 follow-up']], 'day_actions' => [['day' => 1, 'action' => 'send_whatsapp', 'message' => 'Hi, we tried calling you'], ['day' => 3, 'action' => 'send_email', 'message' => 'Follow-up email']], 'is_active' => true, 'runs_count' => 5, 'last_run_at' => now()->subHours(2), 'completed_count' => 4, 'error_count' => 1, 'leads_affected' => 5]
        );

        Automation::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Auto WhatsApp on New Lead'],
            ['trigger_type' => 'lead_created', 'actions' => [['type' => 'send_whatsapp', 'message' => 'Hi {{name}}, welcome! We received your inquiry.']], 'is_active' => true, 'runs_count' => 12, 'last_run_at' => now()->subMinutes(30), 'completed_count' => 11, 'error_count' => 1, 'leads_affected' => 12]
        );

        Broadcast::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'GST Filing Reminder'],
            ['channel' => 'email', 'status' => 'scheduled', 'scheduled_at' => now()->addDays(3), 'total_recipients' => 6, 'created_by' => $admin->id]
        );

        WhatsappBot::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Price Inquiry Bot'],
            ['description' => 'Auto-reply when lead asks about pricing', 'trigger_keyword' => 'price', 'is_active' => true, 'sessions_count' => 8, 'flow_data' => ['nodes' => [['id' => 'start', 'type' => 'start', 'label' => 'Start'], ['id' => 'msg1', 'type' => 'message', 'label' => 'Price Reply', 'text' => 'Our packages start from ₹999. Reply with your requirement.']], 'edges' => [['from' => 'start', 'to' => 'msg1']]]]
        );

        $product = Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'GST-001'],
            ['name' => 'GST Filing Service', 'category' => 'Compliance', 'unit' => 'Year', 'price' => 2999, 'tax_rate' => 18, 'hsn_sac' => '998314', 'is_active' => true]
        );

        Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'WEB-001'],
            ['name' => 'Website Design Package', 'category' => 'Digital', 'unit' => 'Project', 'price' => 15000, 'tax_rate' => 18, 'is_active' => true]
        );

        Product::firstOrCreate(
            ['tenant_id' => $tenant->id, 'sku' => 'TM-001'],
            ['name' => 'Trademark Registration', 'category' => 'Legal', 'unit' => 'Application', 'price' => 7999, 'tax_rate' => 18, 'is_active' => true]
        );

        $order = Order::firstOrCreate(
            ['tenant_id' => $tenant->id, 'order_number' => 'ORD-'.date('Y').'-0001'],
            ['lead_id' => $lead?->id, 'created_by' => $admin->id, 'status' => 'confirmed', 'subtotal' => 2999, 'tax_amount' => 539.82, 'total_amount' => 3538.82]
        );

        OrderItem::firstOrCreate(
            ['order_id' => $order->id, 'name' => 'GST Filing Service'],
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 2999, 'tax_rate' => 18, 'total' => 2999]
        );

        CrmTask::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Follow up with Rajesh Kumar'],
            ['lead_id' => $lead?->id, 'user_id' => $senior->id, 'created_by' => $admin->id, 'due_at' => now()->addHours(3), 'priority' => 'high', 'task_type' => 'call', 'status' => 'pending']
        );

        CallLog::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '9988776655', 'called_at' => now()->subHours(1)],
            ['lead_id' => $lead?->id, 'user_id' => $senior->id, 'direction' => 'outgoing', 'duration_seconds' => 245]
        );

        CallLog::firstOrCreate(
            ['tenant_id' => $tenant->id, 'phone' => '9988776644', 'called_at' => now()->subHours(5)],
            ['user_id' => $admin->id, 'direction' => 'missed', 'duration_seconds' => 0]
        );

        SalesTarget::firstOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $senior->id, 'metric_type' => 'sales_amount', 'month' => (int) now()->format('n'), 'year' => (int) now()->format('Y')],
            ['target_value' => 100000, 'achieved_value' => 45000]
        );

        $wonStage = LeadStage::where('tenant_id', $tenant->id)->where('is_won', true)->first();
        if ($wonStage) {
            Lead::where('tenant_id', $tenant->id)->where('email', 'amit@example.com')->update(['lead_stage_id' => $wonStage->id]);
        }

        CrmTask::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Overdue: Call Priya Sharma'],
            ['user_id' => $senior->id, 'created_by' => $admin->id, 'due_at' => now()->subDay(), 'priority' => 'high', 'task_type' => 'call', 'status' => 'pending']
        );

        CrmTask::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Send proposal to Amit Patel'],
            ['user_id' => $admin->id, 'created_by' => $admin->id, 'due_at' => now()->subDays(2), 'priority' => 'medium', 'task_type' => 'email', 'status' => 'completed', 'completed_at' => now()->subDay()]
        );

        CrmTask::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Demo call with Sneha Reddy'],
            ['user_id' => $admin->id, 'created_by' => $admin->id, 'due_at' => now()->addDays(2), 'priority' => 'high', 'task_type' => 'meeting', 'status' => 'pending']
        );

        CrmTask::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'WhatsApp follow-up Vikram'],
            ['user_id' => $senior->id, 'created_by' => $admin->id, 'due_at' => now()->subHours(5), 'priority' => 'medium', 'task_type' => 'whatsapp', 'status' => 'completed', 'completed_at' => now()->subHours(3)]
        );

        CrmTask::firstOrCreate(
            ['tenant_id' => $tenant->id, 'title' => 'Completed: Onboard Rajesh'],
            ['user_id' => $senior->id, 'created_by' => $admin->id, 'due_at' => now()->subDays(3), 'priority' => 'low', 'task_type' => 'follow_up', 'status' => 'completed', 'completed_at' => now()->subDays(2)]
        );
    }
}
