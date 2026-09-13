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
        Schema::table('despachos_detalles', function (Blueprint $table) {
            $table->decimal('peso_unitario', 12, 3)->nullable()->change();
            $table->decimal('total', 12, 3)->nullable()->change();
        });

        Schema::table('recepciones_items', function (Blueprint $table) {
            $table->decimal('peso_unitario', 12, 3)->nullable()->change();
            $table->decimal('total', 12, 3)->nullable()->change();
        });

        Schema::table('recepciones_mermas', function (Blueprint $table) {
            $table->decimal('total', 12, 3)->nullable()->change();
        });

        Schema::table('rubros', function (Blueprint $table) {
            $table->decimal('peso_unitario', 12, 3)->nullable()->change();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('asignacion_total', 12, 3)->nullable()->change();
            $table->decimal('propia_total', 12, 3)->nullable()->change();
            $table->decimal('total', 12, 3)->nullable()->change();
            $table->decimal('despacho_asignacion_total', 12, 3)->nullable()->change();
            $table->decimal('despacho_propia_total', 12, 3)->nullable()->change();
            $table->decimal('despacho_total', 12, 3)->nullable()->change();
            $table->decimal('stock_total', 12, 3)->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despachos_detalles', function (Blueprint $table) {
            $table->decimal('peso_unitario', 12, 2)->change();
            $table->decimal('total', 12, 2)->change();
        });

        Schema::table('recepciones_items', function (Blueprint $table) {
            $table->decimal('peso_unitario', 12, 2)->change();
            $table->decimal('total', 12, 2)->change();
        });

        Schema::table('recepciones_mermas', function (Blueprint $table) {
            $table->decimal('total', 12, 2)->change();
        });

        Schema::table('rubros', function (Blueprint $table) {
            $table->decimal('peso_unitario', 12, 2)->change();
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->decimal('asignacion_total', 12, 2)->change();
            $table->decimal('propia_total', 12, 2)->change();
            $table->decimal('total', 12, 2)->change();
            $table->decimal('despacho_asignacion_total', 12, 2)->change();
            $table->decimal('despacho_propia_total', 12, 2)->change();
            $table->decimal('despacho_total', 12, 2)->change();
            $table->decimal('stock_total', 12, 2)->change();
        });
    }
};
