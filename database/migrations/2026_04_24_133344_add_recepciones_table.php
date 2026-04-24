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
        Schema::table('recepciones', function (Blueprint $table) {
            $table->string('asignacion_referencia')->nullable()->after('is_adjustment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recepciones', function (Blueprint $table) {
            //
        });
    }
};
