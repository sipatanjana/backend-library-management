<?php

namespace App\Http\Requests;

use App\Models\Author;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookRequest extends FormRequest
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
            'author_id' => ['required', 'integer', Rule::exists(Author::class, 'id')],
            'title' => [
                'required',
                'max:255',
                Rule::unique('books', 'title')->where(
                    fn($query) => $query->where('author_id', request()->author_id)
                )
            ],
            'description' => ['required', 'string'],
            'publish_date' => ['required', 'date'],
        ];
    }
}
