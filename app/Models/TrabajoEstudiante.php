<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TrabajoEstudiante extends Pivot
{
    protected $table = 'trabajo_estudiante';

    protected $fillable = [
        'id_trabajo',
        'id_estudiante',
    ];

    public $incrementing = false;
    public $timestamps = false; // Esta tabla no usa timestamps
}
