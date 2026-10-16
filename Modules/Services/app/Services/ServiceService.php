<?php

namespace Modules\Services\Services;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use Modules\Services\Models\Service;

class ServiceService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files) {}

    /** @param array<string, mixed> $data */
    public function create(array $data): Service
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data): Service {
            return Service::query()->create([
                'service_category_id' => $data['service_category_id'] ?? null,
                'title' => $data['title'],
                'short_description' => $data['short_description'] ?? null,
                'content' => $data['content'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'is_active' => (bool) $data['is_active'],
                'image' => $files->replace($data['image'] ?? null, null, 'services/images'),
                'icon' => $data['icon'] ?? null,
                'extra_fields' => $this->values->prepare($this->features->form('Services', 'service')['fields'], $data, [], 'services/features', $files),
            ]);
        });
    }
}
