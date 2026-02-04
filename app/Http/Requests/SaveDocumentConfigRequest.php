<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveDocumentConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $config = $this->input('config', []);
        if (isset($config['elements']) && is_array($config['elements'])) {
            $elements = $config['elements'];
            foreach ($elements as $key => $element) {
                // If color is a valid hex string, convert it to an RGB array before validation.
                if (isset($element['color']) && is_string($element['color']) && preg_match('/^#([0-9a-fA-F]{3}){1,2}$/', $element['color'])) {
                    $hex = ltrim($element['color'], '#');
                    if (strlen($hex) == 3) {
                        $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
                    }
                    $elements[$key]['color'] = [
                        hexdec(substr($hex, 0, 2)),
                        hexdec(substr($hex, 2, 2)),
                        hexdec(substr($hex, 4, 2)),
                    ];
                }
            }
            $config['elements'] = $elements;
            // Replace the request's config with the normalized one.
            $this->merge(['config' => $config]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'config' => ['required', 'array'],
            'config.elements' => ['nullable', 'array'],
            'config.elements.*.type' => ['required', 'string', 'in:text,tag,image,checkbox'],
            'config.elements.*.label' => ['required', 'string', 'max:255'],
            'config.elements.*.page' => ['required', 'integer', 'min:1'],
            'config.elements.*.value' => ['required', 'string'],
            'config.elements.*.valueTest' => ['nullable', 'string'],

            // X and Y are not required for the checkbox group container itself.
            'config.elements.*.x' => ['required_unless:config.elements.*.type,checkbox', 'nullable', 'numeric'],
            'config.elements.*.y' => ['required_unless:config.elements.*.type,checkbox', 'nullable', 'numeric'],

            'config.elements.*.font_family' => ['nullable', 'string'],
            'config.elements.*.font_style' => ['nullable', 'string'],
            'config.elements.*.font_size' => ['nullable', 'numeric'],
            'config.elements.*.font_weight' => ['nullable', 'string'],

            // Color is now expected to be an array of 3 integers.
            'config.elements.*.color' => ['nullable', 'array'],
            'config.elements.*.color.0' => ['required_with:config.elements.*.color', 'integer', 'min:0', 'max:255'], // R
            'config.elements.*.color.1' => ['required_with:config.elements.*.color', 'integer', 'min:0', 'max:255'], // G
            'config.elements.*.color.2' => ['required_with:config.elements.*.color', 'integer', 'min:0', 'max:255'], // B

            // Validation for checkbox options.
            'config.elements.*.options' => ['required_if:config.elements.*.type,checkbox', 'nullable', 'array'],
            'config.elements.*.options.*.label' => ['sometimes', 'required', 'string'],
            'config.elements.*.options.*.value' => ['sometimes', 'required', 'string'],
            'config.elements.*.options.*.x' => ['sometimes', 'required', 'numeric'],
            'config.elements.*.options.*.y' => ['sometimes', 'required', 'numeric'],
        ];
    }
}
