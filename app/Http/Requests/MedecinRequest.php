<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MedecinRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'numero_medecin' => 'required|string',
            'disponible' => 'boolean',
            'user_id' => 'required|exists:users,id',
            'specialite_id' => 'nullable|exists:specialites,id',
            'departement_id' => 'nullable|exists:departements,id',
        ];
    }

    public function messages(): array{
        return [
            'numero_medecin.required' => 'le numero medecin est obligatoire',
            'disponible.boolean' => 'le statut est obligatoire',
            'user_id.required' => 'L utilisateur est obligatoire',
            'specialite_id.exists' => 'La specialite est obligatoire',
            'departement_id.exists' => 'La departement est obligatoire',

        ];
    }



}
