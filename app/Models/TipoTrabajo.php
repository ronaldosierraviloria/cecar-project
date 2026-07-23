<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoTrabajo extends Model
{
    protected $table = 'tipo_trabajo';
    protected $primaryKey = 'id_tipo';

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $fillable = [
        'nombre_tipo',
        'activo',
    ];

    // Un tipo de trabajo puede tener muchos trabajos
    public function trabajos()
    {
        return $this->hasMany(Trabajo::class, 'id_tipo');
    }

    public function rubrica()
    {
        return $this->hasMany(Rubrica::class, 'id_tipo', 'id_tipo');
    }
    
}
