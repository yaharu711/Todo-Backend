<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegistRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:30'],
            'email' => ['required', 'email'], // デフォルトではRFCに沿ったメールアドレスかどうか検証される
            // https://laravel.com/docs/9.x/validation#rule-email
            'password' => ['required', 'confirmed'], // confirmedを追加すると、password_confirmationプロパティもリクエストする必要がある
        ];
    }

    // こうしないと、web.phpの場合はリクエストエラーになるとルートパス（/）ににリダイレクトされてしまう！
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));   
    }
}
