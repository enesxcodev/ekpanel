<?php

namespace App\Services\Settings;

use Illuminate\Database\Eloquent\Model;

class SeoMetadata
{
    public function __construct(private SettingsService $settings) {}

    /** @param array<string, mixed> $data @return array<string, mixed> */
    public function build(array $data = []): array
    {
        $siteName = $this->value('site.title', (string) config('app.name'));
        $siteUrl = url('/');
        $record = $data['page'] ?? $data['content'] ?? $data['form'] ?? null;
        $record = $record instanceof Model ? $record : null;
        $recordTitle = $record ? trim((string) ($record->getAttribute('title') ?: $record->getAttribute('name'))) : '';
        $metaTitle = $record ? trim((string) $record->getAttribute('meta_title')) : '';
        $recordTitle = $metaTitle !== '' ? $metaTitle : ($recordTitle !== '' ? $recordTitle.' · '.$siteName : '');
        $title = $recordTitle !== '' ? $recordTitle : ($data['seoTitle'] ?? $this->value('site.meta_title', $siteName));
        $description = $record ? trim(strip_tags((string) ($record->getAttribute('meta_description') ?: $record->getAttribute('short_description')))) : '';
        $description = $description !== '' ? $description : $this->value('site.meta_description', $this->value('site.description'));
        $keywords = ($record ? $record->getAttribute('meta_keywords') : null) ?: $this->value('site.meta_keywords');
        $logo = $this->absoluteUrl($this->settings->mediaUrl('site.logo'));
        $image = $this->absoluteUrl($record?->getAttribute('image_url'))
            ?? $this->absoluteUrl($this->settings->mediaUrl('seo.og_image')) ?? $logo;
        $ogTitle = $record ? $title : $this->value('seo.og_title', $title);
        $ogDescription = $record ? $description : $this->value('seo.og_description', $description);
        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $siteUrl.'/#organization',
            'name' => $this->value('site.company_name', $siteName),
            'legalName' => $this->value('seo.legal_name'),
            'url' => $siteUrl,
            'logo' => $logo,
            'address' => $this->value('contact.address'),
            'telephone' => $this->value('contact.phone'),
            'email' => $this->value('contact.email'),
            'sameAs' => array_values(array_filter(array_map(
                fn (string $network): ?string => $this->absoluteUrl($this->value('social.'.$network), false),
                ['linkedin', 'facebook', 'twitter', 'instagram', 'pinterest', 'youtube'],
            ))),
        ], fn (mixed $value): bool => $value !== null && $value !== '' && $value !== []);
        $graph = [
            '@context' => 'https://schema.org',
            '@graph' => [
                $organization,
                ['@type' => 'WebSite', '@id' => $siteUrl.'/#website', 'url' => $siteUrl, 'name' => $siteName, 'publisher' => ['@id' => $organization['@id']]],
            ],
        ];
        $card = $this->value('seo.twitter_card', 'summary_large_image');
        $account = $this->value('seo.twitter_site');
        $twitterImage = $record ? $image : ($this->absoluteUrl($this->settings->mediaUrl('seo.twitter_image')) ?? $image);

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => $keywords,
            'canonical' => request()->url(),
            'og' => array_filter([
                'type' => isset($data['content']) ? 'article' : 'website',
                'url' => request()->url(),
                'site_name' => $this->value('seo.og_site_name', $siteName),
                'title' => $ogTitle,
                'description' => $ogDescription,
                'image' => $image,
                'image:alt' => $image ? ($record ? $title : $this->value('seo.og_image_alt', $title)) : null,
            ], fn (mixed $value): bool => $value !== null && $value !== ''),
            'twitter' => array_filter([
                'card' => in_array($card, ['summary', 'summary_large_image'], true) ? $card : 'summary_large_image',
                'site' => preg_match('/^@?[A-Za-z0-9_]{1,15}$/', $account) ? '@'.ltrim($account, '@') : null,
                'title' => $record ? $title : $this->value('seo.twitter_title', $ogTitle),
                'description' => $record ? $description : $this->value('seo.twitter_description', $ogDescription),
                'image' => $twitterImage,
                'image:alt' => $twitterImage ? ($record ? $title : $this->value('seo.twitter_image_alt', $this->value('seo.og_image_alt', $title))) : null,
            ], fn (mixed $value): bool => $value !== null && $value !== ''),
            'jsonLd' => json_encode($graph, JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    private function value(string $key, string $fallback = ''): string
    {
        $value = trim((string) $this->settings->get($key, ''));

        return $value !== '' ? $value : $fallback;
    }

    private function absoluteUrl(?string $value, bool $allowRelative = true): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $value = trim($value);
        if (filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower((string) parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true)) {
            return $value;
        }
        if ($allowRelative && ! str_starts_with($value, '//') && ! parse_url($value, PHP_URL_SCHEME)) {
            return url($value);
        }

        return null;
    }
}
