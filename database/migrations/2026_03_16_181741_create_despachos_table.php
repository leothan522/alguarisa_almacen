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
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->date('fecha');
            $table->time('hora');
            $table->text('observacion')->nullable();
            $table->bigInteger('almacenes_id')->unsigned();
            $table->bigInteger('planes_id')->unsigned();
            $table->bigInteger('jefes_id')->unsigned()->nullable();
            $table->string('jefes_nombre');
            $table->string('jefes_cedula');
            $table->bigInteger('responsables_id')->unsigned()->nullable();
            $table->string('responsables_nombre');
            $table->string('responsables_cedula');
            $table->string('responsables_telefono')->nullable();
            $table->string('responsables_empresa')->nullable();
            $table->boolean('is_merma')->default(false);
            $table->boolean('is_return')->default(false);
            $table->boolean('is_complete')->default(false);
            $table->text('pdf_expediente')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('despachos')->onDelete('cascade');
            $table->foreign('almacenes_id')->references('id')->on('almacenes')->cascadeOnDelete();
            $table->foreign('planes_id')->references('id')->on('planes')->cascadeOnDelete();
            $table->foreign('jefes_id')->references('id')->on('jefes')->nullOnDelete();
            $table->foreign('responsables_id')->references('id')->on('responsables')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('despachos');
    }
};
