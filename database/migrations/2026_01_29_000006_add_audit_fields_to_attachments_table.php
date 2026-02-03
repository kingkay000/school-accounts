<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->foreignId('transaction_id')->nullable()->after('ledger_entry_id')->constrained('transactions')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->after('transaction_id')->constrained('vendors')->nullOnDelete();
            $table->enum('document_type', ['invoice', 'receipt', 'delivery_note', 'grn', 'completion_report', 'purchase_request', 'asset_registry', 'payment', 'other'])
                ->default('other')
                ->after('vendor_id');
            $table->json('metadata')->nullable()->after('document_type');
            $table->string('file_hash')->nullable()->after('metadata');
        });
    }

    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropForeign(['vendor_id']);
            $table->dropColumn(['transaction_id', 'vendor_id', 'document_type', 'metadata', 'file_hash']);
        });
    }
};
