<?php

namespace App\Http\Requests\Backoffice;

use App\Models\BlogPost;
use App\Services\FaqPublicService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_notice' => ['nullable', 'boolean'],
            'category' => ['required', 'string', Rule::in(array_keys(BlogPost::CATEGORIES))],
            'title' => ['required', 'string', 'max:255'],
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
        ];
    }
}
