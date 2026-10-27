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
        Schema::create('budget_ceilings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planning_period_id')->constrained()->restrictOnDelete();
            $table->string('source');
            $table->decimal('current_expenditure', 18, 2)->default(0);
            $table->decimal('investment_expenditure', 18, 2)->default(0);
            $table->decimal('total_amount', 18, 2);
            $table->text('methodology');
            $table->string('status')->default('BORRADOR')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->unique(['planning_period_id', 'source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_ceilings');
    }
};
