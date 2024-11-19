<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
            'author_id' => ['integer'],
            'title' => [
                'max:255',
                Rule::unique('books', 'title')->where(
                    fn($query) => $query->where([['author_id', request()->author_id], ['id', '!=', request()->id]])
                )
            ],
            'description' => ['string'],
            'publish_date' => ['date'],
        ];
    }
}
