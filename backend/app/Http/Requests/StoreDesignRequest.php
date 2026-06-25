<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDesignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_id' => ['required', 'exists:templates,id'],
            'format' => ['required', 'in:png,jpg'],
            // قيم الطبقات النصية والصورية تُتحقق منها ديناميكيًا في الـ Controller
            // حسب الطبقات المعرّفة لكل قالب (نص أو ملف صورة مرفوع)
            'values' => ['required', 'array'],
        ];
    }
}
