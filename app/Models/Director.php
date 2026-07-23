<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Director extends Model
{
    use HasFactory;

    protected $table = 'directors';
    protected $primaryKey = 'id_director';

    protected $fillable = [
        'nombre',
        'apellido',
        'correo_electronico',
    ];

    public function trabajos()
    {
        return $this->belongsToMany(Trabajo::class, 'director_trabajo', 'id_director', 'id_trabajo');
    }
}
