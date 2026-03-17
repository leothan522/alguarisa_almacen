<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->integer('despacho_asignacion_cantidad')->nullable()->after('total');
            $table->decimal('despacho_asignacion_total', 12)->nullable()->after('despacho_asignacion_cantidad');
            $table->integer('despacho_propia_cantidad')->nullable()->after('despacho_asignacion_total');
            $table->decimal('despacho_propia_total', 12)->nullable()->after('despacho_propia_cantidad');
            $table->decimal('despacho_total', 12)->nullable()->after('despacho_propia_total');
            $table->integer('stock_cantidad')->nullable()->after('despacho_total');
            $table->decimal('stock_total', 12)->nullable()->after('stock_cantidad');
        });

        // 2. Copiamos el valor de 'total' a 'stock_total' para los registros existentes
        // Esto asegura que lo que ya tenías como "entrada" sea el stock inicial
        DB::table('stocks')->update([
            'stock_total' => DB::raw('total'),
            'stock_cantidad' => DB::raw('asignacion_cantidad + propia_cantidad'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stocks', function (Blueprint $table) {
            //
        });
    }
};
