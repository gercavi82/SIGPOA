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
        Schema::create('poa_activity_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poa_activity_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->decimal('target', 18, 4)->default(0);
            $table->decimal('amount', 18, 2)->default(0);
            $table->timestamps();
            $table->unique(['poa_activity_id', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poa_activity_months');
    }
};
