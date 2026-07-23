<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'historial_estados';

    protected $fillable = [
        'trabajo_grado_id',
        'estado',
        'version_documento',
        'user_id',
        'observacion_estado',
    ];

    /**
     * Relación con el Trabajo de Grado.
     */
    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'trabajo_grado_id', 'id_trabajo');
    }

    /**
     * Relación con el Usuario (Gestor/Evaluador).
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id', 'id_usuario');
    }
}
