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
            $table->dropForeign('medical_entries_previous_entry_id_foreign');
            $table->dropColumn('previous_entry_id');
            $table->foreignId('cancelled_by')->nullable()->constrained('doctors')->after('patient_id')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_entries', function (Blueprint $table) {
            // Rimuove foreign e colonna cancelled_by
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_by', 'cancelled_at']);

            // Ripristina previous_entry_id
            $table->foreignId('previous_entry_id')
                ->nullable()
                ->constrained('medical_entries')
                ->nullOnDelete();
        });
    }
};
