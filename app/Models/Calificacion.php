<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificacion';
    protected $primaryKey = 'id_calificacion';

    protected $fillable = [
        'id_rubrica',
        'id_profesor',
        'puntaje_total',
        'observacion_final',
        'comentarios',
        'estado',
        'fecha_calificacion',
    ];

    public $timestamps = false; // La tabla usa fecha_calificacion, no timestamps

    // Una calificación pertenece a una rúbrica
    public function rubrica()
    {
        return $this->belongsTo(Rubrica::class, 'id_rubrica');
    }

    // Una calificación pertenece a un profesor
    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor');
    }
}
