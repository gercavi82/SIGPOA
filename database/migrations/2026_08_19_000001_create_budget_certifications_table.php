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
        Schema::create('budget_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poa_process_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('poa_certification_id')->nullable()->constrained()->nullOnDelete();
            $table->string('certification_number')->index();
            $table->date('certification_date');
            $table->unsignedSmallInteger('year')->index();
            $table->decimal('total_amount', 18, 2);
            $table->string('funding_source')->nullable();
            $table->string('status')->default('ACTIVA')->index();
            $table->text('concept')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['year', 'certification_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_certifications');
    }
};
