<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TipoTrabajo;
use App\Models\Rubrica;


class Trabajo extends Model
{
    use HasFactory;

    protected $table = 'trabajo';
    protected $primaryKey = 'id_trabajo';

    protected $fillable = [
        'titulo',
        'fecha_subida',
        'id_tipo',
        'plantilla_rubrica',
        'archivo_pdf',
        'version_actual',
        'estado',
        'retirado',
    ];

    protected $casts = [
        'retirado' => 'boolean',
    ];

    public $timestamps = true;
    // Relación con tipo de trabajo
    public function tipo()
    {
        return $this->belongsTo(TipoTrabajo::class, 'id_tipo', 'id_tipo');
    }
    // Relación con estudiantes
    public function estudiante()
    {
        return $this->hasMany(Estudiante::class, 'id_trabajo');
    }
    public function evaluadores()
    {
    // 1. Modelo relacionado: Usuario::class
    // 2. Tabla pivote: 'trabajo_profesor'
    // 3. FK de la tabla local en el pivote: 'id_trabajo' (PK del Trabajo)
    // 4. FK del modelo relacionado en el pivote: 'id_profesor' (PK del Usuario es 'id_usuario')
    return $this->belongsToMany(\App\Models\Profesor::class, 'trabajo_profesor', 'id_trabajo', 'id_profesor')
                 // MUY IMPORTANTE: Los nombres de columna deben coincidir exactamente con la base de datos.
                 ->withPivot('fecha_asignacion', 'fecha_limite_revision', 'estado_revision', 'decision_evaluador', 'motivo_rechazo');
    }
    public function rubricas()
    {
    return $this->belongsToMany(Rubrica::class, 'trabajo_rubrica', 'id_trabajo', 'id_rubrica')
                ->withPivot('fecha_asignacion');    
    }   
    public function rubricaAsignada()
    {
    return $this->hasOne(TrabajoRubrica::class, 'id_trabajo');
    }

    public function retroalimentaciones()
    {
        return $this->hasMany(Retroalimentacion::class, 'trabajo_grado_id', 'id_trabajo');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstado::class, 'trabajo_grado_id', 'id_trabajo');
    }

    public function directores()
    {
        return $this->belongsToMany(Director::class, 'director_trabajo', 'id_trabajo', 'id_director');
    }

    public function evaluaciones()
    {
        return $this->hasMany(Evaluacion::class, 'id_trabajo');
    }
}

