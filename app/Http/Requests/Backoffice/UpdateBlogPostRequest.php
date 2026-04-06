<?php

namespace App\Http\Requests\Backoffice;

use App\Models\BlogPost;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateBlogPostRequest extends StoreBlogPostRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['remove_thumbnail'] = ['nullable', 'boolean'];
        $rules['slug'] = [
            'nullable',
            'string',
            'max:255',
            'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
            Rule::unique('blog_posts', 'slug')->ignore($this->route('blog_post')),
            function (string $attribute, mixed $value, \Closure $fail): void {
                if (! is_string($value) || $value === '') {
                    return;
                }
                $normalized = Str::slug($value);
                if (in_array($normalized, BlogPost::reservedPostSlugs(), true)) {
                    $fail('이 URL 경로는 사용할 수 없습니다.');
                }
            },
        ];

        return $rules;
    }
}
