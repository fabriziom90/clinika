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
        Schema::table('patients', function (Blueprint $table) {
            // Rimuovi la foreign key se esiste
            if (Schema::hasColumn('patients', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }

            // Aggiungi le nuove colonne come prime tre
            $table->string('name')->after('id');
            $table->string('surname')->after('name');
            $table->string('email')->after('surname');
            $table->dropColumn('vat');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Rimuovi le nuove colonne
            $table->dropColumn(['name', 'surname', 'email']);

            // Ripristina la foreign key se necessario
            $table->string('vat', 11);
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
