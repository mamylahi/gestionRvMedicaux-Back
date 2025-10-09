<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RendezVousRequest extends FormRequest
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
            'patient_id' => 'required',
            'medecin_id' => 'required',
            'date_rendezvous' => 'required',
            'heure_rendezvous' => 'required',
            'motif' => 'nullable|string|max:255',
            'statut' => 'in:en_attente,confirme,annule,termine',
        ];
    }
}
