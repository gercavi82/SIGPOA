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
        Schema::create('budget_certification_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_certification_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('line_number');
            $table->text('concept')->nullable();
            $table->foreignId('poa_activity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('funding_source')->nullable();
            $table->decimal('amount', 18, 2);
            $table->string('status')->default('ACTIVA')->index();
            $table->timestamps();

            $table->unique(['budget_certification_id', 'line_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_certification_lines');
    }
};
