<?php

namespace App\Http\Requests\Media;

use App\Rules\SafeMediaUpload;
use App\Services\Media\MediaFileInspector;
use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('media.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'file' => ['bail', 'required', 'file', 'max:51200', 'extensions:'.implode(',', MediaFileInspector::EXTENSIONS), new SafeMediaUpload],
        ];
    }

    public function messages(): array
    {
        return ['file.max' => 'Dosya en fazla 50 MB olabilir.', 'file.extensions' => 'Bu dosya uzantısına izin verilmiyor.', 'file.uploaded' => 'Dosya yüklenemedi. Sunucunun yükleme boyutu sınırını kontrol edin.'];
    }
}
