<?php

namespace Modules\Pages\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Pages\Models\Page;

class PageService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Page $page = null, ?Admin $actor = null): Page
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $page, $actor): Page {
            $page = $page === null ? new Page : Page::query()->lockForUpdate()->findOrFail($page->id);
            $page->fill([
                'page_category_id' => $data['page_category_id'] ?? null,
                'title' => $data['title'],
                'meta_title' => $data['meta_title'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'content' => $this->sanitizeContent($data['content'] ?? ''),
                ...$this->contentMedia->prepare($page, $data, $this->contentMedia->definition(), $actor),
                'slug' => $data['slug'],
                'status' => $data['status'],
                'sort_order' => $data['sort_order'],
            ])->save();
            $page->extra_fields = $this->values->prepare(
                $this->features->form('Pages', 'page')['fields'],
                $data,
                $page->extra_fields ?? [],
                'pages/'.$page->id.'/features',
                $files,
                $actor,
            );
            $page->save();

            $this->contentMedia->syncUsage($page, 'image', 'Sayfa / '.$page->title, 'admin.pages.edit');
            $this->values->syncMediaUsages($this->features->form('Pages', 'page')['fields'], $page, $this->media, 'Sayfa / '.$page->title, 'admin.pages.edit');

            return $page;
        });
    }

    public function delete(Page $page): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($page): void {
            $page = Page::query()->lockForUpdate()->findOrFail($page->id);
            foreach ($this->features->form('Pages', 'page')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $page->extra_fields[$key] ?? null, 'pages/'.$page->id.'/features', true);
                }
            }
            $this->media->detachUsages($page);
            $page->delete();
        });
    }

    public function sanitizeContent(string $html): string
    {
        return $this->sanitizer->sanitize($html);
    }
}
