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
        Schema::create('recepciones_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recepciones_id');
            $table->unsignedBigInteger('rubros_id')->nullable();
            $table->string('rubros_nombre')->nullable();
            $table->string('rubros_unidad_medida')->nullable();
            $table->integer('cantidad_unidades')->nullable();
            $table->decimal('peso_unitario', 12);
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('tipo_adquisicion')->default('asignacion');
            $table->foreign('recepciones_id')->references('id')->on('recepciones')->cascadeOnDelete();
            $table->foreign('rubros_id')->references('id')->on('rubros')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepciones_items');
    }
};
