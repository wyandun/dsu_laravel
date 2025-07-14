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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('empleado'); // empleado, jefe, administrador
            $table->string('tipo_jefe')->nullable(); // director, coordinador (solo para role=jefe)
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['direccion_id']);
            $table->dropColumn(['role', 'tipo_jefe', 'direccion_id']);
        });
    }
};
