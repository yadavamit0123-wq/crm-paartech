<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\DocumentPdfController;
use App\Http\Controllers\GstExportController;
use App\Http\Controllers\PayrollPdfController;
use App\Http\Controllers\PublicPaymentController;
use App\Http\Controllers\VisitingCardController;
use App\Livewire\Accounting\Dashboard as AccountingDashboard;
use App\Livewire\AdCampaigns\Index as AdCampaignsIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Automations\Index as AutomationsIndex;
use App\Livewire\Bots\Builder as BotsBuilder;
use App\Livewire\Bots\Index as BotsIndex;
use App\Livewire\Broadcasts\Index as BroadcastsIndex;
use App\Livewire\CallLogs\Index as CallLogsIndex;
use App\Livewire\CustomFields\Index as CustomFieldsIndex;
use App\Livewire\Customers\Index as CustomersIndex;
use App\Livewire\Customers\Show as CustomerShow;
use App\Livewire\Dashboard;
use App\Livewire\Documents\Create as DocumentCreate;
use App\Livewire\Documents\Index as DocumentsIndex;
use App\Livewire\Documents\Show as DocumentShow;
use App\Livewire\Employees\Index as EmployeesIndex;
use App\Livewire\Expenses\Create as ExpenseCreate;
use App\Livewire\Expenses\Index as ExpensesIndex;
use App\Livewire\GstReports\Index as GstReportsIndex;
use App\Livewire\Inbox\Index as InboxIndex;
use App\Livewire\Integrations\Index as IntegrationsIndex;
use App\Livewire\LeadStages\Index as LeadStagesIndex;
use App\Livewire\Leads\AiAssistant;
use App\Livewire\Leads\AutoDialer;
use App\Livewire\Leads\BulkUpload;
use App\Livewire\Leads\Create as LeadCreate;
use App\Livewire\Leads\Dashboard as LeadsDashboard;
use App\Livewire\Leads\Forms as LeadsForms;
use App\Livewire\Leads\Index as LeadsIndex;
use App\Livewire\Leads\Labels as LeadsLabels;
use App\Livewire\Leads\LeadSources;
use App\Livewire\Leads\Show as LeadShow;
use App\Livewire\Marketing\Dashboard as MarketingDashboard;
use App\Livewire\Orders\Create as OrderCreate;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Orders\Show as OrderShow;
use App\Livewire\Payroll\Index as PayrollIndex;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Reviews\Index as ReviewsIndex;
use App\Livewire\SalesTargets\Index as SalesTargetsIndex;
use App\Livewire\SeoAudit\Index as SeoAuditIndex;
use App\Livewire\Settings\Index as SettingsIndex;
use App\Livewire\SocialPosts\Index as SocialPostsIndex;
use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;
use App\Livewire\Tasks\Index as TasksIndex;
use App\Livewire\Templates\Index as TemplatesIndex;
use App\Livewire\VisitingCards\Index as VisitingCardsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->isSuperAdmin() ? 'super-admin.dashboard' : 'dashboard')
        : redirect()->route('login');
});

Route::get('/pay/{milestone}', [PublicPaymentController::class, 'show'])->name('payments.public');
Route::get('/card/{slug}', [VisitingCardController::class, 'show'])->name('visiting-cards.public');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::middleware('super_admin')->prefix('super-admin')->group(function () {
        Route::get('/dashboard', SuperAdminDashboard::class)->name('super-admin.dashboard');
    });

    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/dashboard', Dashboard::class)->name('dashboard');
    });

    // ─── Leads CRM Hub (all 3sigma modules nested here) ───
    Route::prefix('leads')->group(function () {
        Route::redirect('/', '/leads/dashboard');

        Route::get('/dashboard', LeadsDashboard::class)
            ->middleware('permission_any:leads.view_own,leads.view_all')
            ->name('leads.dashboard');

        Route::get('/list', LeadsIndex::class)
            ->middleware('permission_any:leads.view_own,leads.view_all')
            ->name('leads.list');

        Route::get('/forms', LeadsForms::class)
            ->middleware('permission_any:leads.view_own,leads.view_all')
            ->name('leads.forms');

        Route::get('/labels', LeadsLabels::class)
            ->middleware('permission:leads.edit')
            ->name('leads.labels');

        Route::get('/create', LeadCreate::class)->middleware('permission:leads.create')->name('leads.create');
        Route::get('/bulk-upload', BulkUpload::class)->middleware('permission:leads.bulk_upload')->name('leads.bulk-upload');
        Route::get('/auto-dialer', AutoDialer::class)->middleware('permission_any:leads.view_own,leads.view_all')->name('leads.auto-dialer');
        Route::get('/lead-sources', LeadSources::class)->middleware('permission:integrations.manage')->name('leads.lead-sources');
        Route::get('/ai-assistant', AiAssistant::class)->middleware('permission_any:leads.view_own,leads.view_all')->name('leads.ai-assistant');

        Route::get('/inbox', InboxIndex::class)->middleware('permission:inbox.view')->name('leads.inbox');
        Route::get('/tasks', TasksIndex::class)->middleware('permission:tasks.view')->name('leads.tasks');
        Route::get('/products', ProductsIndex::class)->middleware('permission:products.manage')->name('leads.products');
        Route::get('/orders', OrdersIndex::class)->middleware('permission:orders.view')->name('leads.orders');
        Route::get('/orders/create', OrderCreate::class)->middleware('permission:orders.create')->name('leads.orders.create');
        Route::get('/orders/{order}', OrderShow::class)->middleware('permission:orders.view')->name('leads.orders.show');
        Route::get('/templates', TemplatesIndex::class)->middleware('permission:templates.manage')->name('leads.templates');
        Route::get('/broadcasts', BroadcastsIndex::class)->middleware('permission:broadcasts.manage')->name('leads.broadcasts');
        Route::get('/automations', AutomationsIndex::class)->middleware('permission:automations.manage')->name('leads.automations');
        Route::get('/bots', BotsIndex::class)->middleware('permission:bots.manage')->name('leads.bots');
        Route::get('/bots/{bot}/builder', BotsBuilder::class)->middleware('permission:bots.manage')->name('leads.bots.builder');
        Route::get('/reports', ReportsIndex::class)->middleware('permission:reports.view')->name('leads.reports');
        Route::get('/call-logs', CallLogsIndex::class)->middleware('permission:reports.view')->name('leads.call-logs');
        Route::get('/sales-targets', SalesTargetsIndex::class)->middleware('permission:targets.manage')->name('leads.sales-targets');
        Route::get('/custom-fields', CustomFieldsIndex::class)->middleware('permission:settings.manage')->name('leads.custom-fields');
        Route::get('/visiting-cards', VisitingCardsIndex::class)->middleware('permission:visiting_cards.manage')->name('leads.visiting-cards');
        Route::get('/settings', SettingsIndex::class)->middleware('permission:settings.manage')->name('leads.settings');
        Route::get('/reviews', ReviewsIndex::class)->middleware('permission:reviews.manage')->name('leads.reviews');
        Route::get('/stages', LeadStagesIndex::class)->middleware('permission:stages.manage')->name('leads.stages');
        Route::get('/team', EmployeesIndex::class)->middleware('permission:employees.manage')->name('leads.team');

        Route::prefix('documents')->group(function () {
            Route::get('/', DocumentsIndex::class)->middleware('permission:documents.view')->name('leads.documents');
            Route::get('/create', DocumentCreate::class)->middleware('permission:documents.create')->name('leads.documents.create');
            Route::get('/{document}/edit', DocumentCreate::class)->middleware('permission:documents.create')->name('leads.documents.edit');
            Route::get('/{document}/pdf', [DocumentPdfController::class, 'stream'])->middleware('permission:documents.view')->name('leads.documents.pdf');
            Route::get('/{document}/download', [DocumentPdfController::class, 'download'])->middleware('permission:documents.view')->name('leads.documents.download');
            Route::get('/{document}', DocumentShow::class)->middleware('permission:documents.view')->name('leads.documents.show');
        });

        Route::get('/customers', CustomersIndex::class)->middleware('permission:customers.view')->name('leads.customers');
        Route::get('/customers/{customer}', CustomerShow::class)->middleware('permission:customers.view')->name('leads.customers.show');

        Route::get('/{lead}', LeadShow::class)
            ->middleware('permission_any:leads.view_own,leads.view_all')
            ->whereNumber('lead')
            ->name('leads.show');
    });

    // Legacy redirects (old URLs still work)
    Route::redirect('/inbox', '/leads/inbox');
    Route::redirect('/tasks', '/leads/tasks');
    Route::redirect('/products', '/leads/products');
    Route::redirect('/orders', '/leads/orders');
    Route::redirect('/templates', '/leads/templates');
    Route::redirect('/broadcasts', '/leads/broadcasts');
    Route::redirect('/automations', '/leads/automations');
    Route::redirect('/bots', '/leads/bots');
    Route::redirect('/reports', '/leads/reports');
    Route::redirect('/call-logs', '/leads/call-logs');
    Route::redirect('/sales-targets', '/leads/sales-targets');
    Route::redirect('/custom-fields', '/leads/custom-fields');
    Route::redirect('/visiting-cards', '/leads/visiting-cards');
    Route::redirect('/settings', '/leads/settings');
    Route::redirect('/stages', '/leads/stages');
    Route::redirect('/employees', '/leads/team');
    Route::redirect('/documents', '/leads/documents');
    Route::redirect('/customers', '/leads/customers');
    Route::redirect('/reviews', '/leads/reviews');

    // ─── Accounting (separate from Leads hub) ───
    Route::middleware('permission:accounting.view')->group(function () {
        Route::get('/accounting', AccountingDashboard::class)->name('accounting.dashboard');
    });

    Route::prefix('expenses')->group(function () {
        Route::get('/', ExpensesIndex::class)->middleware('permission:accounting.view')->name('expenses.index');
        Route::get('/create', ExpenseCreate::class)->middleware('permission:expenses.manage')->name('expenses.create');
    });

    Route::get('/payroll', PayrollIndex::class)->middleware('permission:payroll.manage')->name('payroll.index');
    Route::get('/payroll/{payrollRun}/pdf', [PayrollPdfController::class, 'download'])->middleware('permission:payroll.manage')->name('payroll.pdf');

    Route::get('/gst-reports', GstReportsIndex::class)->middleware('permission:gst.export')->name('gst-reports.index');

    Route::prefix('gst-export')->middleware('permission:gst.export')->group(function () {
        Route::get('/gstr1/csv', [GstExportController::class, 'gstr1Csv'])->name('gst.export.gstr1.csv');
        Route::get('/gstr1/json', [GstExportController::class, 'gstr1Json'])->name('gst.export.gstr1.json');
        Route::get('/gstr3b/csv', [GstExportController::class, 'gstr3bCsv'])->name('gst.export.gstr3b.csv');
        Route::get('/gstr3b/json', [GstExportController::class, 'gstr3bJson'])->name('gst.export.gstr3b.json');
        Route::get('/sales-register', [GstExportController::class, 'salesRegister'])->name('gst.export.sales');
        Route::get('/purchase-register', [GstExportController::class, 'purchaseRegister'])->name('gst.export.purchase');
    });

    // ─── Marketing (separate) ───
    Route::get('/integrations', IntegrationsIndex::class)->middleware('permission:integrations.manage')->name('integrations.index');

    Route::middleware('permission:marketing.view')->group(function () {
        Route::get('/marketing', MarketingDashboard::class)->name('marketing.dashboard');
    });
    Route::get('/social-posts', SocialPostsIndex::class)->middleware('permission:social.manage')->name('social-posts.index');
    Route::get('/seo-audit', SeoAuditIndex::class)->middleware('permission:seo.audit')->name('seo-audit.index');
    Route::get('/ad-campaigns', AdCampaignsIndex::class)->middleware('permission:ads.manage')->name('ad-campaigns.index');
});
