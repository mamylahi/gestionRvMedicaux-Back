<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DossierMedicalRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
            'groupe_sanguin' => 'nullable|in:O-,O+,A-,A+,B-,B+,AB-,AB+',
            'date_creation' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'date_creation.required' => 'La date de creation est obligatoire',
            'patient_id.required' => 'Le patient est obligatoire',
            'groupe_sanguin.required' => 'Le groupe sanguin est obligatoire',
        ];
    }


}
