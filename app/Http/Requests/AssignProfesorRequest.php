<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Trabajo;
use App\Models\Profesor;

class AssignProfesorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // luego puedes validar rol admin aquí
    }

    public function rules(): array
    {
        return [
            'id_trabajo' => 'required|exists:trabajo,id_trabajo',
            'profesores' => 'required|array|min:1|max:3',
            'profesores.*' => 'required|exists:profesor,id_profesor',
        ];
    }

    public function messages(): array
    {
        return [
            'id_trabajo.required' => 'Debe seleccionar un trabajo para asignar profesores.',
            'profesores.required' => 'Debe seleccionar al menos un profesor.',
            'profesores.max' => 'No puede asignar más de 3 profesores a un mismo trabajo.',
            'profesores.*.exists' => 'Uno de los profesores seleccionados no existe.',
        ];
    }

    /**
     * Validación adicional después de las reglas:
     * - Máximo 3 profesores por trabajo
     * - Máximo 3 trabajos asignados a cada profesor
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $idTrabajo = $this->id_trabajo;
            $profesoresSeleccionados = $this->profesores;

            $trabajo = Trabajo::with('profesores')->find($idTrabajo);

            // Validar que el trabajo no supere 3 profesores
            $yaAsignados = $trabajo->profesores->count();
            $nuevasAsignaciones = count($profesoresSeleccionados);

            if (($yaAsignados + $nuevasAsignaciones) > 3) {
                $validator->errors()->add('profesores', 'Este trabajo ya tiene profesores asignados. Máximo permitido: 3.');
            }

            // Validar que cada profesor no tenga más de 3 trabajos asignados
            foreach ($profesoresSeleccionados as $idProfesor) {
                $profesor = Profesor::withCount('trabajos')->find($idProfesor);

                if ($profesor->trabajos_count >= 3) {
                    $validator->errors()->add('profesores', "El profesor {$profesor->usuario->nombre} {$profesor->usuario->apellido} ya tiene 3 trabajos asignados.");
                }
            }
        });
    }
}

