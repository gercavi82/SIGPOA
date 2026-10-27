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
        Schema::create('poa_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poa_process_id')->constrained()->restrictOnDelete();
            $table->foreignId('organizational_unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('programmatic_structure_id')->nullable()->constrained()->nullOnDelete();
            $table->string('number');
            $table->text('description');
            $table->text('objective');
            $table->unsignedSmallInteger('priority')->nullable();
            $table->string('status')->default('BORRADOR')->index();
            $table->string('unit_of_measure')->nullable();
            $table->decimal('annual_target', 18, 4)->default(0);
            $table->decimal('annual_amount', 18, 2)->default(0);
            $table->foreignId('responsible_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['poa_process_id', 'number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poa_activities');
    }
};
