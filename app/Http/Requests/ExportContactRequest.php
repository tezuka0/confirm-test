<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportContactRequest extends FormRequest
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
            //
            'keyword' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'integer', 'in:0,1,2,3'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'gender.in' => '性別の指定が正しくありません',
            'category_id.exists' => '選択されたお問い合わせの種類が正しくありません',
            'date.date' => '日付の形式が正しくありません',
        ];
    }
}
