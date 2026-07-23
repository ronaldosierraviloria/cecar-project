<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstadoRevisionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Más adelante puedes validar que el usuario sea Administrador
        return true;
    }

    public function rules(): array
    {
        return [
            'id_trabajo' => 'required|exists:trabajo,id_trabajo',
            'id_profesor' => 'required|exists:profesor,id_profesor',

            // Estados válidos según ENUM
            'estado_revision' => 'required|in:Pendiente,En revisión,Finalizado',
        ];
    }

    public function messages(): array
    {
        return [
            'id_trabajo.required' => 'Debe seleccionar el trabajo.',
            'id_profesor.required' => 'Debe seleccionar el profesor asignado.',
            'estado_revision.required' => 'Debe seleccionar un estado de revisión.',
            'estado_revision.in' => 'El estado de revisión no es válido.',
        ];
    }
}
