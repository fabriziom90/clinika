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
        Schema::create('doctor_compensation', function (Blueprint $table) {
            $table->id();

            $table->foreignId('doctor_id')
                ->constrained('doctors')
                ->cascadeOnDelete();

            $table->foreignId('service_id')
                ->constrained('services')
                ->restrictOnDelete();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->cascadeOnDelete();

            $table->foreignId('invoice_item_id')
                ->unique()
                ->constrained('invoice_items')
                ->cascadeOnDelete();

            $table->enum('compensation_type', ['percentage', 'fixed']);

            $table->decimal('compensation_value', 10, 2);

            $table->decimal('service_amount', 10, 2);

            $table->decimal('compensation_amount', 10, 2);

            $table->enum('status', ['accrued', 'paid'])
                ->default('accrued');

            $table->timestamp('accrued_at')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_compensation');
    }
};
