<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();

            // clinic data
            $table->string('name');
            $table->string('slug')->unique();

            // contacts
            $table->text('email')->nullable();
            $table->text('phone')->nullable();

            // address
            $table->text('address')->nullable();
            $table->text('city')->nullable();
            $table->text('province')->nullable();
            $table->text('zip_code')->nullable();

            // vat data
            $table->text('vat_number')->nullable();
            $table->text('tax_code')->nullable();

            // logo
            $table->text('logo_path')->nullable();

            // database tenant
            $table->string('database');
            $table->string('db_host');
            $table->string('db_port')->default('3306');
            $table->text('db_username');
            $table->text('db_password');

            // state
            $table->boolean('active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
