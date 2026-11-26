<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_cita', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->unsignedInteger('codigo_usuario');
            $table->unsignedInteger('codigo_cita');
            $table->primary(['codigo_usuario', 'codigo_cita']);
            $table->foreign('codigo_usuario', 'fk_uc_usuario')
                ->references('codigo_usuario')->on('usuario')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('codigo_cita', 'fk_uc_cita')
                ->references('codigo_cita')->on('cita')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_cita');
    }
};
