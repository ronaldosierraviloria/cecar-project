<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $table = 'estudiante';
    protected $primaryKey = 'id_estudiante';

    protected $fillable = [
        'id_trabajo',
        'nombre',
        'apellido',
        'correo',
        'id_area',
    ];
    public $timestamps = false;

    // Relación con trabajo
    public function trabajo()
    {
        return $this->belongsTo(Trabajo::class, 'id_trabajo');
    }

    // Relación con area
    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }
}
