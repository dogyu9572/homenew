<?php

namespace App\Services;

use App\Models\Portfolio;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class AssistantProjectCatalogService
{
    public function search(string $query, int $limit = 3): array
    {
        $keywords = $this->keywords($query);
        $wantsProjectCards = $this->wantsProjectCards($query);
        if ($keywords === []) {
            return $wantsProjectCards
                ? $this->broadProjectCards($query, $limit)
                : [];
        }

        $rows = $this->portfolioRows();
        $portfolioIndex = $this->portfolioIndex();
        $projects = $this->projectBlocks($rows, $portfolioIndex);
        $scored = [];
        $preferSignup = $this->preferSignup($keywords);
        $specificKeywords = $this->specificKeywords($query);
        $requiredKeywords = $this->requiredKeywords($query);

        foreach ($projects as $project) {
            $score = $this->score($project, $keywords, $preferSignup);
            if ($score <= 0) {
                continue;
            }
            if ($specificKeywords !== [] && ! $this->matchesAnyKeyword($project, $specificKeywords)) {
                continue;
            }
            if ($requiredKeywords !== [] && ! $this->matchesAllKeywords($project, $requiredKeywords)) {
                continue;
            }
            if ($preferSignup && ! $this->hasSignupFeature($project)) {
                continue;
            }
            if ($wantsProjectCards && ! (bool) ($project['has_portfolio'] ?? false)) {
                continue;
            }

            $project['score'] = $score;
            $project['snippet'] = $this->snippet($project['content'], $this->snippetKeywords($query, $keywords), $preferSignup)
                ?: $project['title'] . ' 프로젝트에서 관련 기능이 확인되었습니다.';
            $scored[] = $project;
        }

        usort($scored, static function (array $a, array $b): int {
            return [(int) $b['has_portfolio'], $b['score'], $b['number']] <=> [(int) $a['has_portfolio'], $a['score'], $a['number']];
        });

        if ($scored === [] && $wantsProjectCards) {
            return $this->broadProjectCards($query, $limit);
        }

        return array_map(
            fn(array $project): array => $this->toCard($project),
            array_slice($scored, 0, $limit)
        );
    }

    public function shouldExposeProjectCards(string $query): bool
    {
        return $this->wantsProjectCards($query);
    }

    public function portfolioAnalysisContext(string $query): string
    {
        if (! $this->wantsPortfolioAnalysis($query)) {
            return '';
        }

        $projects = $this->projectBlocks($this->portfolioRows(), [
            'all' => [],
            'by_domain' => [],
            'by_title' => [],
        ]);
        if ($projects === []) {
            return '';
        }

        $systemTypes = $this->countProjectSignals($projects, $this->systemTypeSignals());
        $frameworks = $this->countProjectSignals($projects, $this->frameworkSignals());
        $databases = $this->countProjectSignals($projects, $this->databaseSignals());
        $features = $this->countProjectSignals($projects, $this->featureSignals());

        return implode("\n", array_filter([
            '<portfolio_analysis>',
            sprintf('분석 대상: %s 폴더의 기능 명세 MD 기준, 접속 불가/기능 명세 작성 불가 프로젝트 제외 후 총 %d개 프로젝트.', $this->analysisPath(), count($projects)),
            '주의: 프레임워크와 DB는 기능 명세에 명시된 기술명만 집계했습니다. 명시되지 않은 기술 스택은 추정하지 말고 "자료에 명시 부족"이라고 답하세요.',
            $this->formatRanking('시스템 유형 TOP', $systemTypes, 8),
            $this->formatRanking('프레임워크/플랫폼 명시 TOP', $frameworks, 8),
            $this->formatRanking('DB 제품 명시 TOP', $databases, 8),
            $this->formatRanking('구현 기능 TOP', $features, 12),
            '</portfolio_analysis>',
        ]));
    }

    private function keywords(string $query): array
    {
        $normalized = mb_strtolower(trim($query));
        $keywords = [];

        if (preg_match('/회원\s*가입|회원가입|가입/u', $normalized)) {
            $keywords = array_merge($keywords, [
                '회원가입',
                '회원 가입',
                '신규 회원',
                '신규회원',
                '계정 생성',
                '회원 로그인 및 가입',
                '회원 관련 서비스',
            ]);
        }

        if (str_contains($normalized, '로그인')) {
            $keywords = array_merge($keywords, ['로그인', '회원 인증', '마이페이지']);
        }

        if (str_contains($normalized, '예약')) {
            $keywords = array_merge($keywords, ['예약', '신청', '일정']);
        }

        if (str_contains($normalized, '결제')) {
            $keywords = array_merge($keywords, ['결제', '주문', '구매']);
        }

        if (str_contains($normalized, '검색')) {
            $keywords = array_merge($keywords, ['검색', '통합검색']);
        }

        if ($this->containsAny($normalized, ['쇼핑몰', '자사몰', '커머스'])) {
            $keywords = array_merge($keywords, ['쇼핑몰']);
        }

        $stopWords = [
            '있는',
            '프로젝트',
            '사이트',
            '홈페이지',
            '알려줘',
            '보여줘',
            '추천',
            '관련',
            '사례',
            '어떤',
            '있나요',
            '있어',
            '구축한',
            '포트폴리오',
        ];
        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        foreach ($tokens as $token) {
            if (mb_strlen($token) < 2 || in_array($token, $stopWords, true)) {
                continue;
            }

            $keywords[] = $token;
        }

        return array_values(array_unique($keywords));
    }

    private function specificKeywords(string $query): array
    {
        $normalized = mb_strtolower(trim($query));
        $generic = [
            '있는',
            '프로젝트',
            '사이트',
            '홈페이지',
            '알려줘',
            '보여줘',
            '추천',
            '관련',
            '사례',
            '어떤',
            '있나요',
            '있어',
            '자세히',
            '좀',
            '기능',
            '구축',
            '구축한',
            '제작',
            '리뉴얼',
            '포트폴리오',
            '쇼핑몰',
            '회원',
            '회원가입',
            '가입',
            '로그인',
            '예약',
            '결제',
            '검색',
            '공공기관',
            '병원',
            '의료',
            '학회',
            '협회',
            '대학',
            '학원',
            '대기업',
            '일반',
        ];

        $tokens = preg_split('/[^\p{L}\p{N}]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $keywords = [];
        foreach ($tokens as $token) {
            if (mb_strlen($token) < 2 || in_array($token, $generic, true)) {
                continue;
            }

            $keywords[] = $token;
        }

        return array_values(array_unique($keywords));
    }

    private function requiredKeywords(string $query): array
    {
        $normalized = mb_strtolower(trim($query));
        $keywords = [];
        foreach (['쇼핑몰', '공공기관', '병원', '의료', '학회', '협회', '대학', '학원'] as $keyword) {
            if (str_contains($normalized, $keyword)) {
                $keywords[] = $keyword;
            }
        }

        return array_values(array_unique($keywords));
    }

    private function wantsPortfolioAnalysis(string $query): bool
    {
        $normalized = mb_strtolower(trim($query));
        $scopeSignals = ['전체', '귀사', '포트폴리오', '수백', '전부', '모든'];
        $analysisSignals = ['분석', '통계', '가장 많이', '많이 구축', '많이 사용', '많이 구현', '프레임워크', 'db', '데이터베이스'];

        return $this->containsAny($normalized, $scopeSignals)
            && $this->containsAny($normalized, $analysisSignals);
    }

    private function wantsProjectCards(string $query): bool
    {
        $normalized = mb_strtolower(trim($query));

        return $this->containsAny($normalized, ['포트폴리오', '프로젝트', '구축 사례', '레퍼런스', '구축한', '사례'])
            && $this->containsAny($normalized, ['알려', '보여', '추천', '궁금', '확인', '소개', '있', '핵심', '기능', '참고']);
    }

    private function broadProjectCards(string $query, int $limit): array
    {
        $projects = $this->projectBlocks($this->portfolioRows(), $this->portfolioIndex());
        $normalized = mb_strtolower(trim($query));
        $shoppingOnly = $this->containsAny($normalized, ['쇼핑몰', '자사몰', '커머스']);

        $projects = array_values(array_filter($projects, function (array $project) use ($shoppingOnly): bool {
            if (! (bool) ($project['has_portfolio'] ?? false)) {
                return false;
            }
            if (! $shoppingOnly) {
                return true;
            }

            return $this->matchesAnyKeyword($project, ['쇼핑몰', '자사몰', '커머스', '상품', '구매', '주문', '장바구니']);
        }));

        usort($projects, static function (array $a, array $b): int {
            return [$b['number'], $b['title']] <=> [$a['number'], $a['title']];
        });

        return array_map(
            fn(array $project): array => $this->toCard([
                ...$project,
                'snippet' => $this->snippet($project['content'], ['쇼핑몰', '구매', '주문', '상품'], false)
                    ?: $project['title'] . ' 프로젝트에서 확인된 주요 기능을 MD 기준으로 정리했습니다.',
            ]),
            array_slice($projects, 0, $limit)
        );
    }

    private function containsAny(string $value, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($value, mb_strtolower($needle))) {
                return true;
            }
        }

        return false;
    }

    private function portfolioRows(): array
    {
        $path = $this->analysisPath('HK_portfolios.csv');
        if (! is_readable($path)) {
            return [];
        }

        $rows = [];
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return [];
        }

        $header = fgetcsv($handle);
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header ?: [], $row);
            if (! is_array($data)) {
                continue;
            }

            $url = trim((string) ($data['site_url'] ?? ''));
            $domain = $this->normalizeDomain($url);
            $rows[] = [
                'number' => (int) ($data['번호'] ?? 0),
                'category' => trim((string) ($data['순서'] ?? '')),
                'title' => trim((string) ($data['제목'] ?? '')),
                'url' => $url,
                'domain' => $domain,
            ];
        }
        fclose($handle);

        return $rows;
    }

    private function projectBlocks(array $rows, array $portfolioIndex): array
    {
        $files = glob($this->analysisPath('*.md')) ?: [];
        $projects = [];

        foreach ($files as $file) {
            if (str_contains(basename($file), '접속불가')) {
                continue;
            }

            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            preg_match_all('/^#\s+(.+)$/mu', $content, $matches, PREG_OFFSET_CAPTURE);
            $headings = $matches[1] ?? [];
            foreach ($headings as $index => $headingMatch) {
                $heading = trim($headingMatch[0]);
                if ($heading === '' || str_contains($heading, '사이트 기능 명세서')) {
                    continue;
                }

                $start = $headingMatch[1] + strlen($headingMatch[0]);
                $end = isset($headings[$index + 1]) ? $headings[$index + 1][1] : strlen($content);
                $block = trim(substr($content, $start, max(0, $end - $start)));
                if ($block === '') {
                    continue;
                }
                if ($this->isUnavailableProject($heading, $block)) {
                    continue;
                }

                $domain = $this->domainFromHeading($heading);
                $row = $this->matchRow($rows, $heading, $domain);
                $title = $row['title'] ?? $this->titleFromHeading($heading);
                $url = $row['url'] ?? $this->urlFromDomain($domain);
                $portfolio = $this->matchPortfolio($portfolioIndex, $title, $domain, $url)
                    ?? $this->knownPortfolio($title, $domain, $url);
                $portfolioHref = (string) ($portfolio['href'] ?? '');

                $projects[] = [
                    'number' => (int) ($row['number'] ?? 0),
                    'category' => (string) ($portfolio['category'] ?? $row['category'] ?? ''),
                    'title' => (string) ($portfolio['title'] ?? $title),
                    'url' => (string) ($portfolioHref !== '' ? $portfolioHref : $this->portfolioListUrl($title)),
                    'external_url' => $url,
                    'portfolio_image' => (string) ($portfolio['image'] ?? ''),
                    'domain' => $domain ?: $this->normalizeDomain($url),
                    'content' => $block,
                    'has_portfolio' => $this->isPortfolioDetailHref($portfolioHref),
                    'source' => basename($file),
                ];
            }
        }

        return $projects;
    }

    private function matchRow(array $rows, string $heading, string $domain): ?array
    {
        $normalizedDomain = $this->normalizeDomain($domain);
        if ($normalizedDomain !== '') {
            foreach ($rows as $row) {
                if ($row['domain'] === $normalizedDomain) {
                    return $row;
                }
            }
        }

        $cleanHeading = mb_strtolower($this->titleFromHeading($heading));
        foreach ($rows as $row) {
            $title = mb_strtolower($row['title']);
            if ($title !== '' && ($cleanHeading !== '' && (str_contains($title, $cleanHeading) || str_contains($cleanHeading, $title)))) {
                return $row;
            }
        }

        return null;
    }

    private function score(array $project, array $keywords, bool $preferSignup): int
    {
        $title = mb_strtolower((string) $project['title']);
        $domain = mb_strtolower((string) $project['domain']);
        $haystack = mb_strtolower(implode("\n", [
            $project['title'],
            $project['category'],
            $project['domain'],
            $project['content'],
        ]));
        $score = 0;

        foreach ($keywords as $keyword) {
            $keyword = mb_strtolower($keyword);
            if ($keyword === '') {
                continue;
            }

            $count = $keyword === '회원 가입'
                ? preg_match_all('/(?<!연)회원\s+가입/u', $haystack)
                : mb_substr_count($haystack, $keyword);
            if ($count > 0) {
                $score += $count * (str_contains($keyword, '회원') || str_contains($keyword, '가입') ? 8 : 3);
            }

            if (mb_strlen($keyword) >= 3 && (str_contains($title, $keyword) || str_contains($domain, $keyword))) {
                $score += 100;
            }
        }

        if ($preferSignup && preg_match_all($this->signupPattern(), $project['content'], $matches)) {
            $score += count($matches[0]) * 30;
        }

        return $score;
    }

    private function matchesAnyKeyword(array $project, array $keywords): bool
    {
        $haystack = mb_strtolower(implode("\n", [
            $project['title'] ?? '',
            $project['category'] ?? '',
            $project['domain'] ?? '',
            $project['external_url'] ?? '',
            $project['content'] ?? '',
        ]));

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($haystack, mb_strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    private function matchesAllKeywords(array $project, array $keywords): bool
    {
        $haystack = mb_strtolower(implode("\n", [
            $project['title'] ?? '',
            $project['category'] ?? '',
            $project['domain'] ?? '',
            $project['external_url'] ?? '',
            $project['content'] ?? '',
        ]));

        foreach ($keywords as $keyword) {
            if ($keyword !== '' && ! str_contains($haystack, mb_strtolower($keyword))) {
                return false;
            }
        }

        return true;
    }

    private function snippet(string $content, array $keywords, bool $preferSignup): string
    {
        $lines = preg_split('/\R/u', $content) ?: [];
        if ($preferSignup) {
            foreach ($lines as $line) {
                $clean = $this->cleanMarkdownLine($line);
                if ($clean !== '' && preg_match($this->signupPattern(), $clean)) {
                    return Str::limit($clean, 126);
                }
            }
        }

        foreach ($lines as $line) {
            if ($this->isMetadataLine($line)) {
                continue;
            }

            $clean = $this->cleanMarkdownLine($line);
            if ($clean === '') {
                continue;
            }

            foreach ($keywords as $keyword) {
                if (mb_stripos($clean, $keyword) !== false) {
                    return Str::limit($clean, 126);
                }
            }
        }

        return '';
    }

    private function snippetKeywords(string $query, array $keywords): array
    {
        $normalized = mb_strtolower(trim($query));
        if ($this->containsAny($normalized, ['쇼핑몰', '자사몰', '커머스'])) {
            $keywords = array_merge($keywords, ['상품', '구매', '주문', '결제', '장바구니', '배송']);
        }

        return array_values(array_unique($keywords));
    }

    private function preferSignup(array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($keyword, '회원') || str_contains($keyword, '가입') || str_contains($keyword, '계정')) {
                return true;
            }
        }

        return false;
    }

    private function hasSignupFeature(array $project): bool
    {
        return preg_match($this->signupPattern(), (string) ($project['content'] ?? '')) === 1;
    }

    private function signupPattern(): string
    {
        return '/회원가입|(?<!연)회원\s+가입|신규\s*회원|계정\s*생성|회원\s*로그인\s*및\s*가입/u';
    }

    private function cleanMarkdownLine(string $line): string
    {
        $line = trim($line);
        if ($this->isMetadataLine($line)) {
            return '';
        }

        $line = trim($line, "|\t ");
        $line = preg_replace('/\s*\|\s*/u', ' - ', $line) ?? $line;
        $line = preg_replace('/`([^`]+)`/u', '$1', $line) ?? $line;
        $line = preg_replace('/\s+/u', ' ', $line) ?? $line;

        return trim($line);
    }

    private function toCard(array $project): array
    {
        return [
            'title' => $project['title'],
            'body' => $project['snippet'],
            'href' => $this->cleanCardUrl((string) $project['url']),
            'image' => $project['portfolio_image'] ?: $this->imageForCategory($project['category']),
            'meta' => $this->projectMeta($project),
            'tags' => $this->featureTags($project),
            'context' => $this->contextSummary($project['content']),
            'has_portfolio' => (bool) ($project['has_portfolio'] ?? false),
            'source' => $project['source'],
        ];
    }

    private function projectMeta(array $project): string
    {
        $category = trim((string) ($project['category'] ?? ''));
        $domain = $this->normalizeDomain((string) ($project['domain'] ?? ''));
        $title = (string) ($project['title'] ?? '');
        $content = (string) ($project['content'] ?? '');
        $detail = $this->industryLabel($domain, $title, $content);

        return implode(' · ', array_filter([$category, $detail]));
    }

    private function industryLabel(string $domain, string $title, string $content): string
    {
        $domainLabels = [
            'ellaboutique.co.kr' => '패션',
            'goldenbrown.co.kr' => 'F&B',
            'buynpium.com' => '뷰티',
            'bestbar.co.kr' => '건자재',
            'home.cosmojin.com' => '여행',
            'ppohome.com' => '아울렛',
        ];
        if ($domain !== '' && isset($domainLabels[$domain])) {
            return $domainLabels[$domain];
        }

        $haystack = mb_strtolower($title . "\n" . $content);
        $rules = [
            '여행' => ['여행', '투어', '관광', 'tour'],
            '패션' => ['패션', '명품', '의류', '신발', '가방', '악세사리'],
            'F&B' => ['식품', '카페', '베이커리', '커피', 'f&b', '납품'],
            '뷰티' => ['뷰티', '화장품', '피부', '미용'],
            '건자재' => ['철근', '건자재', '자재'],
            '레저' => ['캠핑', '레저', '아웃도어'],
            '아울렛' => ['아울렛', '쇼핑센터'],
            '의료' => ['병원', '의료', '메디컬'],
            '교육' => ['교육', '대학', '학원'],
        ];

        foreach ($rules as $label => $needles) {
            foreach ($needles as $needle) {
                if (str_contains($haystack, mb_strtolower($needle))) {
                    return $label;
                }
            }
        }

        return '';
    }

    private function featureTags(array $project): array
    {
        $haystack = mb_strtolower(implode("\n", [
            $project['title'] ?? '',
            $project['category'] ?? '',
            $project['domain'] ?? '',
            $project['snippet'] ?? '',
            $project['content'] ?? '',
        ]));
        $definitions = [
            'ERP' => ['/erp/u', '/전사\s*자원/u'],
            '견적서 발송' => ['/견적서/u'],
            '맞춤형 예약' => ['/맞춤형[^\\n|]*예약/u', '/예약[^\\n|]*맞춤형/u'],
            '차량구좌' => ['/차량\s*구좌/u', '/구좌별/u'],
            'AI 챗봇' => ['/ai\s*챗봇/u', '/챗봇/u'],
            '라이브커머스' => ['/라이브\s*커머스/u'],
            '상품조회' => ['/상품\s*목록/u', '/상품\s*조회/u', '/상품\s*검색/u', '/제품\s*목록/u'],
            '상품상세' => ['/상품\s*상세/u', '/제품\s*상세/u'],
            '장바구니' => ['/장바구니/u'],
            '주문관리' => ['/주문/u', '/발주/u'],
            '예약구매' => ['/예약\s*구매/u', '/선예약/u', '/사전\s*예약/u'],
            '대량주문' => ['/대량\s*구매/u', '/대량\s*주문/u', '/벌크\s*발주/u'],
            '결제' => ['/결제/u', '/에스크로/u'],
            '배송' => ['/배송/u'],
            '안전거래' => ['/안전\s*거래/u', '/에스크로/u'],
            '회원관리' => ['/회원\s*관리/u', '/회원\s*로그인/u', '/회원가입/u', '/회원\s*가입/u', '/계정/u', '/로그인/u'],
            '비회원주문' => ['/비회원\s*주문/u'],
            '마이페이지' => ['/마이페이지/u'],
            '재고관리' => ['/재고/u'],
            '단가표' => ['/단가표/u'],
            '차등가' => ['/차등가/u', '/거래처별/u'],
            '통합' => ['/통합/u'],
            '예약관리' => ['/예약/u'],
            '세금계산서' => ['/세금계산서/u'],
            '문의관리' => ['/문의/u', '/상담/u'],
            '관리자' => ['/관리자/u', '/대시보드/u'],
            '검색' => ['/검색/u'],
            '게시판' => ['/게시판/u', '/공지/u'],
        ];

        $tags = [];
        foreach ($definitions as $tag => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $haystack) === 1) {
                    $normalized = $this->normalizeFeatureTag($tag);
                    if ($normalized !== null && ! in_array($normalized, $tags, true)) {
                        $tags[] = $normalized;
                    }
                    break;
                }
            }
            if (count($tags) >= 3) {
                break;
            }
        }

        return $tags;
    }

    private function normalizeFeatureTag(string $tag): ?string
    {
        $tag = trim(str_replace('#', '', $tag));
        $tag = preg_replace('/\s+/u', ' ', $tag) ?? $tag;
        if ($tag === '') {
            return null;
        }
        if (mb_strlen($tag) <= 6) {
            return $tag;
        }

        $withoutSpaces = str_replace(' ', '', $tag);
        return mb_strlen($withoutSpaces) <= 6 ? $withoutSpaces : null;
    }

    private function cleanCardUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        $protocol = '';
        if (preg_match('/^(https?:)\s*\/\s*\/\s*(.*)$/i', $url, $matches) === 1) {
            $protocol = mb_strtolower($matches[1]) . '//';
            $url = $matches[2];
        }

        $url = preg_replace('/\s*\/\s*/u', '/', $url) ?? $url;
        $url = preg_replace('#/{2,}#', '/', $url) ?? $url;
        $url = preg_replace('/\s*-\s*/u', '-', $url) ?? $url;
        $url = preg_replace('/\s+/u', '', $url) ?? $url;

        return $protocol . $url;
    }

    private function contextSummary(string $content): string
    {
        $lines = preg_split('/\R/u', $content) ?: [];
        $summary = [];

        foreach ($lines as $line) {
            if ($this->isMetadataLine($line)) {
                continue;
            }

            $clean = $this->cleanMarkdownLine($line);
            if ($clean === '' || str_contains($clean, '기능명 - 기능 설명') || str_contains($clean, '기능명 - 설명')) {
                continue;
            }

            $summary[] = $clean;
            if (count($summary) >= 8) {
                break;
            }
        }

        return Str::limit(implode(' / ', $summary), 900);
    }

    private function countProjectSignals(array $projects, array $definitions): array
    {
        $stats = [];
        foreach ($definitions as $label => $patterns) {
            $stats[$label] = [
                'count' => 0,
                'examples' => [],
            ];
        }

        foreach ($projects as $project) {
            $haystack = mb_strtolower(implode("\n", [
                $project['title'] ?? '',
                $project['category'] ?? '',
                $project['domain'] ?? '',
                $project['content'] ?? '',
            ]));

            foreach ($definitions as $label => $patterns) {
                foreach ($patterns as $pattern) {
                    if (preg_match($pattern, $haystack) !== 1) {
                        continue;
                    }

                    $stats[$label]['count']++;
                    if (count($stats[$label]['examples']) < 4) {
                        $stats[$label]['examples'][] = (string) ($project['title'] ?? '');
                    }
                    break;
                }
            }
        }

        $stats = array_filter($stats, static fn(array $item): bool => $item['count'] > 0);
        uasort($stats, static fn(array $a, array $b): int => $b['count'] <=> $a['count']);

        return $stats;
    }

    private function formatRanking(string $title, array $stats, int $limit): string
    {
        if ($stats === []) {
            return "{$title}: 명시 자료 없음";
        }

        $lines = [$title . ':'];
        $rank = 1;
        foreach (array_slice($stats, 0, $limit, true) as $label => $item) {
            $examples = implode(', ', array_filter($item['examples']));
            $lines[] = sprintf('%d. %s - %d개 프로젝트%s', $rank, $label, $item['count'], $examples !== '' ? " (근거 예시: {$examples})" : '');
            $rank++;
        }

        return implode("\n", $lines);
    }

    private function systemTypeSignals(): array
    {
        return [
            '콘텐츠/소개형 홈페이지' => ['/회사\s*소개/u', '/기관\s*소개/u', '/브랜드\s*소개/u', '/사업\s*분야/u', '/인사말/u', '/연혁/u', '/오시는\s*길/u'],
            '게시판/CMS형 사이트' => ['/공지사항/u', '/자료실/u', '/게시판/u', '/보도자료/u', '/뉴스/u', '/faq/u', '/갤러리/u'],
            '회원/마이페이지 기반 서비스' => ['/회원\s*가입/u', '/회원가입/u', '/로그인/u', '/마이페이지/u', '/계정\s*생성/u', '/권한\s*관리/u'],
            '예약/신청/접수 관리 시스템' => ['/예약/u', '/신청/u', '/접수/u', '/승인/u', '/분석\s*의뢰/u', '/자격\s*관리/u', '/교정\s*관리/u'],
            '쇼핑몰/커머스' => ['/쇼핑몰/u', '/상품\s*목록/u', '/상품\s*상세/u', '/장바구니/u', '/주문/u', '/결제/u', '/배송/u', '/구매/u'],
            '교육/LMS/세미나 시스템' => ['/교육/u', '/강좌/u', '/수강/u', '/강의/u', '/세미나/u', '/lms/u'],
            '행사/컨퍼런스 사이트' => ['/행사/u', '/컨퍼런스/u', '/학술대회/u', '/전시/u', '/참가\s*신청/u'],
            '검색/데이터베이스/아카이브' => ['/통합\s*검색/u', '/상세\s*검색/u', '/데이터베이스/u', '/아카이브/u', '/자료\s*조회/u'],
            '관리자/운영 백오피스' => ['/관리자/u', '/대시보드/u', '/통계/u', '/운영\s*관리/u', '/콘텐츠\s*관리/u'],
        ];
    }

    private function frameworkSignals(): array
    {
        return [
            '카페24(쇼핑몰 플랫폼)' => ['/카페24/u', '/cafe24/u'],
            'JavaScript SPA 구조' => ['/javascript\s+spa/u', '/spa\s*구조/u'],
            'Spring' => ['/(?<!springer)\bspring\b/u', '/스프링/u'],
            '전자정부프레임워크' => ['/전자정부\s*프레임워크/u'],
            'Laravel' => ['/laravel/u', '/라라벨/u'],
            'React' => ['/\breact\b/u'],
            'Vue' => ['/\bvue\b/u'],
            'Next.js' => ['/next\.?js/u'],
            'WordPress' => ['/wordpress/u', '/워드프레스/u'],
            'CodeIgniter' => ['/codeigniter/u'],
            '그누보드' => ['/그누보드/u'],
        ];
    }

    private function databaseSignals(): array
    {
        return [
            'MySQL' => ['/mysql/u'],
            'MariaDB' => ['/mariadb/u'],
            'PostgreSQL' => ['/postgresql/u', '/postgres/u'],
            'Oracle' => ['/\boracle\b/u', '/오라클/u'],
            'MSSQL' => ['/mssql/u', '/sql\s*server/u'],
            'MongoDB' => ['/mongodb/u', '/mongo\s*db/u'],
            'Redis' => ['/redis/u'],
            'SQLite' => ['/sqlite/u'],
        ];
    }

    private function featureSignals(): array
    {
        return [
            '공지사항/게시판' => ['/공지사항/u', '/게시판/u', '/보도자료/u', '/뉴스/u', '/자료실/u'],
            '로그인/인증' => ['/로그인/u', '/인증/u', '/권한/u'],
            '회원가입/계정 생성' => ['/회원\s*가입/u', '/회원가입/u', '/신규\s*회원/u', '/계정\s*생성/u'],
            '검색/필터' => ['/검색/u', '/필터/u', '/분류/u'],
            '문의/상담/Contact' => ['/문의/u', '/상담/u', '/contact/u', '/온라인\s*상담/u'],
            '자료 다운로드' => ['/다운로드/u', '/자료\s*다운/u', '/첨부파일/u'],
            '예약/신청/접수' => ['/예약/u', '/신청/u', '/접수/u', '/참가\s*신청/u'],
            '결제/주문/장바구니' => ['/결제/u', '/주문/u', '/장바구니/u', '/구매/u'],
            '마이페이지/이용내역' => ['/마이페이지/u', '/이용\s*내역/u', '/주문\s*내역/u', '/회원정보/u'],
            '상품 목록/상세' => ['/상품\s*목록/u', '/상품\s*상세/u', '/제품\s*목록/u', '/제품\s*상세/u'],
            '갤러리/미디어' => ['/갤러리/u', '/영상/u', '/동영상/u', '/이미지/u', '/미디어/u'],
            'SNS/외부 연동' => ['/sns/u', '/인스타그램/u', '/유튜브/u', '/카카오/u', '/외부\s*연계/u'],
            '관리자/콘텐츠 관리' => ['/관리자/u', '/콘텐츠\s*관리/u', '/운영\s*관리/u', '/대시보드/u'],
            '오시는 길/지도' => ['/오시는\s*길/u', '/찾아오시는\s*길/u', '/지도/u', '/교통/u'],
        ];
    }

    private function isUnavailableProject(string $heading, string $content): bool
    {
        $haystack = $heading . "\n" . $content;
        $blockedPhrases = [
            '기능 명세 작성 불가',
            '기능 명세를 작성할 수 없음',
            '쇼핑몰이 잠시 쉬고 있어요',
            '서비스가 일시 중단',
            '서비스 일시 중단',
            '사이트 미구성',
            '콘텐츠가 배포되지 않은',
            '도메인이 만료',
            '도메인 만료',
            'URL 미제공',
            '실제 콘텐츠를 확인할 수 없음',
        ];

        if (str_contains($heading, '접속 불가')) {
            return true;
        }

        foreach ($blockedPhrases as $phrase) {
            if (str_contains($haystack, $phrase)) {
                return true;
            }
        }

        return false;
    }

    private function isMetadataLine(string $line): bool
    {
        $line = trim($line);

        return $line === ''
            || str_starts_with($line, '#')
            || str_starts_with($line, '>')
            || str_starts_with($line, '|---')
            || str_starts_with($line, '---');
    }

    private function isPortfolioDetailHref(string $href): bool
    {
        if ($href === '') {
            return false;
        }

        $path = (string) parse_url($href, PHP_URL_PATH);

        return (bool) preg_match('#^/portfolio/[^/]+/?$#', $path);
    }

    private function portfolioIndex(): array
    {
        if (! $this->usePortfolioDb()) {
            return [
                'all' => [],
                'by_domain' => [],
                'by_title' => [],
            ];
        }

        try {
            $portfolios = Portfolio::query()
                ->where('is_active', true)
                ->where('is_before_2023', false)
                ->where('is_direct_site_link', false)
                ->get(['id', 'slug', 'title', 'site_url', 'category', 'categories', 'thumbnail_image', 'is_direct_site_link']);
        } catch (Throwable) {
            return [
                'all' => [],
                'by_domain' => [],
                'by_title' => [],
            ];
        }

        $index = [
            'all' => [],
            'by_domain' => [],
            'by_title' => [],
        ];

        foreach ($portfolios as $portfolio) {
            $image = $portfolio->thumbnail_image
                ? url(Storage::url($portfolio->thumbnail_image))
                : '';
            $item = [
                'title' => (string) $portfolio->title,
                'href' => route('portfolio.portfolio_view', ['portfolio' => $portfolio->slug]),
                'category' => (string) ($portfolio->category ?: ($portfolio->categories[0] ?? '')),
                'image' => $image,
            ];

            $index['all'][] = $item;

            $domain = $this->normalizeDomain((string) $portfolio->site_url);
            if ($domain !== '') {
                $index['by_domain'][$domain] = $item;
            }

            $titleKey = $this->normalizeTitle((string) $portfolio->title);
            if ($titleKey !== '') {
                $index['by_title'][$titleKey] = $item;
            }
        }

        return $index;
    }

    private function usePortfolioDb(): bool
    {
        $value = config('services.assistant.use_portfolio_db', true);
        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function matchPortfolio(array $index, string $title, string $domain, string $url): ?array
    {
        foreach (array_filter([$domain, $this->normalizeDomain($url)]) as $candidateDomain) {
            if (isset($index['by_domain'][$candidateDomain])) {
                return $index['by_domain'][$candidateDomain];
            }
        }

        $titleKey = $this->normalizeTitle($title);
        if ($titleKey !== '' && isset($index['by_title'][$titleKey])) {
            return $index['by_title'][$titleKey];
        }

        foreach ($index['all'] as $item) {
            $candidateTitle = $this->normalizeTitle((string) $item['title']);
            if (
                $titleKey !== ''
                && $candidateTitle !== ''
                && (str_contains($candidateTitle, $titleKey) || str_contains($titleKey, $candidateTitle))
            ) {
                return $item;
            }
        }

        return null;
    }

    private function knownPortfolio(string $title, string $domain, string $url): ?array
    {
        $normalizedDomain = $this->normalizeDomain($domain ?: $url);
        $overrides = [
            'buynpium.com' => [
                'title' => '바이앤피움 웹사이트 리뉴얼',
                'href' => url('/portfolio/buynpium'),
                'category' => '쇼핑몰',
                'image' => '',
            ],
            'bestbar.co.kr' => [
                'title' => '한국철근거래소 온라인 쇼핑몰 구축',
                'href' => url('/portfolio/koris-price'),
                'category' => '쇼핑몰',
                'image' => '',
            ],
            'home.cosmojin.com' => [
                'title' => '코스모진 여행사 웹사이트 리뉴얼',
                'href' => url('/portfolio/cosmojin'),
                'category' => '쇼핑몰',
                'image' => '',
            ],
            'ppohome.com' => [
                'title' => '평택 프리미엄 아울렛 웹사이트 구축',
                'href' => url('/portfolio/ppo-outlet-pyeongchang'),
                'category' => '쇼핑몰',
                'image' => '',
            ],
            'ellaboutique.co.kr' => [
                'title' => '엘라부티끄 중고 명품 편집샵 쇼핑몰 구축',
                'href' => url('/portfolio/ellaboutique'),
                'category' => '쇼핑몰',
                'image' => '',
            ],
            'goldenbrown.co.kr' => [
                'title' => '골든브라운 쇼핑몰 구축',
                'href' => url('/portfolio/goldenbrown'),
                'category' => '쇼핑몰',
                'image' => '',
            ],
            'polymer.or.kr' => [
                'title' => '고분자학회 학술대회 생성 플랫폼',
                'href' => url('/portfolio/polymer-society-korea'),
                'category' => '학회/협회',
                'image' => '',
            ],
            'lipid.or.kr' => [
                'title' => '한국지질동맥경화학회',
                'href' => url('/portfolio/lipid'),
                'category' => '학회/협회',
                'image' => '',
            ],
            'ksecho.org' => [
                'title' => '한국심초음파학회',
                'href' => url('/portfolio/ksecho'),
                'category' => '학회/협회',
                'image' => '',
            ],
            'eyedoctor.or.kr' => [
                'title' => '대한안과의사회 웹사이트',
                'href' => url('/portfolio/korean-ophthalmologists-association'),
                'category' => '학회/협회',
                'image' => '',
            ],
            'gaok.or.kr' => [
                'title' => '대한민국시도지사협의회 웹사이트 리뉴얼',
                'href' => url('/portfolio/governors-association-korea'),
                'category' => '학회/협회',
                'image' => '',
            ],
            'sportsmuseum.kspo.or.kr' => [
                'title' => '국립스포츠박물관 홈페이지 구축',
                'href' => url('/portfolio/sportsmuseum'),
                'category' => '공공기관',
                'image' => '',
            ],
            'sivc.keti.re.kr' => [
                'title' => 'KETI 시스템반도체 개발지원센터',
                'href' => url('/portfolio/keti'),
                'category' => '공공기관',
                'image' => '',
            ],
            'hy-run.hanyang.ac.kr' => [
                'title' => '한양대학교 연구장비활용네트워크(HY-RUN)',
                'href' => url('/portfolio/research-equipment-reservation-system'),
                'category' => '공공기관',
                'image' => '',
            ],
            'irf.snu.ac.kr' => [
                'title' => '서울대학교 기초과학공동기기원 리뉴얼',
                'href' => url('/portfolio/seoul-national-university-basic-science-shared-equipment-center'),
                'category' => '공공기관',
                'image' => '',
            ],
            'rfc.sejong.ac.kr' => [
                'title' => '세종대학교 공동기기원 웹사이트 리뉴얼',
                'href' => url('/portfolio/sejonguniversity-research-facilities'),
                'category' => '대학/학원',
                'image' => '',
            ],
            'ia.omron.co.kr' => [
                'title' => '오므론 웹사이트 리뉴얼',
                'href' => url('/portfolio/omron'),
                'category' => '중견/대기업',
                'image' => '',
            ],
            'jeisys.com' => [
                'title' => '제이시스메디컬 웹사이트 리뉴얼',
                'href' => url('/portfolio/jeisys-medical'),
                'category' => '중견/대기업',
                'image' => '',
            ],
            'kyobotrust.co.kr' => [
                'title' => '교보자산신탁 웹사이트 리뉴얼',
                'href' => url('/portfolio/kyobo-trust'),
                'category' => '중견/대기업',
                'image' => '',
            ],
            'koreanaircnd.com' => [
                'title' => '대한항공씨앤디서비스',
                'href' => url('/portfolio/korean-air-cnd'),
                'category' => '중견/대기업',
                'image' => '',
            ],
            'highdentalkorea.com' => [
                'title' => '하이덴탈코리아 통합 홈페이지',
                'href' => url('/portfolio/high-dental-korea'),
                'category' => '병원/의료',
                'image' => '',
            ],
            'nicem.snu.ac.kr' => [
                'title' => '서울대학교 농생명과학공동기기원 리뉴얼',
                'href' => url('/portfolio/seoul-national-university-agriculture-life-sciences-shared-equipment-center'),
                'category' => '공공기관',
                'image' => '',
            ],
        ];

        if ($normalizedDomain !== '' && isset($overrides[$normalizedDomain])) {
            return $overrides[$normalizedDomain];
        }

        $titleKey = $this->normalizeTitle($title);
        foreach ($overrides as $item) {
            $candidateTitle = $this->normalizeTitle((string) $item['title']);
            if ($titleKey !== '' && $candidateTitle !== '' && (str_contains($candidateTitle, $titleKey) || str_contains($titleKey, $candidateTitle))) {
                return $item;
            }
        }

        return null;
    }

    private function imageForCategory(string $category): string
    {
        return match ($category) {
            '쇼핑몰' => asset('images/img_service_how_e01.png'),
            '병원/의료' => asset('images/img_industry_d01.jpg'),
            '공공기관' => asset('images/img_industry_b01.jpg'),
            '학회/협회' => asset('images/img_industry_c01.jpg'),
            '대학/학원' => asset('images/img_industry_e01.jpg'),
            default => asset('images/img_service_how_a01.png'),
        };
    }

    private function titleFromHeading(string $heading): string
    {
        $title = preg_replace('/\([^)]*\)/u', '', $heading) ?? $heading;
        $title = preg_replace('/\s*기능\s*명세서\s*$/u', '', $title) ?? $title;
        $title = preg_replace('/\s+/u', ' ', $title) ?? $title;

        return trim($title);
    }

    private function domainFromHeading(string $heading): string
    {
        if (preg_match('/\(([^)]+)\)/u', $heading, $matches)) {
            return $this->normalizeDomain($matches[1]);
        }

        return '';
    }

    private function normalizeDomain(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $host = parse_url($value, PHP_URL_HOST);
        if ($host === null) {
            $host = preg_replace('/\/.*$/', '', preg_replace('/^https?:\/\//i', '', $value) ?? $value);
        }

        return preg_replace('/^www\./i', '', mb_strtolower((string) $host)) ?? '';
    }

    private function normalizeTitle(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = preg_replace('/\([^)]*\)/u', ' ', $value) ?? $value;
        $value = preg_replace('/\b(web|website|renewal|homepage)\b/i', ' ', $value) ?? $value;
        $value = preg_replace('/웹사이트|홈페이지|리뉴얼|구축|신규|제작|유지보수|온라인|쇼핑몰|시스템/ui', ' ', $value) ?? $value;
        $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    private function portfolioListUrl(string $title): string
    {
        $query = trim($title);

        return $query !== ''
            ? route('portfolio.portfolio_list', ['q' => $query])
            : route('portfolio.portfolio_list');
    }

    private function urlFromDomain(string $domain): string
    {
        return $domain !== '' ? 'https://' . $domain . '/' : '';
    }

    private function analysisPath(string $path = ''): string
    {
        $basePath = trim((string) config('services.assistant.project_analysis_path', ''));
        if ($basePath === '') {
            $basePath = base_path('사이트분석');
        } elseif (! preg_match('/^(?:[A-Za-z]:[\/\\\\]|\/|\\\\)/', $basePath)) {
            $basePath = base_path($basePath);
        }

        return $path === '' ? $basePath : rtrim($basePath, "\\/") . DIRECTORY_SEPARATOR . $path;
    }
}
