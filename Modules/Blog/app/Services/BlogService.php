<?php

namespace Modules\Blog\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
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
                'title' => $data['title'],
                'slug' => $data['slug'],
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'content' => $this->sanitizer->sanitize($data['content'] ?? ''),
                ...$this->contentMedia->prepare($content, $data, $this->contentMedia->definition(), $actor),
                'status' => $data['status'],
                'published_at' => $data['published_at'] ?? null,
            ])->save();
            $content->extra_fields = $this->values->prepare($this->features->form('Blog', 'content')['fields'], $data, $content->extra_fields ?? [], 'blog/'.$content->id.'/features', $files, $actor);
            $content->save();
            $content->tags()->sync($data['tags'] ?? []);
            $this->contentMedia->syncUsage($content, 'image', 'İçerik / '.$content->title, 'admin.blog.edit');
            $this->values->syncMediaUsages($this->features->form('Blog', 'content')['fields'], $content, $this->media, 'İçerik / '.$content->title, 'admin.blog.edit');

            return $content;
        });
    }

    public function delete(BlogContent $content): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($content): void {
            $content = BlogContent::query()->lockForUpdate()->findOrFail($content->id);
            foreach ($this->features->form('Blog', 'content')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $content->extra_fields[$key] ?? null, 'blog/'.$content->id.'/features', true);
                }
            }
            $this->media->detachUsages($content);
            $content->delete();
        });
    }
}
