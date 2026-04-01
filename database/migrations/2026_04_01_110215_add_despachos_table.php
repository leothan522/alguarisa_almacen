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
        Schema::table('despachos', function (Blueprint $table) {
            $table->boolean('is_sealed')->default(false)->after('is_return');
            $table->text('image_documento')->nullable()->after('is_complete');
            $table->text('image_1')->nullable()->after('image_documento');
            $table->text('image_2')->nullable()->after('image_1');
            $table->boolean('is_adjustment')->default(false)->after('pdf_expediente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('despachos', function (Blueprint $table) {
            //
        });
    }
};
