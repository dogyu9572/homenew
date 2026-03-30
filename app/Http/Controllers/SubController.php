<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogPostEventLog;
use App\Models\Portfolio;
use App\Services\BlogService;
use App\Services\FaqPublicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubController extends Controller
{
    private const SI_SUBCATEGORY_MAP = [
        'reservation' => '서비스-통합SI시스템 개발(예약)',
        'erp' => '통합SI시스템 개발(ERP)',
        'backoffice' => '통합SI시스템 개발(백오피스)',
        'cms' => '통합SI시스템 개발(CMS)',
        'lms' => '통합SI시스템 개발(LMS)',
    ];

    private const APP_SUBCATEGORY = '서비스-앱';
    private const AI_SUBCATEGORY = '서비스-AI';

    public function __construct(
        private FaqPublicService $faqPublicService
    ) {}

    public function homepage_seo_geo()
    {
        $gNum = '01';
        $sNum = '01';
        $gName = 'Service';
        $sName = 'SEO·GEO 최적화';
        $gSlug = 'service';
        $faqItems = $this->faqPublicService->forServiceMenuLabel($sName);

        return view('service.homepage-seo-geo', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'faqItems'));
    }

    public function homepage_development()
    {
        $gNum = '01';
        $sNum = '02';
        $gName = 'Service';
        $sName = '홈페이지 제작';
        $gSlug = 'service';
        $faqItems = $this->faqPublicService->forServiceMenuLabel($sName);

        return view('service.homepage-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'faqItems'));
    }

    public function website_maintenance()
    {
        $gNum = '01';
        $sNum = '03';
        $gName = 'Service';
        $sName = '홈페이지 유지보수';
        $gSlug = 'service';
        $faqItems = $this->faqPublicService->forServiceMenuLabel($sName);

        return view('service.website-maintenance', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'faqItems'));
    }

    public function ecommerce_website_development()
    {
        $gNum = '01';
        $sNum = '04';
        $gName = 'Service';
        $sName = '온라인 쇼핑몰 제작';
        $gSlug = 'service';
        $faqItems = $this->faqPublicService->forServiceMenuLabel($sName);

        return view('service.ecommerce-website-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'faqItems'));
    }

    public function integrated_si_system_development()
    {
        $gNum = '01';
        $sNum = '05';
        $gName = 'Service';
        $sName = '통합 SI 시스템 개발';
        $gSlug = 'service';
        $faqItems = $this->faqPublicService->forServiceMenuLabel($sName);
        $siPortfolioItemsReservation = $this->getServicePortfolioItems(self::SI_SUBCATEGORY_MAP['reservation']);
        $siPortfolioItemsErp = $this->getServicePortfolioItems(self::SI_SUBCATEGORY_MAP['erp']);
        $siPortfolioItemsBackoffice = $this->getServicePortfolioItems(self::SI_SUBCATEGORY_MAP['backoffice']);
        $siPortfolioItemsCms = $this->getServicePortfolioItems(self::SI_SUBCATEGORY_MAP['cms']);
        $siPortfolioItemsLms = $this->getServicePortfolioItems(self::SI_SUBCATEGORY_MAP['lms']);

        return view('service.integrated-si-system-development', compact(
            'gNum',
            'sNum',
            'gName',
            'sName',
            'gSlug',
            'faqItems',
            'siPortfolioItemsReservation',
            'siPortfolioItemsErp',
            'siPortfolioItemsBackoffice',
            'siPortfolioItemsCms',
            'siPortfolioItemsLms'
        ));
    }

    public function mobile_app_development()
    {
        $gNum = '01';
        $sNum = '06';
        $gName = 'Service';
        $sName = '앱 개발';
        $gSlug = 'service';
        $faqItems = $this->faqPublicService->forServiceMenuLabel($sName);
        $mobileAppPortfolioItems = $this->getServicePortfolioItems(self::APP_SUBCATEGORY, 6);

        return view('service.mobile-app-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'faqItems', 'mobileAppPortfolioItems'));
    }

    public function ai_solution()
    {
        $gNum = '01';
        $sNum = '07';
        $gName = 'Service';
        $sName = '맞춤형 AI 솔루션';
        $gSlug = 'service';
        $faqItems = $this->faqPublicService->forServiceMenuLabel($sName);
        $aiPortfolioItems = $this->getServicePortfolioItems(self::AI_SUBCATEGORY, 6);

        return view('service.ai-solution', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'faqItems', 'aiPortfolioItems'));
    }

    private function getServicePortfolioItems(string $serviceSubcategory, int $limit = 3)
    {
        return Portfolio::query()
            ->where('is_active', true)
            ->whereJsonContains('service_subcategories', $serviceSubcategory)
            ->orderBy('sort_order', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get(['id', 'title', 'category', 'categories', 'development_summary', 'detail_summary', 'thumbnail_image']);
    }

    public function enterprise()
    {
        $gNum = '02';
        $sNum = '01';
        $gName = 'Industry';
        $sName = '중견/대기업';
        $gSlug = 'industries';

        return view('industries.enterprise', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }

    public function academic_association()
    {
        $gNum = '02';
        $sNum = '02';
        $gName = 'Industry';
        $sName = '학회/협회';
        $gSlug = 'industries';

        return view('industries.academic-association', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }

    public function government()
    {
        $gNum = '02';
        $sNum = '03';
        $gName = 'Industry';
        $sName = '공공기관';
        $gSlug = 'industries';

        return view('industries.government', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }

    public function hospital_medical_website_development()
    {
        $gNum = '02';
        $sNum = '04';
        $gName = 'Industry';
        $sName = '병원/의료';
        $gSlug = 'industries';

        return view('industries.hospital-medical-website-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }

    public function university_research_lab_website()
    {
        $gNum = '02';
        $sNum = '05';
        $gName = 'Industry';
        $sName = '대학·연구실';
        $gSlug = 'industries';

        return view('industries.university-research-lab-website', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }

    public function portfolio_list(Request $request)
    {
        $gNum = '03';
        $sNum = '01';
        $gName = 'Portfolio';
        $sName = '포트폴리오';
        $gSlug = 'portfolio';
        $query = Portfolio::query()->where('is_active', true);
        $category = (string) $request->input('category', '');
        $keyword = trim((string) $request->input('q', ''));
        $keywordLike = $keyword !== '' ? '%'.addcslashes($keyword, '%_\\').'%' : '';
        $keywordTag = $keyword !== '' ? '#'.ltrim($keyword, '#') : '';

        if ($category !== '') {
            $query->whereJsonContains('categories', $category);
        }
        if ($keyword !== '') {
            $query->where(function ($sub) use ($keywordLike, $keywordTag) {
                $sub->where('title', 'like', $keywordLike)
                    ->orWhere('development_summary', 'like', $keywordLike)
                    ->orWhere('category', 'like', $keywordLike)
                    ->orWhere('categories', 'like', $keywordLike)
                    ->orWhere('keywords', 'like', $keywordLike)
                    ->orWhere('detail_summary', 'like', $keywordLike)
                    ->orWhere('detail_editor', 'like', $keywordLike)
                    ->orWhere('problem_title', 'like', $keywordLike)
                    ->orWhere('problem_content', 'like', $keywordLike)
                    ->orWhere('solution_title', 'like', $keywordLike)
                    ->orWhere('solution_content', 'like', $keywordLike)
                    ->orWhereJsonContains('keywords', $keywordTag)
                    ->orWhereHas('featureDevelopments', function ($fq) use ($keywordLike) {
                        $fq->where('title', 'like', $keywordLike)
                            ->orWhere('content', 'like', $keywordLike);
                    });
            });
        }

        $portfolios = $query->orderBy('sort_order', 'desc')->orderBy('id', 'desc')->paginate(12)->withQueryString();
        $portfolioCount = $portfolios->total();
        $listItems = [];
        foreach (array_values($portfolios->items()) as $index => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => (($portfolios->currentPage() - 1) * $portfolios->perPage()) + $index + 1,
                'name' => $item->title,
                'url' => route('portfolio.portfolio_view', ['portfolio' => $item->id]),
            ];
        }

        return view('portfolio.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'portfolios', 'portfolioCount', 'category', 'keyword', 'listItems'));
    }

    public function portfolio_view(?Portfolio $portfolio = null)
    {
        $gNum = '03';
        $sNum = '01';
        $gName = 'Portfolio';
        $sName = '포트폴리오 상세';
        $page = 'view';
        $gSlug = 'portfolio';
        if (! $portfolio) {
            $portfolio = Portfolio::where('is_active', true)->orderBy('sort_order', 'desc')->firstOrFail();
        }
        $portfolio->load(['featureDevelopments', 'reviews']);
        $canonicalUrl = strtok(url()->current(), '?');
        $primaryCategory = $portfolio->category ?: ($portfolio->categories[0] ?? '');

        return view('portfolio.view', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'page', 'portfolio', 'canonicalUrl', 'primaryCategory'));
    }

    public function blog_list(Request $request, BlogService $blogService)
    {
        $gNum = '04';
        $sNum = '01';
        $gName = 'Blog';
        $sName = '블로그';
        $gSlug = 'blog';

        $category = (string) $request->input('category', '');
        $keyword = (string) $request->input('keyword', '');

        $posts = $blogService->getList($category, $keyword);
        $featuredPost = $blogService->getFeaturedPost($category);
        $listItems = [];
        foreach (array_values($posts->items()) as $index => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => (($posts->currentPage() - 1) * $posts->perPage()) + $index + 1,
                'name' => $item->title,
                'url' => route('blog.blog_view', ['blogPost' => $item->id]),
            ];
        }
        $featuredExcerpt = null;
        if ($featuredPost) {
            $firstSection = $featuredPost->sections()->first();
            $featuredExcerpt = Str::limit(strip_tags((string) ($firstSection->content ?? '')), 220);
        }

        return view('blog.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'posts', 'featuredPost', 'category', 'keyword', 'listItems', 'featuredExcerpt'));
    }

    public function blog_view(BlogService $blogService, ?BlogPost $blogPost = null)
    {
        $gNum = '04';
        $sNum = '01';
        $gName = 'Blog';
        $page = 'view';
        $gSlug = 'blog';

        $post = $blogPost ?? ($blogService->getFeaturedPost('') ?? abort(404));
        if (! $post->is_published) {
            abort(404);
        }

        $sName = $post->title;
        $recommended = $blogService->getAutoRecommendedPosts($post);
        $likeCount = BlogPostEventLog::query()
            ->where('blog_post_id', $post->id)
            ->where('event_type', BlogPostEventLog::EVENT_LIKE)
            ->count();
        $heroImage = $post->thumbnail_path ? Storage::url($post->thumbnail_path) : null;
        $publishedIso = $post->published_at?->toAtomString() ?? '';
        $publishedDate = $post->published_at?->format('Y.m.d') ?? '';
        $canonicalUrl = strtok(url()->current(), '?');
        $tocSections = $post->sections->filter(fn ($section) => !empty($section->subtitle))->values();

        return view('blog.view', compact(
            'gNum',
            'sNum',
            'gName',
            'sName',
            'gSlug',
            'page',
            'post',
            'recommended',
            'likeCount',
            'heroImage',
            'publishedIso',
            'publishedDate',
            'canonicalUrl',
            'tocSections'
        ));
    }

    public function blog_event(Request $request, BlogPost $blogPost, BlogService $blogService): JsonResponse
    {
        $validated = $request->validate([
            'event_type' => ['required', 'string', 'in:'.implode(',', BlogPostEventLog::EVENTS)],
            'session_key' => ['required', 'string', 'max:128'],
            'dwell_seconds' => ['nullable', 'integer', 'min:0', 'max:7200'],
            'scroll_depth' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        if ($validated['event_type'] === BlogPostEventLog::EVENT_VIEW) {
            $blogService->recordView($blogPost, $validated['session_key']);
        } else {
            $blogService->recordEvent(
                $blogPost,
                $validated['session_key'],
                $validated['event_type'],
                [
                    'dwell_seconds' => $validated['dwell_seconds'] ?? null,
                    'scroll_depth' => $validated['scroll_depth'] ?? null,
                ]
            );
        }

        return response()->json(['success' => true]);
    }

    public function blog_like(Request $request, BlogPost $blogPost, BlogService $blogService): JsonResponse
    {
        $validated = $request->validate([
            'session_key' => ['required', 'string', 'max:128'],
        ]);

        $alreadyLiked = BlogPostEventLog::query()
            ->where('blog_post_id', $blogPost->id)
            ->where('event_type', BlogPostEventLog::EVENT_LIKE)
            ->where('session_key', $validated['session_key'])
            ->exists();

        $liked = false;
        if (! $alreadyLiked) {
            $blogService->recordEvent(
                $blogPost,
                $validated['session_key'],
                BlogPostEventLog::EVENT_LIKE
            );
            $liked = true;
        } else {
            BlogPostEventLog::query()
                ->where('blog_post_id', $blogPost->id)
                ->where('event_type', BlogPostEventLog::EVENT_LIKE)
                ->where('session_key', $validated['session_key'])
                ->delete();
        }

        return response()->json([
            'success' => true,
            'liked' => $liked,
            'like_count' => BlogPostEventLog::query()
                ->where('blog_post_id', $blogPost->id)
                ->where('event_type', BlogPostEventLog::EVENT_LIKE)
                ->count(),
        ]);
    }

    public function contact()
    {
        $gNum = '05';
        $sNum = '01';
        $gName = 'Contact Us';
        $sName = '문의하기';
        $gSlug = 'contact';

        return view('contact.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }

    public function privacy_policy()
    {
        $gNum = '09';
        $sNum = '01';
        $gName = '개인정보처리방침';
        $sName = '개인정보처리방침';
        $gSlug = 'terms';

        return view('terms.privacy_policy', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }
}
