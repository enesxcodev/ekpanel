<?php

namespace Modules\Projects\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Media\MediaUsage;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\MediaSelection;
use App\Services\Media\MediaService;
use Illuminate\Validation\ValidationException;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectCategory;

class ProjectService
{
    public function __construct(private FieldFiles $files, private ModuleFeatures $features, private FeatureValues $values, private MediaService $media, private MediaSelection $selection, private HtmlSanitizer $sanitizer) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Project $project, Admin $actor): Project
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $project, $actor): Project {
            $project = $project ? Project::query()->lockForUpdate()->findOrFail($project->id) : new Project;
            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            $isPrimary = $language->code === ProjectsLanguageService::PrimaryCode;
            if (! $project->exists && ! $isPrimary) {
                throw ValidationException::withMessages(['lang_id' => 'Yeni projeler Türkçe oluşturulmalıdır.']);
            }
            if (! $project->exists || $isPrimary) {
                ProjectCategory::query()->lockForUpdate()->findOrFail($data['project_category_id']);
                $ids = array_map(intval(...), $data['image_media_ids']);
                $images = $this->selection->images($ids, $actor, $project->exists ? $project->images()->pluck('media_files.id')->all() : []);
                $cover = isset($data['cover_media_id']) ? (int) $data['cover_media_id'] : ($ids[0] ?? null);
                if ($cover !== null && ! in_array($cover, $ids, true)) {
                    throw ValidationException::withMessages(['cover_media_id' => 'Kapak, seçilen proje görsellerinden biri olmalıdır.']);
                }
                $project->fill([
                    'project_category_id' => $data['project_category_id'], 'project_date' => $data['project_date'] ?? null,
                    'is_active' => (bool) $data['is_active'], 'sort_order' => $data['sort_order'], 'cover_media_id' => $cover,
                ])->save();
                $pivot = [];
                foreach ($ids as $index => $id) {
                    $pivot[$id] = ['sort_order' => $index];
                }
                $project->images()->sync($pivot);
                MediaUsage::query()->where('owner_type', $project->getMorphClass())->where('owner_id', $project->id)->where('field', 'like', 'project_image.%')->delete();
                foreach ($images as $image) {
                    $this->media->attachUsage($image, $project, 'project_image.'.$image->id, 'Proje #'.$project->id, 'admin.projects.edit');
                }
            }
            $definitions = $this->features->form('Projects', 'project')['fields'];
            $translation = $project->translations()->firstOrNew(['project_id' => $project->id, 'lang_id' => $language->id]);
            $translation->fill(['title' => $data['title'], 'slug' => $data['slug'], 'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null]);
            $translation->extra_fields = $this->values->prepare($definitions, $data, $translation->extra_fields ?? [], 'projects/'.$project->id.'/translations/'.$language->id.'/features', $files, $actor);
            $translation->save();
            $this->values->syncMediaUsages($definitions, $translation, $this->media, 'Proje / '.$translation->title, 'admin.projects.translations.edit');

            return $project;
        });
    }

    public function delete(Project $project): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($project): void {
            $project = Project::query()->lockForUpdate()->findOrFail($project->id);
            foreach ($project->translations as $translation) {
                foreach ($this->features->form('Projects', 'project')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'projects/'.$project->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($project);
            $project->delete();
        });
    }
}
