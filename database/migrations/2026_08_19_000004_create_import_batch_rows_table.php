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
        Schema::create('import_batch_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_batch_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('cur_number')->index();
            $table->unsignedSmallInteger('cur_line')->default(1);
            $table->string('certification_number')->nullable()->index();
            $table->string('activity_code')->nullable()->index();
            $table->foreignId('poa_activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('budget_certification_id')->nullable()->constrained()->nullOnDelete();
            $table->date('accrual_date')->nullable();
            $table->decimal('amount', 18, 2);
            $table->string('status')->default('VALIDO')->index();
            $table->text('error_message')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['import_batch_id', 'row_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_batch_rows');
    }
};
