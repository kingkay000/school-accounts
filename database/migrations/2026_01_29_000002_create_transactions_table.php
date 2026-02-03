<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_log_id')->nullable()->constrained('bank_logs')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->enum('direction', ['in', 'out'])->default('out');
            $table->decimal('amount', 12, 2)->default(0);
            $table->date('txn_date')->nullable();
            $table->string('counterparty_name')->nullable();
            $table->text('narration')->nullable();
            $table->enum('transaction_type', ['goods', 'service', 'asset', 'other'])->default('other');
            $table->enum('status', ['captured', 'documented', 'matched', 'validated', 'tax_assessed', 'audit_ready', 'exception', 'closed'])
                ->default('captured');
            $table->json('risk_flags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
