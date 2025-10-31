<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SecretaireRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $secretaireId = $this->route('secretaire'); // ID du secrétaire pour l'update

        $rules = [
            // Données utilisateur
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(
                    $secretaireId ? $this->getSecretaireUserId($secretaireId) : null
                )
            ],
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',

            // Données secrétaire
            'numero_employe' => 'nullable|string|max:50',
        ];

        // Validation différente selon la méthode (POST = création, PUT/PATCH = modification)
        if ($this->isMethod('post')) {
            // Pour la CRÉATION : mot de passe obligatoire, user_id NON requis (sera créé)
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['password_confirmation'] = 'required|string|min:8';
            // PAS de user_id requis car il sera créé automatiquement
        } else {
            // Pour la MODIFICATION : mot de passe optionnel
            $rules['password'] = 'nullable|string|min:8|confirmed';
            $rules['password_confirmation'] = 'nullable|string|min:8';
        }

        return $rules;
    }

    /**
     * Messages d'erreur personnalisés
     */
    public function messages(): array
    {
        return [
            // Validation nom
            'nom.required' => 'Le nom est obligatoire',
            'nom.string' => 'Le nom doit être une chaîne de caractères',
            'nom.max' => 'Le nom ne peut pas dépasser 100 caractères',

            // Validation prénom
            'prenom.required' => 'Le prénom est obligatoire',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères',
            'prenom.max' => 'Le prénom ne peut pas dépasser 100 caractères',

            // Validation email
            'email.required' => 'L\'email est obligatoire',
            'email.email' => 'L\'email doit être une adresse email valide',
            'email.unique' => 'Cet email est déjà utilisé',
            'email.max' => 'L\'email ne peut pas dépasser 255 caractères',

            // Validation téléphone
            'telephone.string' => 'Le téléphone doit être une chaîne de caractères',
            'telephone.max' => 'Le téléphone ne peut pas dépasser 20 caractères',

            // Validation adresse
            'adresse.string' => 'L\'adresse doit être une chaîne de caractères',
            'adresse.max' => 'L\'adresse ne peut pas dépasser 255 caractères',

            // Validation mot de passe
            'password.required' => 'Le mot de passe est obligatoire',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
            'password_confirmation.required' => 'La confirmation du mot de passe est obligatoire',

            // Validation numéro employé
            'numero_employe.string' => 'Le numéro d\'employé doit être une chaîne de caractères',
            'numero_employe.max' => 'Le numéro d\'employé ne peut pas dépasser 50 caractères',
        ];
    }

    /**
     * Récupérer l'user_id du secrétaire pour ignorer lors de la validation unique de l'email
     */
    private function getSecretaireUserId($secretaireId)
    {
        $secretaire = \App\Models\Secretaire::find($secretaireId);
        return $secretaire ? $secretaire->user_id : null;
    }
}
