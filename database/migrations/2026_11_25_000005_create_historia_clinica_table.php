<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historia_clinica', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('codigo_historia_clinica');
            $table->text('diagnostico');
            $table->date('fecha_hc');
            $table->text('observaciones')->nullable();
            $table->unsignedInteger('codigo_mascota');
            $table->foreign('codigo_mascota', 'fk_hc_mascota')
                ->references('codigo_mascota')->on('mascota')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historia_clinica');
    }
};
