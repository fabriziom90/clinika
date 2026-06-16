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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->foreignId("appointment_id")->nullable()->constrained()->nullOnDelete();
            $table->foreignId("doctor_id")->nullable()->constrained()->nullOnDelete();
            $table->foreignId("patient_id")->nullable()->constrained()->nullOnDelete();
            $table->string("number"); //year/number;
            $table->integer("progressive_number");
            $table->integer("year");
            $table->date("date");
            $table->decimal("subtotal", 10, 2);
            $table->decimal("vat_amount", 10, 2);
            $table->decimal("stamp_duty", 10, 2)->default(2);
            $table->decimal("discount_amount", 10, 2)->default(0);
            $table->decimal("total", 10, 2);
            $table->float("amount");

            $table->string("status")->default("draft");

            //patient snapshot
            $table->string("full_name");
            $table->string("vat_number");
            $table->string("address");
            $table->string("city");
            $table->string("zip_code");

            $table->text("description");

            //metadata
            $table->foreignId("user_id")->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
