<?php

namespace App\Http\Requests\Backoffice;

use App\Models\BlogPost;
use App\Services\FaqPublicService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge([
                'slug' => trim((string) $this->input('slug', '')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'is_notice' => ['nullable', 'boolean'],
            'category' => ['required', 'string', Rule::in(array_keys(BlogPost::CATEGORIES))],
            'title' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blog_posts', 'slug'),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || $value === '') {
                        return;
                    }
                    $normalized = Str::slug($value);
                    if (in_array($normalized, BlogPost::reservedPostSlugs(), true)) {
                        $fail('이 URL 경로는 사용할 수 없습니다.');
                    }
                },
            ],
            'lead_content' => ['nullable', 'string'],
            'tags_input' => ['nullable', 'string', 'max:500'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'sections' => ['required', 'array', 'min:1', 'max:10'],
            'sections.*.subtitle' => ['nullable', 'string', 'max:255'],
            'sections.*.subheading' => ['nullable', 'string', 'max:255'],
            'sections.*.content' => ['nullable', 'string'],
            'faq_board_post_ids' => ['nullable', 'array', 'max:20'],
            'faq_board_post_ids.*' => ['integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v): void {
            $sections = $this->input('sections', []);
            $has = false;
            foreach ($sections as $section) {
                $subtitle = trim((string) ($section['subtitle'] ?? ''));
                $subheading = trim((string) ($section['subheading'] ?? ''));
                $content = trim((string) ($section['content'] ?? ''));
                if ($subtitle !== '' || $subheading !== '' || $content !== '') {
                    $has = true;
                    break;
                }
            }
            if (! $has) {
                $v->errors()->add('sections', '목차 제목 또는 본문을 1개 이상 입력해 주세요.');
            }

            $faqIds = $this->input('faq_board_post_ids');
            if (! is_array($faqIds) || $faqIds === []) {
                return;
            }

            $faqIds = array_map('intval', $faqIds);
            $faqIds = array_values(array_filter($faqIds, fn (int $id) => $id > 0));
            if ($faqIds === []) {
                return;
            }

            if (count($faqIds) !== count(array_unique($faqIds))) {
                $v->errors()->add('faq_board_post_ids', '같은 FAQ를 중복 선택할 수 없습니다.');

                return;
            }

            /** @var FaqPublicService $faqPublic */
            $faqPublic = app(FaqPublicService::class);
            $table = $faqPublic->boardTableName();
            if (! Schema::hasTable($table)) {
                $v->errors()->add('faq_board_post_ids', 'FAQ 게시판을 사용할 수 없습니다.');

                return;
            }

            $q = DB::table($table)->whereIn('id', $faqIds);
            if (Schema::hasColumn($table, 'deleted_at')) {
                $q->whereNull('deleted_at');
            }
            if (Schema::hasColumn($table, 'is_active')) {
                $q->where('is_active', true);
            }

            if ($q->count() !== count($faqIds)) {
                $v->errors()->add('faq_board_post_ids', '선택한 FAQ 중 일부가 존재하지 않거나 비활성입니다.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'category.required' => '카테고리를 선택해 주세요.',
            'title.required' => '제목을 입력해 주세요.',
            'sections.required' => '목차·본문 구간을 1개 이상 입력해 주세요.',
            'sections.max' => '목차·본문 구간은 최대 10개까지 가능합니다.',
            'thumbnail.max' => '썸네일 파일은 5MB 이하로 업로드해 주세요.',
            'slug.regex' => 'URL 슬러그는 영문 소문자, 숫자, 하이픈(-)만 사용할 수 있습니다. (한글·공백·특수문자·언더스코어(_)는 사용할 수 없습니다.)',
            'slug.unique' => '이미 사용 중인 URL 슬러그입니다. 다른 값을 입력해 주세요.',
        ];
    }
}
