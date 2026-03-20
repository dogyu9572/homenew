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
    public function __construct(private PortfolioService $portfolioService)
    {
    }

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
        $portfolio->load(['featureImages', 'reviews']);
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
        $this->portfolioService->updateOrder($request->input('portfolioOrder', []));
        return response()->json(['success' => true]);
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
        return $request->validate([
            'category' => ['required', Rule::in(Portfolio::CATEGORIES), Rule::unique('portfolios', 'category')->ignore($portfolio?->id)],
            'development_summary' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'keywords_input' => 'nullable|string|max:1000',
            'thumbnail_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
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
            'feature_title' => 'nullable|string|max:255',
            'feature_content' => 'nullable|string',
            'feature_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'reviews.*.title' => 'nullable|string|max:255',
            'reviews.*.manager_name' => 'nullable|string|max:255',
            'reviews.*.content' => 'nullable|string',
        ]);
    }

    private function preparePayload(Request $request, array $validated, ?Portfolio $portfolio = null): array
    {
        $payload = $validated;
        $payload['keywords'] = $this->parseKeywords($request->input('keywords_input'));
        $payload['is_main_display'] = $request->boolean('is_main_display');
        $payload['is_active'] = $request->boolean('is_active', true);
        unset($payload['keywords_input']);

        if ($request->hasFile('thumbnail_image')) {
            if ($portfolio?->thumbnail_image) {
                Storage::disk('public')->delete($portfolio->thumbnail_image);
            }
            $payload['thumbnail_image'] = $request->file('thumbnail_image')->store('portfolio', 'public');
        } elseif ($portfolio) {
            $payload['thumbnail_image'] = $portfolio->thumbnail_image;
        }

        if ($request->hasFile('solution_before_image')) {
            if ($portfolio?->solution_before_image) {
                Storage::disk('public')->delete($portfolio->solution_before_image);
            }
            $payload['solution_before_image'] = $request->file('solution_before_image')->store('portfolio', 'public');
        } elseif ($portfolio) {
            $payload['solution_before_image'] = $portfolio->solution_before_image;
        }

        if ($request->hasFile('solution_after_image')) {
            if ($portfolio?->solution_after_image) {
                Storage::disk('public')->delete($portfolio->solution_after_image);
            }
            $payload['solution_after_image'] = $request->file('solution_after_image')->store('portfolio', 'public');
        } elseif ($portfolio) {
            $payload['solution_after_image'] = $portfolio->solution_after_image;
        }

        $storedFeatureImages = [];
        if ($request->hasFile('feature_images')) {
            foreach ($request->file('feature_images') as $image) {
                if (count($storedFeatureImages) >= 5) {
                    break;
                }
                if ($image) {
                    $storedFeatureImages[] = $image->store('portfolio/features', 'public');
                }
            }
        } elseif ($portfolio) {
            $storedFeatureImages = $portfolio->featureImages->pluck('image_path')->toArray();
            $payload['feature_images_keep_existing'] = true;
        }
        $payload['feature_images'] = array_slice($storedFeatureImages, 0, 5);

        return $payload;
    }

    private function parseKeywords(?string $input): array
    {
        if (!$input) {
            return [];
        }

        $parts = preg_split('/\s+/', trim($input));
        $keywords = [];
        foreach ($parts as $part) {
            $word = trim($part);
            if ($word === '') {
                continue;
            }
            if (!str_starts_with($word, '#')) {
                $word = '#' . $word;
            }
            $keywords[] = $word;
        }

        return array_values(array_unique($keywords));
    }
}
