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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titulo');
            $table->string('tipo'); // Quipux, Mantis, CTIT, Correo, Otros
            $table->string('numero_referencia')->nullable();
            $table->decimal('tiempo', 5, 2); // Tiempo en horas con 2 decimales
            $table->text('observaciones')->nullable();
            $table->date('fecha_actividad');
            $table->timestamps();

            // Índices para mejorar performance en consultas frecuentes
            $table->index(['user_id', 'fecha_actividad']);
            $table->index(['tipo', 'numero_referencia']);
            $table->index('fecha_actividad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
