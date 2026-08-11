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
        Schema::create('consent_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consent_type_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('version');
            $table->longText('content');
            $table->boolean('is_active')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'consent_type_id', 'version'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consent_versions');
    }
};
