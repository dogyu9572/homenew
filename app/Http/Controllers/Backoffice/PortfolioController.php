<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Services\Backoffice\PortfolioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    public function __construct(private PortfolioService $portfolioService) {}

    public function index(Request $request)
    {
        $portfolios = $this->portfolioService->getPortfolios($request);

        return view('backoffice.portfolio.index', [
            'portfolios' => $portfolios,
            'categories' => Portfolio::CATEGORIES,
        ]);
    }

    public function create()
    {
        return view('backoffice.portfolio.create', [
            'categories' => Portfolio::CATEGORIES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRequest($request);
        $payload = $this->preparePayload($request, $validated);
        $this->portfolioService->create($payload);

        return redirect()->route('backoffice.portfolio.index')->with('success', '포트폴리오가 등록되었습니다.');
    }

    public function edit(Portfolio $portfolio)
    {
        $portfolio->load(['featureImages', 'featureDevelopments', 'reviews']);

        return view('backoffice.portfolio.edit', [
            'portfolio' => $portfolio,
            'categories' => Portfolio::CATEGORIES,
        ]);
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $validated = $this->validateRequest($request, $portfolio);
        $payload = $this->preparePayload($request, $validated, $portfolio);
        $this->portfolioService->update($portfolio, $payload);

        return redirect()->route('backoffice.portfolio.index')->with('success', '포트폴리오가 수정되었습니다.');
    }

    public function destroy(Portfolio $portfolio)
    {
        $this->portfolioService->delete($portfolio);

        return redirect()->route('backoffice.portfolio.index')->with('success', '포트폴리오가 삭제되었습니다.');
    }

    public function updateOrder(Request $request)
    {
        // FAQ 정렬(sorting.js)과 동일: { updates: [ { post_id, sort_order }, ... ] }
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
            ->route('backoffice.portfolio.index')
            ->with('success', "{$deletedCount}개의 포트폴리오가 삭제되었습니다.");
    }

    private function validateRequest(Request $request, ?Portfolio $portfolio = null): array
    {
        return $request->validate(
            [
                'categories' => ['required', 'array', 'min:1'],
                'categories.*' => ['string', Rule::in(Portfolio::CATEGORIES)],
                'service_subcategories' => ['nullable', 'array'],
                'service_subcategories.*' => ['string', Rule::in(Portfolio::SERVICE_SUBCATEGORIES)],
                'development_summary' => 'nullable|string|max:255',
                'title' => 'required|string|max:255',
                'keywords_input' => 'nullable|string|max:1000',
                'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'top_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'sort_order' => 'nullable|integer|min:0',
                'is_main_display' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
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
            ]
        );
    }

    private function preparePayload(Request $request, array $validated, ?Portfolio $portfolio = null): array
    {
        $payload = $validated;
        $categories = array_values(array_unique($request->input('categories', [])));
        $serviceSubcategories = array_values(array_unique($request->input('service_subcategories', [])));
        $payload['categories'] = $categories;
        $payload['service_subcategories'] = $serviceSubcategories;
        $payload['category'] = $categories[0] ?? null;
        $payload['keywords'] = $this->parseKeywords($request->input('keywords_input'));
        $payload['is_main_display'] = $request->boolean('is_main_display');
        $payload['is_active'] = $request->boolean('is_active', true);
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
