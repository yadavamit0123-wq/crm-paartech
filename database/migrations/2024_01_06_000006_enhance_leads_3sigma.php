<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_labels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color', 20)->default('#6366f1');
            $table->timestamps();
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('lead_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['lead_id', 'status', 'due_at']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('lead_label_id')->nullable()->after('lead_stage_id')->constrained('lead_labels')->nullOnDelete();
            $table->string('service_type')->nullable()->after('campaign');
            $table->timestamp('last_call_at')->nullable()->after('last_contacted_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lead_label_id');
            $table->dropColumn(['service_type', 'last_call_at']);
        });
        Schema::dropIfExists('lead_tasks');
        Schema::dropIfExists('lead_labels');
    }
};
