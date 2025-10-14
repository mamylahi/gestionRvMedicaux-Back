<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
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
            'numero_patient' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ];
    }



    public function messages(): array{
        return [
            'numero_patient.required' => 'ID patient est obligatoire',
            'user_id.required' => 'l ID utilisateur est obligatoire',
        ];
    }

}
