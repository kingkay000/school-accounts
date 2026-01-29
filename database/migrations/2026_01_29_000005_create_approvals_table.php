<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->foreignId('approver_id')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('reason')->nullable();
            $table->foreignId('evidence_attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
            $table->timestamps();

            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
