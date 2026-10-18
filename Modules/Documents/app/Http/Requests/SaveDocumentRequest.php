<?php

namespace Modules\Documents\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('document') ? 'documents.update' : 'documents.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $document = $this->route('document');

        return [
            'name' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:65535'],
            'category_id' => ['nullable', 'integer', 'exists:document_categories,id'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('documents', 'slug')->ignore($document?->id)],
            ...$media->rules($media->definition('file', 'Dosya', 15360, ['png', 'jpg', 'jpeg', 'webp', 'pdf', 'doc', 'pptx'])),
            'status' => ['required', Rule::in(['draft', 'published'])],
            ...$values->rules($features->form('Documents', 'document')['fields'], $this->all(), $document?->extra_fields ?? []),
        ];
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'name.required' => 'Belge adını girin.',
            'slug.regex' => 'Kısa bağlantı yalnızca küçük harf, rakam ve tire içerebilir.',
            'slug.unique' => 'Bu kısa bağlantı zaten kullanılıyor.',
            'file.prohibited_if' => 'Yeni dosya yükleme ve mevcut dosyayı kaldırma işlemlerinden birini seçin.',
            'file.mimes' => 'Dosya PNG, JPG, JPEG, WebP, PDF, DOC veya PPTX olmalıdır.',
            'file.extensions' => 'Dosya uzantısı PNG, JPG, JPEG, WebP, PDF, DOC veya PPTX olmalıdır.',
            'file.max' => 'Dosya en fazla 15 MB olabilir.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'Belge adı',
            'content' => 'İçerik',
            'category_id' => 'Kategori',
            'slug' => 'Kısa bağlantı',
            'file_media_id' => 'Dosya',
            'remove_file' => 'Dosyayı kaldır',
            'status' => 'Yayın durumu',
        ] + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Documents', 'document')['fields']);
    }
}
