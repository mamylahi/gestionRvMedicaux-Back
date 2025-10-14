<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompteRenduRequest extends FormRequest
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
            'traitement' => 'required|string',
            'diagnostic' => 'required|string',
            'observation' => 'nullable|string',
            'date_creation' => 'required|date',
        ];
    }
    public function messages(): array{
        return [
            'consultation_id.required' => 'ID consultation est obligatoire',
            'traitement.required' => 'Le traitement est obligatoire',
            'diagnostic.required' => 'Le diagnostic est obligatoire',
            'observation.required' => ' observation est obligatoire',
            'date_creation.required' => 'Date de creation est obligatoire',
        ];
    }


}
