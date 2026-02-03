<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('tin')->nullable();
            $table->boolean('vat_registered')->default(false);
            $table->enum('category', ['goods', 'service', 'mixed'])->default('mixed');
            $table->json('bank_details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
