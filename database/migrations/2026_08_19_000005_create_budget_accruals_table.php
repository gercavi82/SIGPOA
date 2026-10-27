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
        Schema::create('budget_accruals', function (Blueprint $table) {
            $table->id();
            $table->string('cur_number')->index();
            $table->unsignedSmallInteger('cur_line')->default(1);
            $table->unsignedSmallInteger('year')->index();
            $table->unsignedTinyInteger('month')->index();
            $table->date('accrual_date');
            $table->decimal('accrued_amount', 18, 2);
            $table->foreignId('poa_activity_id')->constrained()->restrictOnDelete();
            $table->foreignId('budget_certification_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('import_batch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('import_batch_row_id')->nullable()->constrained()->nullOnDelete();
            $table->text('observations')->nullable();
            $table->string('status')->default('ACTIVO')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['year', 'cur_number', 'cur_line', 'poa_activity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_accruals');
    }
};
