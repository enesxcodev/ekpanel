<?php

namespace Modules\Team\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
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
            $extraFields = $this->values->prepare($this->features->form('Team', 'member')['fields'], $data, $member->extra_fields ?? [], 'team/features', $files, $actor);
            $member->fill([
                'name' => $data['name'],
                'title' => $data['title'] ?? null,
                'is_active' => (bool) $data['is_active'],
                ...$this->contentMedia->prepare($member, $data, $this->contentMedia->definition(), $actor),
                'extra_fields' => $extraFields,
            ])->save();

            $this->contentMedia->syncUsage($member, 'image', 'Ekip / '.$member->name, 'admin.team.edit');
            $this->values->syncMediaUsages($this->features->form('Team', 'member')['fields'], $member, $this->media, 'Ekip / '.$member->name, 'admin.team.edit');

            return $member;
        });
    }

    public function delete(TeamMember $member): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($member): void {
            $member = TeamMember::query()->lockForUpdate()->findOrFail($member->id);
            foreach ($this->features->form('Team', 'member')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $member->extra_fields[$key] ?? null, 'team/features', true);
                }
            }
            $this->media->detachUsages($member);
            $member->delete();
        });
    }
}
