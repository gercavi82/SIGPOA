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
        Schema::create('poa_activity_phase_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poa_activity_id')->constrained()->restrictOnDelete();
            $table->foreignId('poa_process_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('priority')->nullable();
            $table->decimal('adjusted_amount', 18, 2);
            $table->decimal('adjusted_target', 18, 4)->nullable();
            $table->text('justification')->nullable();
            $table->string('status')->default('BORRADOR')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['poa_activity_id', 'poa_process_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poa_activity_phase_allocations');
    }
};
