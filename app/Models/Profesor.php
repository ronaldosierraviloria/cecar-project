<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    protected $table = 'profesor';
    protected $primaryKey = 'id_profesor';

    protected $fillable = [
        'id_usuario',
        'id_area',
        'terminos_aceptados',
        'datos_aceptados',
    ];

    protected $casts = [
        'terminos_aceptados' => 'boolean',
        'datos_aceptados' => 'boolean',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Relación: Un profesor pertenece a un Área.
     * Uso: $profesor->area->nombre_area
     */
    public function area()
    {
        // belongsTo(Modelo_Relacionado, Clave_Foránea_Local, Clave_Primaria_Tabla_Relacionada)
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    // Un profesor puede tener máximo 3 trabajos asignados (validaremos en lógica)
    public function trabajos()
    {
        return $this->belongsToMany(Trabajo::class, 'trabajo_profesor', 'id_profesor', 'id_trabajo')
                     ->withPivot(['fecha_asignacion', 'fecha_limite_revision', 'estado_revision', 'decision_evaluador', 'motivo_rechazo']);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class, 'id_profesor');
    }
}