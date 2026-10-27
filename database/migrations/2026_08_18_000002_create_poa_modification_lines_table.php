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
        Schema::create('poa_modification_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poa_modification_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_activity_id')->nullable()->constrained('poa_activities')->restrictOnDelete();
            $table->foreignId('destination_activity_id')->nullable()->constrained('poa_activities')->restrictOnDelete();
            $table->decimal('debit_amount', 18, 2)->default(0);
            $table->decimal('credit_amount', 18, 2)->default(0);
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poa_modification_lines');
    }
};
