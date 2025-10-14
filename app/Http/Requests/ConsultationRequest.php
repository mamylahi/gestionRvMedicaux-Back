<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConsultationRequest extends FormRequest
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
            'rendezvous_id' => 'required',
            'date_consultation' => 'required|date',
        ];
    }

    public function messages(): array{
        return [
            'rendezvous_id.required' => 'ID rendezvous est obligatoire',
            'date_consultation.required' => 'Date de consultation est obligatoire',
        ];
    }


}
