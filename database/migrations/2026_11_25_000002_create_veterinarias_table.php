<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veterinarias', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('codigo_veterinarias');
            $table->string('nit', 20);
            $table->string('nombre', 100);
            $table->string('contacto', 20);
            $table->string('estado', 20)->default('Pendiente');
            $table->date('fecha_aprobacion')->nullable();
            $table->unique('nit', 'uq_veterinarias_nit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterinarias');
    }
};
