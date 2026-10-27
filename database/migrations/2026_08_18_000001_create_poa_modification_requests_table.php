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
        Schema::create('poa_modification_requests', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('type')->index();
            $table->string('status')->default('BORRADOR')->index();
            $table->foreignId('poa_process_id')->constrained()->restrictOnDelete();
            $table->foreignId('organizational_unit_id')->constrained()->restrictOnDelete();
            $table->text('justification');
            $table->boolean('requires_esigef')->default(false);
            $table->string('esigef_reform_number')->nullable();
            $table->string('reform_cur')->nullable();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poa_modification_requests');
    }
};
