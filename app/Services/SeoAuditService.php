<?php

namespace App\Services;

use App\Models\SeoAudit;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SeoAuditService
{
    public function audit(string $url, int $tenantId): SeoAudit
    {
        $url = $this->normalizeUrl($url);
        $checks = [];
        $recommendations = [];
        $score = 100;
        $meta = [];

        try {
            $response = Http::timeout(20)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; CRM-SEO-Audit/1.0)',
            ])->get($url);

            $html = $response->body();
            $statusCode = $response->status();

            $checks[] = $this->check('HTTP Status', $statusCode === 200, $statusCode === 200 ? "OK ({$statusCode})" : "Failed ({$statusCode})", 15);
            if ($statusCode !== 200) {
                $score -= 15;
                $recommendations[] = 'Fix HTTP errors — page should return status 200.';
            }

            $title = $this->extract($html, '/<title[^>]*>(.*?)<\/title>/is');
            $titleLen = strlen(trim(strip_tags($title)));
            $titleOk = $titleLen >= 30 && $titleLen <= 60;
            $checks[] = $this->check('Title Tag', ! empty(trim($title)), $titleOk ? "Good ({$titleLen} chars)" : ($titleLen ? "Length: {$titleLen} (ideal 30-60)" : 'Missing'), 10);
            if (! $titleOk) {
                $score -= 10;
                $recommendations[] = 'Add a title tag between 30-60 characters with primary keyword.';
            }
            $meta['title'] = trim(strip_tags($title));

            $desc = $this->extractMeta($html, 'description');
            $descLen = strlen($desc);
            $descOk = $descLen >= 120 && $descLen <= 160;
            $checks[] = $this->check('Meta Description', ! empty($desc), $descOk ? "Good ({$descLen} chars)" : ($descLen ? "Length: {$descLen} (ideal 120-160)" : 'Missing'), 10);
            if (! $descOk) {
                $score -= 10;
                $recommendations[] = 'Add meta description between 120-160 characters.';
            }
            $meta['description'] = $desc;

            $h1Count = preg_match_all('/<h1[^>]*>/i', $html);
            $h1Ok = $h1Count === 1;
            $checks[] = $this->check('H1 Tag', $h1Count > 0, $h1Ok ? 'Exactly 1 H1' : "Found {$h1Count} H1 tags (should be 1)", 10);
            if (! $h1Ok) {
                $score -= 10;
                $recommendations[] = 'Use exactly one H1 tag per page with main keyword.';
            }

            $hasViewport = Str::contains(strtolower($html), 'name="viewport"');
            $checks[] = $this->check('Mobile Viewport', $hasViewport, $hasViewport ? 'Present' : 'Missing', 10);
            if (! $hasViewport) {
                $score -= 10;
                $recommendations[] = 'Add viewport meta tag for mobile responsiveness.';
            }

            $hasCanonical = Str::contains(strtolower($html), 'rel="canonical"');
            $checks[] = $this->check('Canonical URL', $hasCanonical, $hasCanonical ? 'Present' : 'Missing', 5);
            if (! $hasCanonical) {
                $score -= 5;
                $recommendations[] = 'Add canonical link tag to avoid duplicate content.';
            }

            $imgCount = preg_match_all('/<img[^>]+>/i', $html, $imgMatches);
            $missingAlt = 0;
            foreach ($imgMatches[0] ?? [] as $img) {
                if (! preg_match('/alt\s*=\s*["\'][^"\']+["\']/i', $img)) {
                    $missingAlt++;
                }
            }
            $altOk = $imgCount === 0 || $missingAlt === 0;
            $checks[] = $this->check('Image Alt Tags', $altOk, $altOk ? "All {$imgCount} images have alt" : "{$missingAlt}/{$imgCount} missing alt", 10);
            if (! $altOk) {
                $score -= 10;
                $recommendations[] = 'Add descriptive alt text to all images.';
            }

            $hasOg = Str::contains(strtolower($html), 'property="og:');
            $checks[] = $this->check('Open Graph Tags', $hasOg, $hasOg ? 'Present' : 'Missing', 5);
            if (! $hasOg) {
                $score -= 5;
                $recommendations[] = 'Add Open Graph tags for social media sharing.';
            }

            $hasSchema = Str::contains(strtolower($html), 'application/ld+json');
            $checks[] = $this->check('Schema Markup', $hasSchema, $hasSchema ? 'JSON-LD found' : 'Missing', 10);
            if (! $hasSchema) {
                $score -= 10;
                $recommendations[] = 'Add Schema.org JSON-LD structured data.';
            }

            $wordCount = str_word_count(strip_tags($html));
            $contentOk = $wordCount >= 300;
            $checks[] = $this->check('Content Length', $contentOk, "{$wordCount} words".($contentOk ? '' : ' (min 300 recommended)'), 10);
            if (! $contentOk) {
                $score -= 10;
                $recommendations[] = 'Increase page content to at least 300 words with relevant keywords.';
            }
            $meta['word_count'] = $wordCount;

            $loadTime = $response->transferStats?->getTransferTime() ?? 0;
            $speedOk = $loadTime < 3;
            $checks[] = $this->check('Load Time', $speedOk, round($loadTime, 2).'s'.($speedOk ? ' (Good)' : ' (Slow)'), 5);
            if (! $speedOk) {
                $score -= 5;
                $recommendations[] = 'Optimize page speed — compress images, enable caching.';
            }
            $meta['load_time'] = round($loadTime, 2);

        } catch (\Exception $e) {
            $score = 0;
            $checks[] = $this->check('Page Fetch', false, 'Error: '.$e->getMessage(), 100);
            $recommendations[] = 'Could not fetch URL. Check if site is online and accessible.';
        }

        $score = max(0, min(100, $score));

        return SeoAudit::create([
            'tenant_id' => $tenantId,
            'url' => $url,
            'score' => $score,
            'checks' => $checks,
            'recommendations' => $recommendations,
            'meta' => $meta,
            'audited_by' => auth()->id(),
        ]);
    }

    protected function normalizeUrl(string $url): string
    {
        if (! str_starts_with($url, 'http')) {
            $url = 'https://'.$url;
        }

        return $url;
    }

    protected function extract(string $html, string $pattern): string
    {
        preg_match($pattern, $html, $m);

        return trim($m[1] ?? '');
    }

    protected function extractMeta(string $html, string $name): string
    {
        preg_match('/<meta[^>]+name=["\']'.preg_quote($name, '/').'["\'][^>]+content=["\']([^"\']*)["\'][^>]*>/i', $html, $m1);
        if (! empty($m1[1])) {
            return trim($m1[1]);
        }
        preg_match('/<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']'.preg_quote($name, '/').'["\'][^>]*>/i', $html, $m2);

        return trim($m2[1] ?? '');
    }

    protected function check(string $name, bool $passed, string $detail, int $weight): array
    {
        return [
            'name' => $name,
            'passed' => $passed,
            'detail' => $detail,
            'weight' => $weight,
        ];
    }
}
