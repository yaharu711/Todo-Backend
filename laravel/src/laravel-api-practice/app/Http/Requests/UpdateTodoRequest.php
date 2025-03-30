<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTodoRequest extends FormRequest
{
    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'min:1', 'max:100'],
            'memo' => ['nullable', 'string', 'max:3000'],
            'is_completed' => ['boolean'],
        ];
    }

    protected function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // リクエスト全体にカスタムバリデーションを適用
            $data = $this->all();

            if (!isset($data['name']) && !isset($data['is_completed']) && !isset($data['memo'])) {
                $validator->errors()->add('request_body', "リクエストボディには少なくとも 'name' または 'is_completed'、'memo' のいずれかが必要です。");
            }
        });
    }
}
