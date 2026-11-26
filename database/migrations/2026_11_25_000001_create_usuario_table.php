<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('codigo_usuario');
            $table->string('nombres', 80);
            $table->string('apellidos', 80);
            $table->string('email', 120);
            $table->string('numero_contacto', 20);
            $table->string('numero_documento', 20);
            $table->string('user', 50);
            $table->string('password', 255);
            $table->string('rol', 30);
            $table->unique('email', 'uq_usuario_email');
            $table->unique('numero_contacto', 'uq_usuario_contacto');
            $table->unique('numero_documento', 'uq_usuario_documento');
            $table->unique('user', 'uq_usuario_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
