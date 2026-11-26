<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('veterinario', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('codigo_veterinario');
            $table->string('nombres_vet', 80);
            $table->string('apellidos_vet', 80);
            $table->string('num_contacto_vet', 20);
            $table->string('email_vet', 120);
            $table->string('num_licencia', 30);
            $table->unsignedInteger('codigo_usuario');
            $table->unsignedInteger('codigo_veterinarias');
            $table->unique('num_contacto_vet', 'uq_vet_contacto');
            $table->unique('email_vet', 'uq_vet_email');
            $table->unique('num_licencia', 'uq_vet_licencia');
            $table->unique('codigo_usuario', 'uq_vet_usuario');
            $table->foreign('codigo_usuario', 'fk_vet_usuario')
                ->references('codigo_usuario')->on('usuario')
                ->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('codigo_veterinarias', 'fk_vet_veterinarias')
                ->references('codigo_veterinarias')->on('veterinarias')
                ->onUpdate('cascade')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('veterinario');
    }
};
