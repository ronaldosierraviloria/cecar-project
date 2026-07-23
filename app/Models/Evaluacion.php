<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluacion extends Model
{
    protected $table = 'evaluaciones';

    protected $fillable = [
        'id_trabajo',
        'id_profesor',
        'tipo_plantilla',
        'nota_final',
        'resultado',
        'evaluacion_completada',
        'observaciones_globales',
        'criterios',
        'firma',
        'firma_evaluador_2',
    ];

    protected $casts = [
        'criterios' => 'array',
        'nota_final' => 'decimal:2',
        'evaluacion_completada' => 'boolean',
    ];

    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'id_profesor');
    }

    /**
     * Obtener el primer evaluador asignado al trabajo
     */
    public function getPrimerEvaluadorAttribute()
    {
        return $this->trabajo->evaluadores()->orderBy('trabajo_profesor.fecha_asignacion')->first();
    }

    /**
     * Obtener el segundo evaluador asignado al trabajo
     */
    public function getSegundoEvaluadorAttribute()
    {
        return $this->trabajo->evaluadores()->orderBy('trabajo_profesor.fecha_asignacion')->skip(1)->first();
    }
}
