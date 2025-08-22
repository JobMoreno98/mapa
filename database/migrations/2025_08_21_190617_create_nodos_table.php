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
        Schema::create('nodos', function (Blueprint $table) {
           $table->id();
            $table->string('nombre')->unique()->index();  // El ID es único y será la clave primaria
            $table->string('edificio');
            $table->string('piso');
            $table->decimal('lat', 10, 6);  // Latitud con 6 decimales
            $table->decimal('lng', 10, 6);  // Longitud con 6 decimales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodos');
    }
};
