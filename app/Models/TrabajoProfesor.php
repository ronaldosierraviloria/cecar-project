<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class TrabajoProfesor extends Pivot
{
    protected $table = 'trabajo_profesor';

    protected $attributes = [
        'retroalimentacion_finalizada' => false,
    ];

    protected $fillable = [
        'id_trabajo',
        'id_profesor',
        'fecha_asignacion',
        'fecha_limite_revision',
        'estado_revision',
        'retroalimentacion_finalizada',
    ];

    public $incrementing = false;
    public $timestamps = true; // Esta tabla tiene timestamps por el withTimestamps()

    protected $dates = [
        'fecha_asignacion',
        'fecha_limite_revision',
    ];

    /*
     * Accessor para calcular si la revisión está vencida
     * Retorna true si la fecha actual es mayor a la fecha límite
     */
    protected function estaVencida(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->fecha_limite_revision) {
                return false;
            }

            return Carbon::now()->gt(Carbon::parse($this->fecha_limite_revision));
        });
    }

    /*
     * Accessor para ver días restantes
     */
    protected function diasRestantes(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->fecha_limite_revision) {
                return null;
            }

            $limite = Carbon::parse($this->fecha_limite_revision);
            return (int) round(Carbon::now()->diffInDays($limite, false));
        });
    }
}
