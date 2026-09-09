<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'integer', 'in:1,2,3'],
            'email' => ['required', 'email', 'max:255'],
            'tel' => ['required', 'regex:/^[0-9]{10,11}$/'],
            'address' => ['required', 'string', 'max:255'],
            'building' => ['nullable', 'string', 'max:255'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['exists:tags,id'],
            'detail' => ['required', 'string', 'max:120'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'お問い合わせの種類を選択してください',
            'category_id.exists' => '選択されたお問い合わせの種類が正しくありません',
            'first_name.required' => '姓を入力してください',
            'first_name.max' => '姓は255文字以内で入力してください',
            'last_name.required' => '名を入力してください',
            'last_name.max' => '名は255文字以内で入力してください',
            'gender.required' => '性別を選択してください',
            'gender.in' => '性別を正しく選択してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスの形式で入力してください',
            'email.max' => 'メールアドレスは255文字以内で入力してください',
            'tel.required' => '電話番号を入力してください',
            'tel.regex' => '電話番号はハイフンなしの半角数字10〜11桁で入力してください',
            'address.required' => '住所を入力してください',
            'address.max' => '住所は255文字以内で入力してください',
            'building.max' => '建物名は255文字以内で入力してください',
            'tag_ids.*.exists' => '選択されたタグが正しくありません',
            'detail.required' => 'お問い合わせ内容を入力してください',
            'detail.max' => 'お問い合わせ内容は120文字以内で入力してください',
        ];
    }
}
