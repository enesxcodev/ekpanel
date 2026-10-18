<?php

namespace App\Rules;

use App\Models\Media\MediaFile;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SelectedMediaImage implements ValidationRule
{
    public function __construct(private int $maxKilobytes = 4096) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $media = MediaFile::query()->where('kind', 'image')->find($value);
        if ($media === null || ! Storage::disk($media->disk)->exists($media->path)) {
            $fail('Kütüphaneden aktif ve geçerli bir görsel seçin.');

            return;
        }
        $file = new UploadedFile(Storage::disk($media->disk)->path($media->path), basename($media->path), test: true);
        $check = Validator::make(['image' => $file], [
            'image' => ['bail', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:'.$this->maxKilobytes],
        ], [
            'image.max' => 'Görsel en fazla '.$this->maxKilobytes.' KB olabilir.',
            'image.image' => 'Seçilen dosya geçerli bir görsel olmalıdır.',
            'image.mimes' => 'Yalnızca JPG, PNG ve WebP görselleri seçilebilir.',
        ]);
        foreach ($check->errors()->all() as $message) {
            $fail($message);
        }
    }
}
