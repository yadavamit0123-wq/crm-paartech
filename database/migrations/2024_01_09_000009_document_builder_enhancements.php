<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'title')) {
                $table->string('title')->nullable()->after('document_number');
            }
            if (! Schema::hasColumn('documents', 'template_key')) {
                $table->string('template_key', 50)->default('classic_purple')->after('title');
            }
            if (! Schema::hasColumn('documents', 'theme_color')) {
                $table->string('theme_color', 20)->default('#7c3aed')->after('template_key');
            }
            if (! Schema::hasColumn('documents', 'currency')) {
                $table->string('currency', 10)->default('INR')->after('theme_color');
            }
            if (! Schema::hasColumn('documents', 'customer_state')) {
                $table->string('customer_state')->nullable()->after('customer_address');
            }
            if (! Schema::hasColumn('documents', 'customer_phone')) {
                $table->string('customer_phone', 30)->nullable()->after('customer_state');
            }
            if (! Schema::hasColumn('documents', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('customer_phone');
            }
            if (! Schema::hasColumn('documents', 'seller_snapshot')) {
                $table->json('seller_snapshot')->nullable()->after('customer_email');
            }
            if (! Schema::hasColumn('documents', 'conversion_path')) {
                $table->json('conversion_path')->nullable()->after('reference_document_id');
            }
            if (! Schema::hasColumn('documents', 'doc_discount_type')) {
                $table->string('doc_discount_type', 10)->default('percent')->after('discount_percent');
            }
            if (! Schema::hasColumn('documents', 'doc_discount_value')) {
                $table->decimal('doc_discount_value', 12, 2)->default(0)->after('doc_discount_type');
            }
            if (! Schema::hasColumn('documents', 'additional_charges')) {
                $table->json('additional_charges')->nullable()->after('doc_discount_value');
            }
            if (! Schema::hasColumn('documents', 'total_in_words')) {
                $table->string('total_in_words')->nullable()->after('grand_total');
            }
            if (! Schema::hasColumn('documents', 'advanced_options')) {
                $table->json('advanced_options')->nullable()->after('terms_conditions');
            }
            if (! Schema::hasColumn('documents', 'shipping_details')) {
                $table->json('shipping_details')->nullable()->after('advanced_options');
            }
        });

        Schema::table('document_items', function (Blueprint $table) {
            if (! Schema::hasColumn('document_items', 'long_description')) {
                $table->text('long_description')->nullable()->after('description');
            }
            if (! Schema::hasColumn('document_items', 'discount_type')) {
                $table->string('discount_type', 10)->default('fixed')->after('rate');
            }
            if (! Schema::hasColumn('document_items', 'group_name')) {
                $table->string('group_name')->nullable()->after('long_description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_items', function (Blueprint $table) {
            $table->dropColumn(['long_description', 'discount_type', 'group_name']);
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'title', 'template_key', 'theme_color', 'currency', 'customer_state',
                'customer_phone', 'customer_email', 'seller_snapshot', 'conversion_path',
                'doc_discount_type', 'doc_discount_value', 'additional_charges',
                'total_in_words', 'advanced_options', 'shipping_details',
            ]);
        });
    }
};
