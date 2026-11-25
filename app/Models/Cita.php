<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    protected $table = 'citas';
    protected $primaryKey = 'id_citas';

    protected $fillable = [
        'fecha_cita',
        'categoria',
        'descripcion',
        'confirmacion',
        'id_mascota',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cita' => 'datetime',
            'confirmacion' => 'boolean',
        ];
    }

    public function mascota()
    {
        return $this->belongsTo(Mascota::class, 'id_mascota', 'id_mascota');
    }

    public function veterinarios()
    {
        return $this->belongsToMany(
            Veterinario::class,
            'cita_veterinario',
            'id_citas',
            'id_veterinario'
        );
    }
}
