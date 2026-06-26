<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ImportPsdTemplatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxKilobytes = (int) config('psd.max_upload_kb', 524288);

        return [
            'psd_files' => ['required', 'array', 'min:1'],
            'psd_files.*' => ['required', 'file', "max:{$maxKilobytes}"],
            'description' => ['nullable', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            foreach ($this->file('psd_files', []) as $index => $file) {
                $extension = strtolower($file->getClientOriginalExtension());

                if ($extension !== 'psd') {
                    $validator->errors()->add("psd_files.{$index}", 'يجب أن يكون الملف بصيغة PSD.');
                }
            }
        });
    }
}
