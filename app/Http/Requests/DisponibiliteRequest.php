<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DisponibiliteRequest extends FormRequest
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
            'medecin_id' => 'required',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after:date_debut',
            'recurrent' => 'boolean',
        ];
    }
    public function messages(): array
    {
        return [
            'medecin_id.required' => 'Le medecin est obligatoire',
            'date_debut.required' => 'La date de debut est obligatoire',
            'date_fin.date' => 'La date de fin est obligatoire',
            'recurrent.boolean' => 'Le reccurent est obligatoire',
        ];
    }



}
