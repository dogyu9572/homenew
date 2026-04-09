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

    public function about()
    {
        $gNum = '00';
        $sNum = '01';
        $gName = 'About';
        $sName = '회사소개';
        $gSlug = 'about';

        return view('about.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug'));
    }

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
            ->get(['id', 'slug', 'title', 'category', 'categories', 'development_summary', 'detail_summary', 'thumbnail_image', 'site_url', 'is_direct_site_link']);
    }

    /**
     * Industry 메뉴 라벨($sName) → 포트폴리오 service_subcategories 값
     * (Portfolio::SERVICE_SUBCATEGORIES·백오피스 체크박스 value와 동일: Industry- 접두 + 하이픈)
     */
    private function industryPortfolioServiceTag(string $sName): ?string
    {
        return match ($sName) {
            '중견/대기업' => 'Industry-중견/대기업',
            '학회/협회' => 'Industry-학회',
            '공공기관' => 'Industry-공공기관',
            '병원/의료' => 'Industry-병원/의료',
            '대학·연구실' => 'Industry-대학·연구실',
            default => null,
        };
    }

    private function getIndustryPortfolioItems(string $sName, int $limit = 6)
    {
        $tag = $this->industryPortfolioServiceTag($sName);
        if ($tag === null) {
            return collect();
        }

        return $this->getServicePortfolioItems($tag, $limit);
    }

    public function enterprise()
    {
        $gNum = '02';
        $sNum = '01';
        $gName = 'Industry';
        $sName = '중견/대기업';
        $gSlug = 'industries';
        $industryPortfolioItems = $this->getIndustryPortfolioItems($sName, 6);

        return view('industries.enterprise', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'industryPortfolioItems'));
    }

    public function academic_association()
    {
        $gNum = '02';
        $sNum = '02';
        $gName = 'Industry';
        $sName = '학회/협회';
        $gSlug = 'industries';
        $industryPortfolioItems = $this->getIndustryPortfolioItems($sName, 6);

        return view('industries.academic-association', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'industryPortfolioItems'));
    }

    public function government()
    {
        $gNum = '02';
        $sNum = '03';
        $gName = 'Industry';
        $sName = '공공기관';
        $gSlug = 'industries';
        $industryPortfolioItems = $this->getIndustryPortfolioItems($sName, 6);

        return view('industries.government', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'industryPortfolioItems'));
    }

    public function hospital_medical_website_development()
    {
        $gNum = '02';
        $sNum = '04';
        $gName = 'Industry';
        $sName = '병원/의료';
        $gSlug = 'industries';
        $industryPortfolioItems = $this->getIndustryPortfolioItems($sName, 6);

        return view('industries.hospital-medical-website-development', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'industryPortfolioItems'));
    }

    public function university_research_lab_website()
    {
        $gNum = '02';
        $sNum = '05';
        $gName = 'Industry';
        $sName = '대학·연구실';
        $gSlug = 'industries';
        $industryPortfolioItems = $this->getIndustryPortfolioItems($sName, 6);

        return view('industries.university-research-lab-website', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'industryPortfolioItems'));
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
                'url' => $item->publicListHref(),
            ];
        }

        return view('portfolio.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'portfolios', 'portfolioCount', 'category', 'keyword', 'listItems'));
    }

    public function portfolio_view(Portfolio $portfolio)
    {
        $gNum = '03';
        $sNum = '01';
        $gName = 'Portfolio';
        $sName = $portfolio->title;
        $page = 'view';
        $gSlug = 'portfolio';
        if (! $portfolio->is_active) {
            abort(404);
        }
        if ($portfolio->is_direct_site_link && filled($portfolio->site_url)) {
            return redirect()->away(trim((string) $portfolio->site_url), 302);
        }
        $portfolio->increment('view_count');
        $portfolio->load(['featureDevelopments', 'reviews']);
        $canonicalUrl = strtok(url()->current(), '?');
        $primaryCategory = $portfolio->category ?: ($portfolio->categories[0] ?? '');

        // 목록 썸네일과 동일: thumbnail_image 우선(없을 때만 상단 히어로 이미지)
        $portfolioImagePath = $portfolio->thumbnail_image ?: $portfolio->top_image;
        $ogImageAbsolute = $portfolioImagePath ? url(Storage::url($portfolioImagePath)) : null;

        return view('portfolio.view', compact(
            'gNum',
            'sNum',
            'gName',
            'sName',
            'gSlug',
            'page',
            'portfolio',
            'canonicalUrl',
            'primaryCategory',
            'ogImageAbsolute'
        ));
    }

    public function blog_list(Request $request, BlogService $blogService, ?string $blogCategoryPath = null)
    {
        $gNum = '04';
        $sNum = '01';
        $gName = 'Blog';
        $sName = '블로그';
        $gSlug = 'blog';

        if ($blogCategoryPath !== null && $blogCategoryPath !== '') {
            $category = BlogPost::categoryKeyFromUrlPath($blogCategoryPath) ?? abort(404);
        } else {
            $category = (string) $request->input('category', '');
            if ($category !== '') {
                $canonicalPath = BlogPost::urlPathFromCategoryKey($category);
                if ($canonicalPath !== null) {
                    return redirect()->route('blog.blog_list_category', ['blogCategoryPath' => $canonicalPath], 301)
                        ->withQueryString(array_filter(['keyword' => $request->input('keyword')]));
                }
            }
        }

        $keyword = (string) $request->input('keyword', '');

        $posts = $blogService->getList($category, $keyword);
        $featuredPost = $blogService->getFeaturedPost($category);
        $listItems = [];
        foreach (array_values($posts->items()) as $index => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => (($posts->currentPage() - 1) * $posts->perPage()) + $index + 1,
                'name' => $item->title,
                'url' => route('blog.blog_view', $item),
            ];
        }
        $featuredExcerpt = null;
        if ($featuredPost) {
            $leadPlain = trim(strip_tags((string) ($featuredPost->lead_content ?? '')));
            if ($leadPlain !== '') {
                $featuredExcerpt = Str::limit($leadPlain, 220);
            } else {
                $firstSection = $featuredPost->sections()->with('items')->first();
                $firstItem = $firstSection?->items->first();
                $featuredExcerpt = Str::limit(strip_tags((string) ($firstItem->content ?? '')), 220);
            }
        }

        return view('blog.index', compact(
            'gNum',
            'sNum',
            'gName',
            'sName',
            'gSlug',
            'posts',
            'featuredPost',
            'category',
            'keyword',
            'listItems',
            'featuredExcerpt',
            'blogCategoryPath'
        ));
    }

    public function blog_view(BlogService $blogService, BlogPost $blogPost)
    {
        $gNum = '04';
        $sNum = '01';
        $gName = 'Blog';
        $page = 'view';
        $gSlug = 'blog';

        $post = $blogPost;
        if (! $post->is_published) {
            abort(404);
        }
        $post->increment('view_count');
        $post->load(['sections.items']);

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
        $tocSections = $post->sections->filter(fn ($section) => ! empty($section->subtitle))->values();
        $blogFaqItems = $this->faqPublicService->forIdsOrdered($post->faq_board_post_ids);

        $postCategoryPath = BlogPost::urlPathFromCategoryKey((string) $post->category);
        $blogListUrl = $postCategoryPath !== null
            ? route('blog.blog_list_category', ['blogCategoryPath' => $postCategoryPath])
            : route('blog.blog_list');

        $metaDescription = $this->blogPostMetaDescription($post);
        $ogImageAbsolute = $heroImage ? url($heroImage) : null;

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
            'tocSections',
            'blogFaqItems',
            'blogListUrl',
            'metaDescription',
            'ogImageAbsolute'
        ));
    }

    /**
     * 에디터 HTML → 공유·메타용 평문 (엔티티 디코딩, 블록 태그 경계 공백 보정).
     */
    private function plainTextFromHtml(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }
        // 태그가 붙은 경우 단어가 이어지는 것 방지: </h3><p>닷컴 → 공백 삽입 후 태그 제거
        $normalized = preg_replace('/>\s*</', '> <', $html);
        $text = strip_tags($normalized);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * 공유·검색용 메타 설명 (본문 앞단 우선, 최대 약 160자).
     */
    private function blogPostMetaDescription(BlogPost $post): string
    {
        $manual = trim((string) ($post->meta_description ?? ''));
        if ($manual !== '') {
            return Str::limit($manual, 500, '');
        }

        $lead = $this->plainTextFromHtml($post->lead_content ?? null);
        if ($lead !== '') {
            return Str::limit($lead, 160, '');
        }

        $firstSection = $post->sections->first();
        $firstItem = $firstSection?->items->first();
        if ($firstItem !== null) {
            $text = $this->plainTextFromHtml($firstItem->content ?? null);
            if ($text !== '') {
                return Str::limit($text, 160, '');
            }
        }

        return Str::limit($post->title, 160, '');
    }

    public function blog_event(Request $request, BlogPost $blogPost, BlogService $blogService): JsonResponse
    {
        if (! $blogPost->is_published) {
            abort(404);
        }

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
        if (! $blogPost->is_published) {
            abort(404);
        }

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
        $contactFormToken = (string) Str::uuid();
        $contactFormTs = now()->timestamp;
        session([
            'contact_form_token' => $contactFormToken,
            'contact_form_ts' => $contactFormTs,
        ]);

        return view('contact.index', compact('gNum', 'sNum', 'gName', 'sName', 'gSlug', 'contactFormToken', 'contactFormTs'));
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

    public function mailform()
    {
        $gNum = '99';
        $gName = '메일폼';
        $gSlug = 'mailform';

        return view('mailform.mailform', compact('gNum', 'gName', 'gSlug'));
    }
}
