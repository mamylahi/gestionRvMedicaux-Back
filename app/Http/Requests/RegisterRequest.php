<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
        $rules = [
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,medecin,secretaire,patient',
        ];

        // Si le rôle est médecin, la spécialité est obligatoire
        if ($this->input('role') === 'medecin') {
            $rules['specialite_id'] = 'required|exists:specialites,id';
            $rules['disponible'] = 'nullable|boolean';
        }

        return $rules;

    }




    public function messages(): array{
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prenom est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'telephone.required' => 'Le telephone est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'role.required' => 'Le role est obligatoire.',
            'specialite_id.required' => 'La spécialité est obligatoire pour un médecin.',
            'specialite_id.exists' => 'La spécialité sélectionnée n\'existe pas.',
            'disponible.boolean' => 'Le statut de disponibilité doit être vrai ou faux.',
        ];

    }


}
