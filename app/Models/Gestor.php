<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gestor extends Model
{
    protected $table = 'gestor';
    protected $primaryKey = 'id_gestor';

    protected $fillable = [
        'id_usuario',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    // Un Gestor puede crear varios trabajos
    public function trabajos()
    {
        return $this->hasMany(Trabajo::class, 'id_gestor');
    }
}
