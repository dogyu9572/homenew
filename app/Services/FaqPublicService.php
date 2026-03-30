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
    private function tableName(): string
    {
        $slug = (string) config('faq_public.board_slug', 'faq');

        return 'board_'.$slug;
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
}
