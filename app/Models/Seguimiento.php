<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seguimiento extends Model
{
    protected $table = 'seguimiento';
    protected $primaryKey = 'id_seguimiento';

    protected $fillable = [
        'id_trabajo',
        'id_admin',
        'estado_visualizacion',
        'fecha_revision',
    ];

    public $timestamps = false; // La tabla no tiene created_at ni updated_at

    // Un seguimiento pertenece a un trabajo
    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo');
    }

    // Un seguimiento pertenece a un administrador (usuario)
    public function admin()
    {
        return $this->belongsTo(Usuario::class, 'id_admin');
    }
}
