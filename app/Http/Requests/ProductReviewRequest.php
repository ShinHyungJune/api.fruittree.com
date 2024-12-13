<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductReviewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            //'user_id' => ['required', 'exists:users,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['required', 'string'],
            'images' => ['nullable', 'array'],
        ];
    }

    public function bodyParameters()
    {
        return [
            'product_id' => ['description' => '<span class="point">참조 상품 ID</span>'],
            'user_id' => ['description' => '<span class="point">후기 작성 사용자 ID</span>'],
            'rating' => ['description' => '<span class="point">평점 (1~5)</span>'],
            'review' => ['description' => '<span class="point">후기 내용</span>'],
        ];
    }

}
