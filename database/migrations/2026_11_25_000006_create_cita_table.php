<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cita', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('codigo_cita');
            $table->string('tipo_cita', 50);
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha');
            $table->boolean('confirmacion')->default(false);
            $table->unsignedInteger('codigo_veterinarias');
            $table->foreign('codigo_veterinarias', 'fk_cita_veterinarias')
                ->references('codigo_veterinarias')->on('veterinarias')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita');
    }
};
