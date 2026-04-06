<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Services\Backoffice\PortfolioService;
use App\Support\PublicUrlSegments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class PortfolioController extends Controller
{
    public function __construct(private PortfolioService $portfolioService) {}

    public function index(Request $request)
    {
        $allowedPer = $this->allowedPortfolioPerPages();
        if ($request->filled('per_page')) {
            $p = (int) $request->get('per_page');
            if (in_array($p, $allowedPer, true)) {
                $request->session()->put('backoffice.portfolio.per_page', $p);
            }
        } else {
            $saved = $request->session()->get('backoffice.portfolio.per_page');
            if ($saved !== null && in_array((int) $saved, $allowedPer, true)) {
                $request->merge(['per_page' => (int) $saved]);
            }
        }

        $portfolios = $this->portfolioService->getPortfolios($request);

        return view('backoffice.portfolio.index', [
            'portfolios' => $portfolios,
            'categories' => Portfolio::CATEGORIES,
            'listQuery' => $this->portfolioListQueryFromRequest($request),
        ]);
    }

    public function create(Request $request)
    {
        return view('backoffice.portfolio.create', [
            'categories' => Portfolio::CATEGORIES,
            'listQuery' => $this->portfolioListQueryFromRequest($request),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $payload = $this->preparePayload($request, $validated);
        $this->portfolioService->create($payload);

        return redirect()
            ->route('backoffice.portfolio.index', $this->portfolioIndexRedirectFromReturnInputs($request))
            ->with('success', '포트폴리오가 등록되었습니다.');
    }

    public function edit(Request $request, Portfolio $portfolio)
    {
        $portfolio->load(['featureImages', 'featureDevelopments', 'reviews']);

        return view('backoffice.portfolio.edit', [
            'portfolio' => $portfolio,
            'categories' => Portfolio::CATEGORIES,
            'listQuery' => $this->portfolioListQueryFromRequest($request),
        ]);
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $validated = $this->validateRequest($request, $portfolio);
        $payload = $this->preparePayload($request, $validated, $portfolio);
        $this->portfolioService->update($portfolio, $payload);

        return redirect()
            ->route('backoffice.portfolio.index', $this->portfolioIndexRedirectFromReturnInputs($request))
            ->with('success', '포트폴리오가 수정되었습니다.');
    }

    public function destroy(Request $request, Portfolio $portfolio)
    {
        $this->portfolioService->delete($portfolio);

        return redirect()
            ->route('backoffice.portfolio.index', $this->portfolioIndexRedirectFromQuery($request))
            ->with('success', '포트폴리오가 삭제되었습니다.');
    }

    public function updateOrder(Request $request)
    {
        // 드래그 순서: 목록 필터·페이지와 동일한 전체 순서에서 해당 페이지 구간만 교체 후 전체 재번호
        if ($request->filled('ordered_ids') && $request->has('portfolio_list_context')) {
            $validated = $request->validate([
                'ordered_ids' => ['required', 'array', 'min:1'],
                'ordered_ids.*' => ['integer', 'exists:portfolios,id'],
                'portfolio_list_context' => ['required', 'array'],
                'portfolio_list_context.page' => ['required', 'integer', 'min:1'],
                'portfolio_list_context.per_page' => ['required', 'integer', 'in:10,20,50,100,500,1000'],
                'portfolio_list_context.category' => ['nullable', 'string'],
                'portfolio_list_context.keyword' => ['nullable', 'string'],
            ]);

            try {
                $this->portfolioService->mergeOrderedSliceAndRenumber(
                    array_map('intval', $validated['ordered_ids']),
                    (int) $validated['portfolio_list_context']['page'],
                    (int) $validated['portfolio_list_context']['per_page'],
                    $validated['portfolio_list_context']['category'] ?? null,
                    $validated['portfolio_list_context']['keyword'] ?? null
                );
            } catch (InvalidArgumentException $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => '정렬 순서가 저장되었습니다.',
            ]);
        }

        // 레거시: { updates: [ { post_id, sort_order }, ... ] }
        if ($request->has('updates')) {
            $validated = $request->validate([
                'updates' => ['required', 'array', 'min:1'],
                'updates.*.post_id' => ['required', 'integer', 'exists:portfolios,id'],
                'updates.*.sort_order' => ['required', 'integer', 'min:0'],
            ]);
            $rows = array_map(static fn (array $u) => [
                'id' => (int) $u['post_id'],
                'order' => (int) $u['sort_order'],
            ], $validated['updates']);
            $this->portfolioService->updateOrder($rows);

            return response()->json([
                'success' => true,
                'message' => '정렬 순서가 저장되었습니다.',
            ]);
        }

        $legacy = $request->input('portfolioOrder', []);
        if ($legacy !== [] && is_array($legacy)) {
            $this->portfolioService->updateOrder($legacy);

            return response()->json(['success' => true]);
        }

        return response()->json([
            'success' => false,
            'message' => '요청 형식이 올바르지 않습니다.',
        ], 422);
    }

    public function deleteMultiple(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:portfolios,id'],
        ]);

        $deletedCount = $this->portfolioService->deleteMultiple($validated['ids']);

        return redirect()
            ->route('backoffice.portfolio.index', $this->portfolioIndexRedirectFromReturnInputs($request))
            ->with('success', "{$deletedCount}개의 포트폴리오가 삭제되었습니다.");
    }

    /**
     * 목록 화면 GET 쿼리(page, per_page, category, keyword)를 배열로 정리
     *
     * @return array<string, mixed>
     */
    private function portfolioListQueryFromRequest(Request $request): array
    {
        return $this->sanitizePortfolioIndexQueryParams([
            'page' => $request->input('page'),
            'per_page' => $request->input('per_page'),
            'category' => $request->input('category'),
            'keyword' => $request->input('keyword'),
        ]);
    }

    /**
     * 등록/수정 폼의 return_* 필드 → 목록 리다이렉트 쿼리
     *
     * @return array<string, mixed>
     */
    private function portfolioIndexRedirectFromReturnInputs(Request $request): array
    {
        return $this->sanitizePortfolioIndexQueryParams([
            'page' => $request->input('return_page'),
            'per_page' => $request->input('return_per_page'),
            'category' => $request->input('return_category'),
            'keyword' => $request->input('return_keyword'),
        ]);
    }

    /**
     * 삭제 요청 URL 쿼리 → 목록 리다이렉트 쿼리
     *
     * @return array<string, mixed>
     */
    private function portfolioIndexRedirectFromQuery(Request $request): array
    {
        return $this->sanitizePortfolioIndexQueryParams([
            'page' => $request->query('page'),
            'per_page' => $request->query('per_page'),
            'category' => $request->query('category'),
            'keyword' => $request->query('keyword'),
        ]);
    }

    /**
     * @param  array<string|int|null>  $raw
     * @return array<string, mixed>
     */
    private function sanitizePortfolioIndexQueryParams(array $raw): array
    {
        $allowedPer = $this->allowedPortfolioPerPages();
        $out = [];
        if (isset($raw['page']) && $raw['page'] !== '' && (int) $raw['page'] >= 1) {
            $out['page'] = (int) $raw['page'];
        }
        if (isset($raw['per_page']) && $raw['per_page'] !== '' && in_array((int) $raw['per_page'], $allowedPer, true)) {
            $out['per_page'] = (int) $raw['per_page'];
        }
        if (! empty($raw['category'])) {
            $out['category'] = (string) $raw['category'];
        }
        if (! empty($raw['keyword'])) {
            $out['keyword'] = (string) $raw['keyword'];
        }

        return $out;
    }

    /**
     * @return array<int, int>
     */
    private function allowedPortfolioPerPages(): array
    {
        return [10, 20, 50, 100, 500, 1000];
    }

    private function validateRequest(Request $request, ?Portfolio $portfolio = null): array
    {
        $request->merge([
            'slug' => trim((string) $request->input('slug', '')),
        ]);

        $validator = Validator::make(
            $request->all(),
            [
                'categories' => ['required', 'array', 'min:1'],
                'categories.*' => ['string', Rule::in(Portfolio::CATEGORIES)],
                'service_subcategories' => ['nullable', 'array'],
                'service_subcategories.*' => ['string', Rule::in(Portfolio::SERVICE_SUBCATEGORIES)],
                'development_summary' => 'nullable|string|max:255',
                'title' => 'required|string|max:255',
                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                    Rule::unique('portfolios', 'slug')->ignore($portfolio),
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        if (! is_string($value) || $value === '') {
                            return;
                        }
                        if (PublicUrlSegments::isReservedForPortfolio(Str::slug($value))) {
                            $fail('이 URL 경로는 사용할 수 없습니다.');
                        }
                    },
                ],
                'keywords_input' => 'nullable|string|max:1000',
                'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'top_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'sort_order' => 'nullable|integer|min:0',
                'is_main_display' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
                'is_direct_site_link' => 'nullable|boolean',
                'detail_summary' => 'nullable|string',
                'detail_editor' => 'nullable|string',
                'site_url' => 'nullable|url|max:255',
                'problem_title' => 'nullable|string|max:255',
                'problem_content' => 'nullable|string',
                'solution_title' => 'nullable|string|max:255',
                'solution_content' => 'nullable|string',
                'solution_before_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'solution_after_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'feature_developments' => 'nullable|array',
                'feature_developments.*.title' => 'nullable|string|max:255',
                'feature_developments.*.content' => 'nullable|string',
                'feature_developments.*.background_text' => 'nullable|string|max:255',
                'feature_developments.*.existing_image_path' => 'nullable|string|max:255',
                'feature_developments.*.remove_image' => 'nullable|boolean',
                'feature_developments.*.image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'reviews.*.title' => 'nullable|string|max:255',
                'reviews.*.manager_name' => 'nullable|string|max:255',
                'reviews.*.content' => 'nullable|string',
                'remove_thumbnail_image' => 'nullable|boolean',
                'remove_top_image' => 'nullable|boolean',
                'remove_solution_before_image' => 'nullable|boolean',
                'remove_solution_after_image' => 'nullable|boolean',
            ],
            [
                'categories.required' => '상위 카테고리를 1개 이상 선택해 주세요.',
                'categories.min' => '상위 카테고리를 1개 이상 선택해 주세요.',
                'categories.*.in' => '유효하지 않은 상위 카테고리입니다.',
                'service_subcategories.*.in' => '유효하지 않은 서브카테고리입니다.',
                'slug.regex' => 'URL 슬러그는 영문 소문자, 숫자, 하이픈(-)만 사용할 수 있습니다. (한글·공백·특수문자·언더스코어(_)는 사용할 수 없습니다.)',
                'slug.unique' => '이미 사용 중인 URL 슬러그입니다. 다른 값을 입력해 주세요.',
            ]
        );

        $validator->after(function ($v) use ($request): void {
            if ($request->boolean('is_direct_site_link') && trim((string) $request->input('site_url', '')) === '') {
                $v->errors()->add('site_url', '「상세 페이지 없이 새창으로 사이트 링크 이동」을 사용할 때는 사이트 링크(URL)이 필수입니다.');
            }
        });

        return $validator->validate();
    }

    private function preparePayload(Request $request, array $validated, ?Portfolio $portfolio = null): array
    {
        $payload = $validated;
        $categories = array_values(array_unique($request->input('categories', [])));
        $serviceSubcategories = array_values(array_unique($request->input('service_subcategories', [])));
        $payload['categories'] = $categories;
        $payload['service_subcategories'] = $serviceSubcategories;
        $payload['category'] = $categories[0] ?? null;
        $payload['slug'] = $validated['slug'] ?? null;
        $payload['keywords'] = $this->parseKeywords($request->input('keywords_input'));
        $payload['is_main_display'] = $request->boolean('is_main_display');
        // 체크 해제 시 name 자체가 전송되지 않으므로 기본값 true 금지(숨김 저장 불가 버그)
        $payload['is_active'] = $request->boolean('is_active', false);
        $payload['is_direct_site_link'] = $request->boolean('is_direct_site_link', false);
        unset($payload['keywords_input']);

        if ($request->boolean('remove_thumbnail_image') && $portfolio?->thumbnail_image) {
            Storage::disk('public')->delete($portfolio->thumbnail_image);
            $payload['thumbnail_image'] = null;
        } elseif ($request->hasFile('thumbnail_image')) {
            if ($portfolio?->thumbnail_image) {
                Storage::disk('public')->delete($portfolio->thumbnail_image);
            }
            $payload['thumbnail_image'] = $request->file('thumbnail_image')->store('portfolio', 'public');
        } elseif ($portfolio) {
            $payload['thumbnail_image'] = $portfolio->thumbnail_image;
        }

        if ($request->boolean('remove_top_image') && $portfolio?->top_image) {
            Storage::disk('public')->delete($portfolio->top_image);
            $payload['top_image'] = null;
        } elseif ($request->hasFile('top_image')) {
            if ($portfolio?->top_image) {
                Storage::disk('public')->delete($portfolio->top_image);
            }
            $payload['top_image'] = $request->file('top_image')->store('portfolio', 'public');
        } elseif ($portfolio) {
            $payload['top_image'] = $portfolio->top_image;
        }

        if ($request->boolean('remove_solution_before_image') && $portfolio?->solution_before_image) {
            Storage::disk('public')->delete($portfolio->solution_before_image);
            $payload['solution_before_image'] = null;
        } elseif ($request->hasFile('solution_before_image')) {
            if ($portfolio?->solution_before_image) {
                Storage::disk('public')->delete($portfolio->solution_before_image);
            }
            $payload['solution_before_image'] = $request->file('solution_before_image')->store('portfolio', 'public');
        } elseif ($portfolio) {
            $payload['solution_before_image'] = $portfolio->solution_before_image;
        }

        if ($request->boolean('remove_solution_after_image') && $portfolio?->solution_after_image) {
            Storage::disk('public')->delete($portfolio->solution_after_image);
            $payload['solution_after_image'] = null;
        } elseif ($request->hasFile('solution_after_image')) {
            if ($portfolio?->solution_after_image) {
                Storage::disk('public')->delete($portfolio->solution_after_image);
            }
            $payload['solution_after_image'] = $request->file('solution_after_image')->store('portfolio', 'public');
        } elseif ($portfolio) {
            $payload['solution_after_image'] = $portfolio->solution_after_image;
        }

        $featureDevelopments = [];
        $featureRows = $request->input('feature_developments', []);
        foreach ($featureRows as $idx => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $content = trim((string) ($row['content'] ?? ''));
            $backgroundText = trim((string) ($row['background_text'] ?? ''));
            $existingImagePath = $row['existing_image_path'] ?? null;
            $removeExistingImage = filter_var($row['remove_image'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $uploadedImagePath = null;

            if ($request->hasFile("feature_developments.$idx.image")) {
                $uploadedImagePath = $request->file("feature_developments.$idx.image")->store('portfolio/features', 'public');
            }

            $imagePath = $removeExistingImage ? null : ($uploadedImagePath ?? $existingImagePath);
            if ($title === '' && $content === '' && $backgroundText === '' && ! $imagePath) {
                continue;
            }

            $featureDevelopments[] = [
                'title' => $title !== '' ? $title : null,
                'content' => $content !== '' ? $content : null,
                'background_text' => $backgroundText !== '' ? $backgroundText : null,
                'image_path' => $imagePath,
            ];
        }
        $payload['feature_developments'] = $featureDevelopments;

        return $payload;
    }

    private function parseKeywords(?string $input): array
    {
        if (! $input) {
            return [];
        }

        $parts = preg_split('/\s+/', trim($input));
        $keywords = [];
        foreach ($parts as $part) {
            $word = trim($part);
            if ($word === '') {
                continue;
            }
            if (! str_starts_with($word, '#')) {
                $word = '#'.$word;
            }
            $keywords[] = $word;
        }

        return array_values(array_unique($keywords));
    }
}
