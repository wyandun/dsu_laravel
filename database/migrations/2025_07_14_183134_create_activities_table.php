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
            $table->enum('tipo', ['Quipux', 'Mantis', 'CTIT', 'Correo', 'Otros']);
            $table->string('numero_referencia')->nullable();
            $table->decimal('tiempo', 5, 2); // 5 dígitos totales, 2 decimales
            $table->text('observaciones')->nullable();
            $table->date('fecha_actividad')->default(now()->toDateString());
            $table->timestamps();
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
