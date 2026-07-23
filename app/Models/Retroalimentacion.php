<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Retroalimentacion extends Model
{
    use HasFactory;

    protected $table = 'retroalimentaciones';

    protected $fillable = [
        'trabajo_grado_id',
        'user_id',
        'comentario',
        'version_documento',
    ];

    /**
     * Relación con el Trabajo de Grado.
     */
    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'trabajo_grado_id', 'id_trabajo');
    }

    /**
     * Relación con el Usuario (Evaluador).
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'user_id', 'id_usuario');
    }
}
