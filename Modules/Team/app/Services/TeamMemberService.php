<?php

namespace Modules\Team\Services;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use Modules\Team\Models\TeamMember;

class TeamMemberService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?TeamMember $member = null): TeamMember
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $member): TeamMember {
            $member = $member === null ? new TeamMember : TeamMember::query()->lockForUpdate()->findOrFail($member->id);
            $extraFields = $this->values->prepare($this->features->form('Team', 'member')['fields'], $data, $member->extra_fields ?? [], 'team/features', $files);
            $member->fill([
                'name' => $data['name'],
                'title' => $data['title'] ?? null,
                'is_active' => (bool) $data['is_active'],
                'image' => $files->replace($data['image'] ?? null, $member->image, 'team/images', (bool) ($data['remove_image'] ?? false)),
                'extra_fields' => $extraFields,
            ])->save();

            return $member;
        });
    }

    public function delete(TeamMember $member): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($member): void {
            $member = TeamMember::query()->lockForUpdate()->findOrFail($member->id);
            $files->replace(null, $member->image, 'team/images', true);
            foreach ($this->features->form('Team', 'member')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile()) {
                    $files->replace(null, $member->extra_fields[$key] ?? null, 'team/features', true);
                }
            }
            $member->delete();
        });
    }
}
