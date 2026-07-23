<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CalificarTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Luego puedes validar si el usuario autenticado es profesor
        return true;
    }

    public function rules(): array
    {
        return [
            'id_rubrica' => 'required|exists:rubrica,id_rubrica',
            'id_profesor' => 'required|exists:profesor,id_profesor',

            'estado' => 'required|in:Borrador,Enviada',

            'puntaje_total' => 'nullable|numeric|min:0|max:100',
            'observacion_final' => 'nullable|string',
            'comentarios' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'id_rubrica.required' => 'Debe indicar la rúbrica que se está evaluando.',
            'id_profesor.required' => 'Debe indicar el profesor que realiza la evaluación.',
            'estado.in' => 'El estado de la calificación debe ser Borrador o Enviada.',
            'puntaje_total.numeric' => 'El puntaje debe ser un número.',
            'puntaje_total.max' => 'El puntaje máximo es 100.',
        ];
    }

    /**
     * Validaciones adicionales según el estado:
     * - Si la calificación se envía, debe tener puntaje y observación final obligatorios.
     */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $estado = $this->estado;

            if ($estado === 'Enviada') {

                if (is_null($this->puntaje_total)) {
                    $validator->errors()->add('puntaje_total', 'Debe asignar un puntaje total para enviar la calificación.');
                }

                if (empty($this->observacion_final)) {
                    $validator->errors()->add('observacion_final', 'Debe incluir una observación final para enviar la calificación.');
                }
            }
        });
    }
}
