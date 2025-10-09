<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaiementRequest extends FormRequest
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
            'consultation_id' => 'required|exists:consultations,id',
            'montant' => 'required|numeric|min:0',
            'date_paiement' => 'required|date',
            'moyen_paiement' => 'required|in:espece,carte,mobile_money',
            'statut' => 'in:en_attente,valide,annule',
        ];
    }
}
