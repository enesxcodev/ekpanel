<?php

namespace App\Rules;

use App\Services\Media\MediaFileInspector;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class SafeMediaUpload implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile || ! $value->isValid()) {
            $fail('Geçerli bir dosya yükleyin.');

            return;
        }
        try {
            app(MediaFileInspector::class)->inspect($value->getRealPath(), $value->getClientOriginalName());
        } catch (ValidationException $exception) {
            $fail($exception->errors()['file'][0]);
        }
    }
}
