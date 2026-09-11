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
        Schema::table('clinics', function (Blueprint $table) {
            $table->text('email')->nullable()->change();
            $table->text('phone')->nullable()->change();
            $table->text('address')->nullable()->change();
            $table->text('city')->nullable()->change();
            $table->text('province')->nullable()->change();
            $table->text('zip_code')->nullable()->change();
            $table->text('vat_number')->nullable()->change();
            $table->text('tax_code')->nullable()->change();
            $table->text('db_username')->change();
            $table->text('db_password')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->string('city')->nullable()->change();
            $table->string('province')->nullable()->change();
            $table->string('zip_code')->nullable()->change();
            $table->string('vat_number')->nullable()->change();
            $table->string('tax_code')->nullable()->change();
            $table->string('db_username')->change();
            $table->string('db_password')->change();
        });
    }
};
