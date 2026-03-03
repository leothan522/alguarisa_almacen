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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('almacenes_id');
            $table->unsignedBigInteger('planes_id');
            $table->unsignedBigInteger('rubros_id');
            $table->integer('asignacion_cantidad')->nullable();
            $table->decimal('asignacion_peso', 12)->nullable();
            $table->decimal('asignacion_total', 12)->nullable();
            $table->integer('propia_cantidad')->nullable();
            $table->decimal('propia_peso', 12)->nullable();
            $table->decimal('propia_total', 12)->nullable();
            $table->decimal('total', 12)->nullable();
            $table->foreign('almacenes_id')->references('id')->on('almacenes')->cascadeOnDelete();
            $table->foreign('planes_id')->references('id')->on('planes')->cascadeOnDelete();
            $table->foreign('rubros_id')->references('id')->on('rubros')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
