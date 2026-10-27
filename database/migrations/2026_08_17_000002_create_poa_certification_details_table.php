<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('poa_certification_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poa_certification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('poa_activity_id')->constrained()->restrictOnDelete();
            $table->text('description')->nullable();
            $table->decimal('amount', 18, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poa_certification_details');
    }
};
