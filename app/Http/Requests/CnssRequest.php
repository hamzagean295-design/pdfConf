<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CnssRequest extends FormRequest
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
            'patient' => 'required|string|max:255',
            'cin' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'sexe' => ['required', Rule::in(['F', 'H'])],
            'parente' => ['required', Rule::in(['Assuré', 'Enfant', 'Conjoint'])],
            'service_hospitalisation' => 'required|string|max:255',
            'inp' => 'required|string|max:255',
            'nature_hospitalisation' => 'required|string|max:255',
            'motif_hospitalisation' => 'required|string|max:255',
            'date_previsible_hospitalisation' => 'required|date',
            'date_en_urgence_le' => 'required|date',
            'nom_etablissement' => 'required|string|max:255',
            'code_etablissement' => 'required|string|max:255',
            'tel' => 'required|string|max:255',
            'total_estime' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'template_id' => ['required', 'integer', Rule::exists('documents', 'id')],
        ];
    }
}