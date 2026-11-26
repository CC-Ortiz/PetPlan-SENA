<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mascota', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('codigo_mascota');
            $table->string('nombre_mascota', 60);
            $table->string('especie', 60)->nullable();
            $table->unsignedTinyInteger('edad')->nullable();
            $table->decimal('peso', 5, 2)->nullable();
            $table->unsignedInteger('codigo_usuario');
            $table->foreign('codigo_usuario', 'fk_mascota_usuario')
                ->references('codigo_usuario')->on('usuario')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mascota');
    }
};
