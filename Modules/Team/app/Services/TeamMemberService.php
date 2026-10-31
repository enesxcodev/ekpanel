<?php

namespace Modules\Team\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Team\Models\TeamMember;

class TeamMemberService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?TeamMember $member = null, ?Admin $actor = null): TeamMember
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $member, $actor): TeamMember {
            $member = $member === null ? new TeamMember : TeamMember::query()->lockForUpdate()->findOrFail($member->id);
            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            if ($member->exists === false || $language->code === 'tr') {
                $member->fill([
                    'is_active' => (bool) ($data['is_active'] ?? $member->is_active ?? true),
                    ...$this->contentMedia->prepare($member, $data, $this->contentMedia->definition(), $actor),
                ])->save();
            }
            $translation = $member->translations()->firstOrNew(['lang_id' => $language->id]);
            $extraFields = $this->values->prepare($this->features->form('Team', 'member')['fields'], $data, $translation->extra_fields ?? [], 'team/features', $files, $actor);
            $translation->fill(['name' => $data['name'], 'title' => $data['title'] ?? null, 'extra_fields' => $extraFields])->save();

            if ($language->code === 'tr') {
                $this->contentMedia->syncUsage($member, 'image', 'Ekip / '.$translation->name, 'admin.team.edit');
            }
            $this->values->syncMediaUsages($this->features->form('Team', 'member')['fields'], $translation, $this->media, 'Ekip / '.$translation->name, 'admin.team.edit');

            return $member;
        });
    }

    public function delete(TeamMember $member): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($member): void {
            $member = TeamMember::query()->lockForUpdate()->findOrFail($member->id);
            foreach ($member->translations as $translation) {
                foreach ($this->features->form('Team', 'member')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'team/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($member);
            $member->delete();
        });
    }
}
