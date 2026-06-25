<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $backgroundRule = $this->isMethod('post') ? 'required' : 'nullable';

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'background' => [$backgroundRule, 'image', 'max:10240'],
            'width' => ['required', 'integer', 'min:1', 'max:8000'],
            'height' => ['required', 'integer', 'min:1', 'max:8000'],
            'is_active' => ['boolean'],

            'layers' => ['required', 'array', 'min:1'],
            'layers.*.key' => ['required', 'string', 'max:100'],
            'layers.*.label' => ['required', 'string', 'max:255'],
            'layers.*.type' => ['required', 'in:text,image'],
            'layers.*.x' => ['required', 'integer'],
            'layers.*.y' => ['required', 'integer'],
            'layers.*.width' => ['required', 'integer', 'min:1'],
            'layers.*.height' => ['nullable', 'integer', 'min:1'],
            'layers.*.is_required' => ['boolean'],
            'layers.*.font_family' => ['nullable', 'string', 'max:100'],
            'layers.*.font_size' => ['nullable', 'integer', 'min:1'],
            'layers.*.font_weight' => ['nullable', 'integer', 'min:100', 'max:1000'],
            'layers.*.color' => ['nullable', 'string', 'max:20'],
            'layers.*.align' => ['nullable', 'in:right,left,center'],
            'layers.*.direction' => ['nullable', 'in:rtl,ltr'],
            'layers.*.line_height' => ['nullable', 'numeric'],
            'layers.*.letter_spacing' => ['nullable', 'numeric'],
            'layers.*.text_shadow' => ['nullable', 'string', 'max:255'],
            'layers.*.placeholder' => ['nullable', 'string'],
            'layers.*.border_radius' => ['nullable', 'integer', 'min:0'],
            'layers.*.object_fit' => ['nullable', 'in:cover,contain,fill'],
        ];
    }
}
