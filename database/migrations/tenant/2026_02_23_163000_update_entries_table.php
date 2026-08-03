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
        Schema::table('medical_entries', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('content');
            $table->dropColumn('type');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign('prescriptions_medical_entry_id_foreign');
            $table->dropColumn('medical_entry_id');
            $table->foreignId('medical_entry_version_id')->constrained('medical_entry_versions')->onDelete('cascade');
        });

        Schema::table('vital_parameters', function (Blueprint $table) {
            $table->dropForeign('vital_parameters_medical_entry_id_foreign');
            $table->dropColumn('medical_entry_id');
            $table->foreignId('medical_entry_version_id')->constrained('medical_entry_versions')->onDelete('cascade');
        });

        Schema::table('medical_attachments', function (Blueprint $table) {
            $table->dropForeign('medical_attachments_medical_entry_id_foreign');
            $table->dropColumn('medical_entry_id');
            $table->foreignId('medical_entry_version_id')->constrained('medical_entry_versions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // medical_entries: ripristina le colonne rimosse
        Schema::table('medical_entries', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('type')->nullable();
        });

        // prescriptions: ripristina la colonna e foreign originale
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['medical_entry_version_id']);
            $table->dropColumn('medical_entry_version_id');

            $table->foreignId('medical_entry_id')->constrained('medical_entries')->onDelete('cascade');
        });

        // vital_parameters: ripristina la colonna e foreign originale
        Schema::table('vital_parameters', function (Blueprint $table) {
            $table->dropForeign(['medical_entry_version_id']);
            $table->dropColumn('medical_entry_version_id');

            $table->foreignId('medical_entry_id')->constrained('medical_entries')->onDelete('cascade');
        });

        Schema::table('medical_attachments', function (Blueprint $table) {
            $table->dropForeign(['medical_entry_version_id']);
            $table->dropColumn('medical_entry_version_id');

            $table->foreignId('medical_entry_id')->constrained('medical_entries')->onDelete('cascade');
        });
    }
};
