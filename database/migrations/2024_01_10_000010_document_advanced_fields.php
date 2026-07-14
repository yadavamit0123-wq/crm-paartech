<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (! Schema::hasColumn('documents', 'logo_path')) {
                $table->string('logo_path')->nullable()->after('seller_snapshot');
            }
            if (! Schema::hasColumn('documents', 'signature_data')) {
                $table->json('signature_data')->nullable()->after('logo_path');
            }
            if (! Schema::hasColumn('documents', 'attachments')) {
                $table->json('attachments')->nullable()->after('signature_data');
            }
            if (! Schema::hasColumn('documents', 'contact_details')) {
                $table->json('contact_details')->nullable()->after('attachments');
            }
            if (! Schema::hasColumn('documents', 'additional_info')) {
                $table->text('additional_info')->nullable()->after('contact_details');
            }
            if (! Schema::hasColumn('documents', 'exchange_rate')) {
                $table->decimal('exchange_rate', 12, 6)->nullable()->after('currency');
            }
        });

        Schema::table('document_items', function (Blueprint $table) {
            if (! Schema::hasColumn('document_items', 'image_path')) {
                $table->string('image_path')->nullable()->after('group_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_items', function (Blueprint $table) {
            if (Schema::hasColumn('document_items', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });

        Schema::table('documents', function (Blueprint $table) {
            foreach (['logo_path', 'signature_data', 'attachments', 'contact_details', 'additional_info', 'exchange_rate'] as $col) {
                if (Schema::hasColumn('documents', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
