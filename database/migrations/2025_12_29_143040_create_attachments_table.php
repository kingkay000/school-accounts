<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_log_id')->nullable()->constrained('bank_logs');
            $table->foreignId('ledger_entry_id')->nullable()->constrained('ledger_entries');
            $table->string('google_drive_file_id');
            $table->string('thumbnail_url')->nullable();
            $table->text('extracted_text')->nullable();
            $table->string('file_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
