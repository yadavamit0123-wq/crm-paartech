<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->json('filter_config')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'name']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('lead_list_id')->nullable()->after('lead_label_id')->constrained('lead_lists')->nullOnDelete();
        });

        Schema::create('lead_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_list_id')->nullable()->constrained('lead_lists')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->nullable();
            $table->unsignedInteger('leads_count')->default(0);
            $table->enum('status', ['active', 'draft', 'inactive'])->default('active');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('description');
            $table->string('unit', 30)->default('Nos')->after('category');
        });

        Schema::table('automations', function (Blueprint $table) {
            $table->timestamp('last_run_at')->nullable()->after('runs_count');
            $table->unsignedInteger('completed_count')->default(0)->after('last_run_at');
            $table->unsignedInteger('error_count')->default(0)->after('completed_count');
            $table->unsignedInteger('leads_affected')->default(0)->after('error_count');
            $table->boolean('is_draft')->default(false)->after('is_active');
            $table->json('day_actions')->nullable()->after('actions');
        });

        Schema::table('crm_tasks', function (Blueprint $table) {
            $table->string('task_type', 30)->default('follow_up')->after('title');
        });

        Schema::table('whatsapp_bots', function (Blueprint $table) {
            $table->boolean('new_leads_only')->default(false)->after('is_active');
            $table->json('field_mapping')->nullable()->after('flow_data');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_bots', function (Blueprint $table) {
            $table->dropColumn(['new_leads_only', 'field_mapping']);
        });

        Schema::table('crm_tasks', function (Blueprint $table) {
            $table->dropColumn('task_type');
        });

        Schema::table('automations', function (Blueprint $table) {
            $table->dropColumn(['last_run_at', 'completed_count', 'error_count', 'leads_affected', 'is_draft', 'day_actions']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['category', 'unit']);
        });

        Schema::dropIfExists('lead_forms');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_list_id');
        });

        Schema::dropIfExists('lead_lists');
    }
};
