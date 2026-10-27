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
        Schema::create('poa_certification_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poa_certification_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('description');
            $table->json('changes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poa_certification_updates');
    }
};
