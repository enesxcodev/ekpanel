<?php

namespace Modules\Blog\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Blog\Models\BlogContent;

class BlogService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?BlogContent $content = null, ?Admin $actor = null): BlogContent
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $content, $actor): BlogContent {
            $content = $content === null ? new BlogContent : BlogContent::query()->lockForUpdate()->findOrFail($content->id);
            $content->fill([
                'blog_category_id' => $data['blog_category_id'],
                'author_id' => $data['author_id'] ?? null,
                ...$this->contentMedia->prepare($content, $data, $this->contentMedia->definition(), $actor),
                'status' => $data['status'],
                'published_at' => $data['published_at'] ?? null,
            ])->save();
            $language = isset($data['lang_id'])
                ? Language::query()->where('is_active', true)->findOrFail($data['lang_id'])
                : app(BlogLanguageService::class)->forPublic();
            $translation = $content->translations()->firstOrNew(['lang_id' => $language->id]);
            $translation->fill([
                'title' => $data['title'],
                'slug' => $data['slug'],
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'content' => $this->sanitizer->sanitize($data['content'] ?? ''),
            ]);
            $translation->extra_fields = $this->values->prepare($this->features->form('Blog', 'content')['fields'], $data, $translation->extra_fields ?? [], 'blog/'.$content->id.'/translations/'.$language->id.'/features', $files, $actor);
            $translation->save();
            $content->tags()->sync($data['tags'] ?? []);
            $this->contentMedia->syncUsage($content, 'image', 'İçerik / '.$translation->title, 'admin.blog.edit');
            $this->values->syncMediaUsages($this->features->form('Blog', 'content')['fields'], $translation, $this->media, 'İçerik / '.$translation->title, 'admin.blog.translations.edit');

            return $content;
        });
    }

    public function delete(BlogContent $content): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($content): void {
            $content = BlogContent::query()->lockForUpdate()->findOrFail($content->id);
            foreach ($content->translations()->get() as $translation) {
                foreach ($this->features->form('Blog', 'content')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'blog/'.$content->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($content);
            $content->delete();
        });
    }
}
