<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source');
            $table->string('event')->nullable();
            $table->json('payload')->nullable();
            $table->enum('status', ['received', 'processed', 'failed', 'ignored'])->default('received');
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'source', 'created_at']);
        });

        Schema::create('google_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reviewer_name');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->text('review_text')->nullable();
            $table->string('google_review_id')->nullable();
            $table->string('review_url')->nullable();
            $table->text('reply_text')->nullable();
            $table->boolean('auto_replied')->default(false);
            $table->timestamp('reply_sent_at')->nullable();
            $table->enum('sentiment', ['positive', 'neutral', 'negative'])->default('positive');
            $table->timestamps();
        });

        Schema::create('review_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('channel', ['whatsapp', 'sms', 'email'])->default('whatsapp');
            $table->enum('status', ['pending', 'sent', 'reviewed'])->default('sent');
            $table->string('review_link')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_requests');
        Schema::dropIfExists('google_reviews');
        Schema::dropIfExists('webhook_logs');
    }
};
