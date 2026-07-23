<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Rubrica;

class StoreRubricaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Luego podemos validar que el usuario sea Gestor
        return true;
    }

    public function rules(): array
    {
        return [
            'id_trabajo' => 'required|exists:trabajo,id_trabajo',

            // Archivo PDF obligatorio
            'archivo_rubrica' => 'required|file|mimes:pdf|max:5120', // 5MB

            // Nombre opcional, tipo se detectará desde el controller
        ];
    }

    public function messages(): array
    {
        return [
            'id_trabajo.required' => 'Debe seleccionar el trabajo para asociar la rúbrica.',
            'id_trabajo.exists' => 'El trabajo seleccionado no existe.',

            'archivo_rubrica.required' => 'Debe adjuntar el archivo de la rúbrica.',
            'archivo_rubrica.mimes' => 'La rúbrica debe estar en formato PDF.',
            'archivo_rubrica.max' => 'El tamaño máximo permitido para la rúbrica es 5MB.',
        ];
    }

    /**
     * Valida que el trabajo no tenga ya una rúbrica, si aplicará la regla de 1 única rúbrica por trabajo.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $idTrabajo = $this->id_trabajo;

            // Si deseas evitar múltiples rúbricas por trabajo, descomenta esta validación:
            /*
            if (Rubrica::where('id_trabajo', $idTrabajo)->exists()) {
                $validator->errors()->add('id_trabajo', 'Este trabajo ya tiene una rúbrica registrada.');
            }
            */
        });
    }
}
