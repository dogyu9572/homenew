<?php

namespace App\Console\Commands;

use App\Models\BlogPost;
use App\Models\Portfolio;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';

    protected $description = '사이트맵 XML을 생성합니다.';

    public function handle(): int
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        if ($baseUrl === '') {
            $this->error('APP_URL이 비어 있습니다. .env 설정을 확인해 주세요.');

            return self::FAILURE;
        }

        $xml = new \XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->setIndent(true);
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($this->staticPages() as $page) {
            $this->writeUrl(
                $xml,
                $baseUrl.$page['path'],
                $page['priority'],
                $page['changefreq']
            );
        }

        Portfolio::query()
            ->where('is_active', true)
            ->whereNotNull('slug')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->each(function (Portfolio $portfolio) use ($xml, $baseUrl): void {
                $this->writeUrl(
                    $xml,
                    $baseUrl.route('portfolio.portfolio_view', $portfolio->slug, false),
                    '0.80',
                    'weekly',
                    $portfolio->updated_at
                );
            });

        BlogPost::query()
            ->where('is_published', true)
            ->whereNotNull('slug')
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at'])
            ->each(function (BlogPost $post) use ($xml, $baseUrl): void {
                $this->writeUrl(
                    $xml,
                    $baseUrl.route('blog.blog_view', $post->slug, false),
                    '0.70',
                    'weekly',
                    $post->updated_at
                );
            });

        $xml->endElement();
        $xml->endDocument();

        file_put_contents(public_path('sitemap.xml'), $xml->outputMemory());

        $this->info('sitemap.xml 생성 완료');

        return self::SUCCESS;
    }

    /**
     * @return array<int, array{path: string, priority: string, changefreq: string}>
     */
    private function staticPages(): array
    {
        return [
            ['path' => '/', 'priority' => '1.00', 'changefreq' => 'daily'],
            ['path' => '/about', 'priority' => '0.80', 'changefreq' => 'monthly'],
            ['path' => '/service/homepage-seo-geo', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/service/homepage-development', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/service/website-maintenance', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/service/ecommerce-website-development', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/service/integrated-si-system-development', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/service/mobile-app-development', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/service/ai-solution', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/industries/enterprise', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/industries/academic-association', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/industries/government', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/industries/hospital-medical-website-development', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/industries/university-research-lab-website', 'priority' => '0.80', 'changefreq' => 'weekly'],
            ['path' => '/portfolio', 'priority' => '0.90', 'changefreq' => 'daily'],
            ['path' => '/blog', 'priority' => '0.90', 'changefreq' => 'daily'],
            ['path' => '/contact', 'priority' => '0.70', 'changefreq' => 'monthly'],
            ['path' => '/terms/privacy_policy', 'priority' => '0.40', 'changefreq' => 'yearly'],
        ];
    }

    private function writeUrl(
        \XMLWriter $xml,
        string $loc,
        string $priority,
        string $changefreq,
        ?\DateTimeInterface $lastmod = null
    ): void {
        $xml->startElement('url');
        $xml->writeElement('loc', $loc);
        $xml->writeElement('changefreq', $changefreq);
        $xml->writeElement('priority', $priority);

        if ($lastmod !== null) {
            $xml->writeElement('lastmod', $lastmod->format(\DateTimeInterface::ATOM));
        }

        $xml->endElement();
    }
}
