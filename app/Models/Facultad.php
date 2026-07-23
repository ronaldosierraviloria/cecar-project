<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facultad extends Model
{
    protected $table = 'facultad';
    protected $primaryKey = 'id_facultad';

    protected $fillable = [
        'nombre_facultad',
    ];

    public function areas()
    {
        return $this->hasMany(Area::class, 'id_facultad', 'id_facultad');
    }
}
