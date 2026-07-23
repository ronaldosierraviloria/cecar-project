<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubrica extends Model
{
    use HasFactory;

    protected $table = 'rubrica';
    protected $primaryKey = 'id_rubrica';
    public $timestamps = false;

    protected $fillable = [
        'id_tipo',
        'archivo',
        'mime_type',
        'activo',
        'fecha_creacion',
    ];

    /**
     * Relación correcta: cada rúbrica pertenece a un tipo de trabajo
     */
    public function tipo()
    {
        return $this->belongsTo(TipoTrabajo::class, 'id_tipo', 'id_tipo');
    }

    /**
     * Relación con trabajos usando tabla pivote trabajo_rubrica
     */
    public function trabajos()
    {
    return $this->belongsToMany(Trabajo::class, 'trabajo_rubrica', 'id_rubrica', 'id_trabajo')
                ->withPivot('fecha_asignacion');
    }

    
}
