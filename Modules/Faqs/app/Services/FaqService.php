<?php

namespace Modules\Faqs\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\MediaService;
use Modules\Faqs\Models\Faq;

class FaqService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Faq $faq = null, ?Admin $actor = null): Faq
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $faq, $actor): Faq {
            $faq = $faq === null ? new Faq : Faq::query()->lockForUpdate()->findOrFail($faq->id);
            $faq->fill([
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'content' => $this->sanitizer->sanitize($data['content']),
            ])->save();
            $faq->extra_fields = $this->values->prepare(
                $this->features->form('Faqs', 'faq')['fields'],
                $data,
                $faq->extra_fields ?? [],
                'faqs/'.$faq->id.'/features',
                $files,
                $actor,
            );
            $faq->save();

            $this->values->syncMediaUsages($this->features->form('Faqs', 'faq')['fields'], $faq, $this->media, 'Sıkça sorulan soru / '.$faq->name, 'admin.faqs.edit');

            return $faq;
        });
    }

    public function delete(Faq $faq): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($faq): void {
            $faq = Faq::query()->lockForUpdate()->findOrFail($faq->id);
            foreach ($this->features->form('Faqs', 'faq')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $faq->extra_fields[$key] ?? null, 'faqs/'.$faq->id.'/features', true);
                }
            }
            $this->media->detachUsages($faq);
            $faq->delete();
        });
    }
}
