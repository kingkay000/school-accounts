<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->boolean('vat_applicable')->default(false);
            $table->decimal('vat_amount', 12, 2)->default(0);
            $table->boolean('wht_applicable')->default(false);
            $table->decimal('wht_rate', 5, 2)->default(0);
            $table->decimal('wht_amount', 12, 2)->default(0);
            $table->enum('status', ['pending', 'withheld', 'remitted'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_assessments');
    }
};
