<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrabajoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Puedes cambiar esto a true si solo usuarios autenticados lo usarán
        return true;
    }

    public function rules(): array
    {
        return [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_tipo' => 'required|exists:tipo_trabajo,id_tipo',
            'id_gestor' => 'required|exists:gestor,id_gestor',

            // Archivo PDF obligatorio
            'archivo_pdf' => 'required|file|mimes:pdf|max:5120', // 5MB

            // Estudiantes (máximo 3)
            'estudiantes' => 'required|array|min:1|max:3',
            'estudiantes.*' => 'exists:estudiante,id_estudiante',
        ];
    }

    public function messages(): array
    {
        return [
            'titulo.required' => 'El título del trabajo es obligatorio.',
            'id_tipo.required' => 'Debe seleccionar un tipo de trabajo.',
            'id_gestor.required' => 'Debe asociarse un gestor al trabajo.',
            'archivo_pdf.required' => 'Debe adjuntar el archivo PDF del trabajo.',
            'archivo_pdf.mimes' => 'El archivo debe ser formato PDF.',
            'archivo_pdf.max' => 'El tamaño máximo permitido para el PDF es 5MB.',
            'estudiantes.required' => 'Debe seleccionar al menos un estudiante.',
            'estudiantes.max' => 'Solo puede asociar máximo 3 estudiantes.',
        ];
    }
}
