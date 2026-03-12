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
        Schema::create('recepciones_mermas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recepciones_id');
            $table->unsignedBigInteger('almacenes_id');
            $table->unsignedBigInteger('planes_id');
            $table->unsignedBigInteger('rubros_id');
            $table->string('tipo_adquisicion')->default('asignacion');
            $table->decimal('total', 12);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepciones_mermas');
    }
};
