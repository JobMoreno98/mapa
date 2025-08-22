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
        Schema::create('edges', function (Blueprint $table) {
            $table->id();  // ID único para cada relación
            $table->string('from_node');  // Nodo de origen
            $table->string('to_node');    // Nodo de destino
            $table->decimal('weight', 5, 2);  // Peso de la conexión
            
            $table->foreign('from_node')->references('nombre')->on('nodos')->onDelete('cascade');
            $table->foreign('to_node')->references('nombre')->on('nodos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('edges');
    }
};
