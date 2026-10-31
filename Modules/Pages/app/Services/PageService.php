<?php

namespace Modules\Pages\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageTranslation;

class PageService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Page $page = null, ?Admin $actor = null): Page
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $page, $actor): Page {
            $language = Language::query()->whereKey($data['lang_id'] ?? null)->where('is_active', true)->firstOrFail();
            $page = $page === null ? new Page : Page::query()->lockForUpdate()->findOrFail($page->id);
            $translation = $page->translations()->where('lang_id', $language->id)->first() ?? new PageTranslation(['lang_id' => $language->id]);
            $isNew = ! $page->exists;
            $pageData = [
                'page_category_id' => $data['page_category_id'] ?? null,
                ...$this->contentMedia->prepare($page, $data, $this->contentMedia->definition(), $actor),
                'status' => $data['status'],
                'sort_order' => $data['sort_order'],
            ];
            $legacyPrimary = $language->code === PagesLanguageService::PrimaryCode;
            if ($legacyPrimary) {
                $pageData += [
                    'title' => $data['title'], 'slug' => $data['slug'],
                    'meta_title' => $data['meta_title'] ?? null, 'meta_keywords' => $data['meta_keywords'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null, 'short_description' => $data['short_description'] ?? null,
                    'content' => $this->sanitizeContent($data['content'] ?? ''),
                ];
            } elseif ($isNew) {
                $pageData += ['title' => 'Page', 'slug' => 'pending-page', 'content' => null];
            }
            $page->fill($pageData)->save();
            if ($isNew && ! $legacyPrimary) {
                $page->forceFill(['title' => 'Page '.$page->id, 'slug' => 'page-'.$page->id])->save();
            }
            $translation->fill([
                'page_id' => $page->id,
                'lang_id' => $language->id,
                'scope_id' => $page->page_category_id ?? 0,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'meta_title' => $data['meta_title'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'content' => $this->sanitizeContent($data['content'] ?? ''),
            ])->save();
            $translation->extra_fields = $this->values->prepare(
                $this->features->form('Pages', 'page')['fields'],
                $data,
                $translation->extra_fields ?? [],
                'pages/'.$page->id.'/translations/'.$language->id.'/features',
                $files,
                $actor,
            );
            $translation->save();

            // Preserve the legacy columns for existing consumers while Turkish remains the primary locale.
            if ($legacyPrimary) {
                $page->fill([
                    'title' => $data['title'], 'slug' => $data['slug'],
                    'meta_title' => $data['meta_title'] ?? null, 'meta_keywords' => $data['meta_keywords'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null, 'short_description' => $data['short_description'] ?? null,
                    'content' => $translation->content, 'extra_fields' => $translation->extra_fields,
                ])->save();
            }

            $this->contentMedia->syncUsage($page, 'image', 'Sayfa / '.$translation->title, 'admin.pages.edit');
            $this->values->syncMediaUsages($this->features->form('Pages', 'page')['fields'], $translation, $this->media, 'Sayfa / '.$translation->title, 'admin.pages.edit');

            return $page->load(['translations.language']);
        });
    }

    public function delete(Page $page): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($page): void {
            $page = Page::query()->with('translations')->lockForUpdate()->findOrFail($page->id);
            foreach ($this->features->form('Pages', 'page')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    foreach ($page->translations as $translation) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'pages/'.$page->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
            }
            $this->media->detachUsages($page);
            foreach ($page->translations as $translation) {
                $this->media->detachUsages($translation);
            }
            $page->delete();
        });
    }

    public function sanitizeContent(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}
