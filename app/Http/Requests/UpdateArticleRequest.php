<?php

namespace App\Http\Requests;

use App\Models\Article;
use Illuminate\Foundation\Http\FormRequest;

class UpdateArticleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $article = Article::findOrFail($this->route('id'));
        return $this->user()->can('update', $article);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'status' => 'sometimes|in:draft,published',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The article title is required.',
            'title.max' => 'The article title cannot exceed 255 characters.',
            'content.required' => 'The article content is required.',
            'status.in' => 'The status must be either draft or published.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'article title',
            'content' => 'article content',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('status') && $this->status === 'published') {
                $user = $this->user();
                if (!$user->canPublishArticles()) {
                    $validator->errors()->add('status', 'You are not authorized to publish articles.');
                }
            }
        });
    }
}
