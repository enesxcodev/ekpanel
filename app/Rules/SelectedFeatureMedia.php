<?php

namespace App\Rules;

use App\Enums\Fields\FieldInputType;
use App\Models\Media\MediaFile;
use App\Services\Fields\FieldDefinition;
use App\Services\Media\MediaFileInspector;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SelectedFeatureMedia implements ValidationRule
{
    public function __construct(private FieldDefinition $definition) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $media = MediaFile::query()->where('disk', 'public')->find($value);
        if ($media === null || ! Storage::disk('public')->exists($media->path)) {
            $fail('Kütüphaneden aktif ve geçerli bir dosya seçin.');

            return;
        }
        $validation = $this->definition->validation;
        $path = Storage::disk('public')->path($media->path);
        try {
            $metadata = (new MediaFileInspector)->inspect($path, basename($media->path));
        } catch (ValidationException $exception) {
            $fail($exception->validator->errors()->first());

            return;
        }
        $extensions = $validation['extensions'] ?? ($this->definition->input_type === FieldInputType::Image ? ['jpg', 'jpeg', 'png', 'webp'] : MediaFileInspector::EXTENSIONS);
        if (! in_array($metadata['extension'], $extensions, true)
            || ($this->definition->input_type === FieldInputType::Image && $metadata['kind'] !== 'image')) {
            $fail('Seçilen dosyanın türü bu alan için uygun değil.');

            return;
        }
        $rules = ['file', 'max:'.($validation['max_size'] ?? 2048)];
        $dimensions = array_intersect_key($validation, array_flip(['min_width', 'min_height', 'max_width', 'max_height']));
        if ($this->definition->input_type === FieldInputType::Image && $dimensions !== []) {
            $rules[] = Rule::dimensions($dimensions);
        }
        $file = new UploadedFile($path, basename($media->path), test: true);
        $check = Validator::make(['file' => $file], ['file' => $rules], [
            'file.max' => ':attribute en fazla '.($validation['max_size'] ?? 2048).' KB olabilir.',
            'file.dimensions' => ':attribute görsel ölçüleri tanımlanan sınırlara uymalıdır.',
        ], ['file' => $this->definition->label]);
        foreach ($check->errors()->all() as $message) {
            $fail($message);
        }
    }
}
