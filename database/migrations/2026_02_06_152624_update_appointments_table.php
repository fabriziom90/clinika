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
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('service_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
            $table->dropColumn('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // ripristino title
            $table->string('title')->after('service_id');

            // rimozione foreign key e colonna service_id
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
    }
};
