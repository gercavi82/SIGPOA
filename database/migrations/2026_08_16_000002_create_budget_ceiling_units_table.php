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
        Schema::create('budget_ceiling_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_ceiling_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organizational_unit_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 18, 2);
            $table->text('adjustment_reason')->nullable();
            $table->timestamps();
            $table->unique(['budget_ceiling_id', 'organizational_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_ceiling_units');
    }
};
