<?php

namespace App\Services\Backoffice;

use App\Models\Portfolio;
use App\Support\PublicUrlSegments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PortfolioService
{
    public function getPortfolios(Request $request)
    {
        $query = Portfolio::query()->with(['featureDevelopments', 'reviews']);

        if ($request->filled('category')) {
            $query->whereJsonContains('categories', $request->category);
        }
        if ($request->filled('keyword')) {
            $query->where('title', 'like', '%'.$request->keyword.'%');
        }

        $perPage = (int) $request->get('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100, 500, 1000], true)) {
            $perPage = 10;
        }

        $this->applyBackofficePortfolioListOrdering($query);

        return $query->paginate($perPage);
    }

    /**
     * 백오피스 목록·드래그 정렬 병합에서 동일하게 사용: 노출 먼저, 그다음 sort_order·id
     */
    private function applyBackofficePortfolioListOrdering(Builder $query): void
    {
        $query->orderByDesc('is_active')
            ->orderByDesc('sort_order')
            ->orderByDesc('id');
    }

    public function create(array $data): Portfolio
    {
        return DB::transaction(function () use ($data) {
            if (! isset($data['sort_order']) || $data['sort_order'] === '' || $data['sort_order'] === null) {
                $data['sort_order'] = ((int) Portfolio::query()->max('sort_order')) + 1;
            }

            $data['slug'] = $this->resolveSlugForSave($data['slug'] ?? null, (string) $data['title'], null);

            $portfolio = Portfolio::create($this->mapPortfolioData($data));
            $this->syncReviews($portfolio, $data['reviews'] ?? []);
            $this->syncFeatureDevelopments($portfolio, $data['feature_developments'] ?? []);

            return $portfolio;
        });
    }

    public function update(Portfolio $portfolio, array $data): Portfolio
    {
        return DB::transaction(function () use ($portfolio, $data) {
            $data['slug'] = $this->resolveSlugForSave($data['slug'] ?? null, (string) $data['title'], $portfolio);

            $portfolio->update($this->mapPortfolioData($data));
            $this->syncReviews($portfolio, $data['reviews'] ?? []);
            $this->syncFeatureDevelopments($portfolio, $data['feature_developments'] ?? []);

            return $portfolio->fresh(['featureDevelopments', 'reviews']);
        });
    }

    public function delete(Portfolio $portfolio): void
    {
        foreach ([$portfolio->thumbnail_image, $portfolio->top_image, $portfolio->solution_before_image, $portfolio->solution_after_image] as $file) {
            if ($file) {
                Storage::disk('public')->delete($file);
            }
        }
        foreach ($portfolio->featureImages as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        foreach ($portfolio->featureDevelopments as $featureDevelopment) {
            if ($featureDevelopment->image_path) {
                Storage::disk('public')->delete($featureDevelopment->image_path);
            }
        }
        $portfolio->delete();
    }

    public function updateOrder(array $payload): void
    {
        DB::transaction(function () use ($payload) {
            foreach ($payload as $row) {
                if (! isset($row['id'], $row['order'])) {
                    continue;
                }
                Portfolio::where('id', $row['id'])->update(['sort_order' => (int) $row['order']]);
            }
        });
    }

    /**
     * 목록(필터·정렬 동일) 전체 ID 순서에서 현재 페이지 구간만 드래그 결과로 교체한 뒤,
     * 전체에 대해 sort_order 를 1..N 으로 다시 부여한다. (페이지네이션에서도 전역 순서 유지)
     *
     * @param  array<int>  $orderedIds  현재 화면 위→아래 순서의 portfolio id
     */
    public function mergeOrderedSliceAndRenumber(
        array $orderedIds,
        int $page,
        int $perPage,
        ?string $category,
        ?string $keyword
    ): void {
        if (! in_array($perPage, [10, 20, 50, 100, 500, 1000], true)) {
            $perPage = 10;
        }

        $query = Portfolio::query();
        if ($category !== null && $category !== '') {
            $query->whereJsonContains('categories', $category);
        }
        if ($keyword !== null && $keyword !== '') {
            $query->where('title', 'like', '%'.$keyword.'%');
        }

        $this->applyBackofficePortfolioListOrdering($query);

        /** @var array<int, int> $allIds */
        $allIds = $query->pluck('id')->all();

        $total = count($allIds);
        if ($total === 0) {
            throw new InvalidArgumentException('정렬할 항목이 없습니다.');
        }

        $start = ($page - 1) * $perPage;
        $len = count($orderedIds);

        if ($len < 1) {
            throw new InvalidArgumentException('정렬 정보가 비어 있습니다.');
        }

        if ($start >= $total) {
            throw new InvalidArgumentException('페이지 정보가 맞지 않습니다. 새로고침 후 다시 시도해 주세요.');
        }

        $expectedSlice = array_slice($allIds, $start, $len);
        $a = $expectedSlice;
        $b = $orderedIds;
        sort($a);
        sort($b);
        if ($a !== $b) {
            throw new InvalidArgumentException('목록이 변경되었습니다. 새로고침 후 다시 시도해 주세요.');
        }

        $newAll = array_merge(
            array_slice($allIds, 0, $start),
            $orderedIds,
            array_slice($allIds, $start + $len)
        );

        DB::transaction(function () use ($newAll): void {
            $n = count($newAll);
            foreach ($newAll as $index => $id) {
                $sortOrder = $n - $index;
                Portfolio::where('id', $id)->update(['sort_order' => $sortOrder]);
            }
        });
    }

    public function deleteMultiple(array $ids): int
    {
        $portfolios = Portfolio::whereIn('id', $ids)->with(['featureImages'])->get();

        foreach ($portfolios as $portfolio) {
            $this->delete($portfolio);
        }

        return $portfolios->count();
    }

    /**
     * 공개 URL용 slug. 수정 시 빈 값이면 기존 slug 유지, 신규는 제목 기반 생성.
     */
    private function resolveSlugForSave(?string $inputSlug, string $title, ?Portfolio $existing): string
    {
        $trimmed = $inputSlug !== null ? trim($inputSlug) : '';
        if ($trimmed === '' && $existing !== null) {
            return $existing->slug;
        }

        $base = $trimmed !== ''
            ? Str::slug($trimmed)
            : Portfolio::baseSlugFromTitle($title);
        if ($base === '') {
            $base = 'portfolio';
        }
        if (PublicUrlSegments::isReservedForPortfolio($base)) {
            $base = $base.'-case';
        }

        return $base;
    }

    private function mapPortfolioData(array $data): array
    {
        return [
            'category' => $data['category'],
            'categories' => $data['categories'] ?? [],
            'service_subcategories' => $data['service_subcategories'] ?? [],
            'slug' => $data['slug'],
            'development_summary' => $data['development_summary'] ?? null,
            'title' => $data['title'],
            'keywords' => $data['keywords'] ?? [],
            'thumbnail_image' => $data['thumbnail_image'] ?? null,
            'top_image' => $data['top_image'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_main_display' => (bool) ($data['is_main_display'] ?? false),
            'is_active' => (bool) ($data['is_active'] ?? true),
            'detail_summary' => $data['detail_summary'] ?? null,
            'detail_editor' => $data['detail_editor'] ?? null,
            'site_url' => $data['site_url'] ?? null,
            'is_direct_site_link' => (bool) ($data['is_direct_site_link'] ?? false),
            'problem_title' => $data['problem_title'] ?? null,
            'problem_content' => $data['problem_content'] ?? null,
            'solution_title' => $data['solution_title'] ?? null,
            'solution_content' => $data['solution_content'] ?? null,
            'solution_before_image' => $data['solution_before_image'] ?? null,
            'solution_after_image' => $data['solution_after_image'] ?? null,
        ];
    }

    private function syncReviews(Portfolio $portfolio, array $reviews): void
    {
        $portfolio->reviews()->delete();
        foreach (array_values($reviews) as $idx => $review) {
            if (empty($review['title']) && empty($review['manager_name']) && empty($review['content'])) {
                continue;
            }
            $portfolio->reviews()->create([
                'title' => $review['title'] ?? null,
                'manager_name' => $review['manager_name'] ?? null,
                'content' => $review['content'] ?? null,
                'sort_order' => $idx,
            ]);
        }
    }

    private function syncFeatureDevelopments(Portfolio $portfolio, array $featureDevelopments): void
    {
        $nextRows = [];
        foreach (array_values($featureDevelopments) as $idx => $featureDevelopment) {
            if (
                empty($featureDevelopment['title']) &&
                empty($featureDevelopment['content']) &&
                empty($featureDevelopment['image_path'])
            ) {
                continue;
            }
            $nextRows[] = [
                'title' => $featureDevelopment['title'] ?? null,
                'content' => $featureDevelopment['content'] ?? null,
                'background_text' => $featureDevelopment['background_text'] ?? null,
                'image_path' => $featureDevelopment['image_path'] ?? null,
                'sort_order' => $idx,
            ];
        }

        $keptImagePaths = collect($nextRows)
            ->pluck('image_path')
            ->filter(fn ($path) => is_string($path) && $path !== '')
            ->unique()
            ->values()
            ->all();

        // 다음 저장 목록에 없는 기존 이미지만 삭제 (유지 이미지까지 삭제되어 엑박이 나는 문제 방지)
        foreach ($portfolio->featureDevelopments as $featureDevelopment) {
            $oldPath = $featureDevelopment->image_path;
            if (! $oldPath) {
                continue;
            }
            if (! in_array($oldPath, $keptImagePaths, true)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $portfolio->featureDevelopments()->delete();
        foreach ($nextRows as $row) {
            $portfolio->featureDevelopments()->create($row);
        }
    }
}
