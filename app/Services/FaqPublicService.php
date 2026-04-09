<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 백오피스 FAQ 게시판(board_faq) 공개 조회
 */
class FaqPublicService
{
    /**
     * FAQ 게시판 DB 테이블명 (예: board_faq)
     */
    public function boardTableName(): string
    {
        $slug = (string) config('faq_public.board_slug', 'faq');

        return 'board_'.$slug;
    }

    private function tableName(): string
    {
        return $this->boardTableName();
    }

    /**
     * 백오피스 블로그 폼용: 노출 가능한 FAQ 목록 (id, title)
     */
    public function listForBackofficePicker(int $limit = 500): Collection
    {
        $table = $this->tableName();
        if (! Schema::hasTable($table)) {
            return collect();
        }
        if ($limit < 1) {
            $limit = 500;
        }

        $query = DB::table($table);
        $this->applyPublicVisibilityConstraints($query);

        if (Schema::hasColumn($table, 'is_notice')) {
            $query->orderByDesc('is_notice');
        }
        if (Schema::hasColumn($table, 'sort_order')) {
            $query->orderByDesc('sort_order');
        }
        $query->orderByDesc('id');

        return $query->limit($limit)->get(['id', 'title']);
    }

    /**
     * 블로그에 저장된 id 순서대로 FAQ 본문 조회 (비활성·삭제된 항목은 제외)
     *
     * @param  list<int>|null  $ids
     */
    public function forIdsOrdered(?array $ids): Collection
    {
        if ($ids === null || $ids === []) {
            return collect();
        }

        $normalized = [];
        foreach ($ids as $id) {
            $n = (int) $id;
            if ($n > 0 && ! in_array($n, $normalized, true)) {
                $normalized[] = $n;
            }
        }
        if ($normalized === []) {
            return collect();
        }

        $table = $this->tableName();
        if (! Schema::hasTable($table)) {
            return collect();
        }

        $query = DB::table($table)->whereIn('id', $normalized);
        $this->applyPublicVisibilityConstraints($query);

        $rows = $query->get(['id', 'title', 'content', 'category']);
        $byId = $rows->keyBy('id');

        $ordered = collect();
        foreach ($normalized as $id) {
            if ($byId->has($id)) {
                $ordered->push($byId->get($id));
            }
        }

        return $ordered;
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    private function applyPublicVisibilityConstraints($query): void
    {
        $table = $this->tableName();

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }
    }

    /**
     * 메인 홈용 FAQ (카테고리: home_category)
     */
    public function forHome(): Collection
    {
        $category = (string) config('faq_public.home_category', '메인');

        return $this->queryByCategories([$category]);
    }

    /**
     * 서비스 하위 페이지용 FAQ ($sName 및 별칭과 category 매칭)
     */
    public function forServiceMenuLabel(string $sName): Collection
    {
        $aliases = config('faq_public.category_aliases.'.$sName);
        if (is_array($aliases) && $aliases !== []) {
            $categories = array_values(array_unique(array_filter(array_map('strval', $aliases))));

            return $this->queryByCategories($categories);
        }

        return $this->queryByCategories([$sName]);
    }

    /**
     * @param  list<string>  $categories
     */
    private function queryByCategories(array $categories): Collection
    {
        $table = $this->tableName();

        if ($categories === [] || ! Schema::hasTable($table)) {
            return collect();
        }

        $query = DB::table($table)->whereIn('category', $categories);

        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (Schema::hasColumn($table, 'is_active')) {
            $query->where('is_active', true);
        }

        $limit = (int) config('faq_public.limit', 50);
        if ($limit < 1) {
            $limit = 50;
        }

        if (Schema::hasColumn($table, 'is_notice')) {
            $query->orderByDesc('is_notice');
        }
        if (Schema::hasColumn($table, 'sort_order')) {
            $query->orderByDesc('sort_order');
        }
        $query->orderByDesc('created_at');

        $rows = $query
            ->limit($limit)
            ->get(['id', 'title', 'content', 'category']);

        return collect($rows);
    }

    /**
     * 서비스 페이지 layout JSON-LD(WebPage)용 mainEntity — Schema.org Question 배열
     *
     * @return list<array<string, mixed>>
     */
    public function mainEntityForServicePageJsonLd(Collection $faqItems): array
    {
        $out = [];
        foreach ($faqItems as $row) {
            $name = trim((string) ($row->title ?? ''));
            $text = $this->plainTextForJsonLd(isset($row->content) ? (string) $row->content : null);
            if ($name === '' && $text === '') {
                continue;
            }
            $out[] = [
                '@type' => 'Question',
                'name' => $name,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $text,
                ],
            ];
        }

        return $out;
    }

    /**
     * FAQ 본문 HTML → JSON-LD acceptedAnswer.text용 평문
     */
    public function plainTextForJsonLd(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }
        $normalized = preg_replace('/>\s*</', '> <', $html);
        $text = strip_tags($normalized);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }
}
