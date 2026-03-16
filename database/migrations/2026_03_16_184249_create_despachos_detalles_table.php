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
        Schema::create('despachos_detalles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('despachos_id');
            $table->unsignedBigInteger('rubros_id')->nullable();
            $table->string('rubros_nombre')->nullable();
            $table->string('rubros_unidad_medida')->nullable();
            $table->integer('cantidad_unidades')->nullable();
            $table->decimal('peso_unitario', 12);
            $table->decimal('total', 12)->nullable();
            $table->string('tipo_adquisicion')->default('asignacion');
            $table->foreign('despachos_id')->references('id')->on('despachos')->cascadeOnDelete();
            $table->foreign('rubros_id')->references('id')->on('rubros')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos_detalles');
    }
};
