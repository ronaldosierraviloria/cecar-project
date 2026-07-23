<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alerta';
    protected $primaryKey = 'id_alerta';

    protected $fillable = [
        'id_trabajo_profesor',
        'fecha_envio',
        'tipo_alerta',
        'leido',
    ];

    public $timestamps = false; // La tabla no usa timestamps

    // Relación con trabajo_profesor (pivot)
    public function trabajoProfesor()
    {
        return $this->belongsTo(TrabajoProfesor::class, 'id_trabajo_profesor');
    }
}
