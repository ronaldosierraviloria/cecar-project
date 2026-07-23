<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'area';
    protected $primaryKey = 'id_area';
    // Por defecto, Laravel asume que la PK es 'id'. Al poner 'id_area' aquí, le indicamos el nombre real.

    protected $fillable = [
        'nombre_area',
        'id_facultad', // Relación con facultad
    ];
    
    // Si usaste created_at/updated_at en la query SQL (TIMESTAMP), mantén esta línea (por defecto es true)
    // Si no los usaste, debes añadir: public $timestamps = false;

    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad', 'id_facultad');
    }

    public function profesores()
    {
        return $this->hasMany(Profesor::class, 'id_area', 'id_area');
    }
        public function rubricas()
    {
        return $this->hasMany(Rubrica::class, 'id_tipo', 'id_tipo');
    }


}