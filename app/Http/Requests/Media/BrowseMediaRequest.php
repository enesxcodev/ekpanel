<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class BrowseMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('media.view') ?? false;
    }

    public function rules(): array
    {
        return [
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
            'view' => ['sometimes', 'in:all,recent,favorites,trash'],
            'kind' => ['nullable', 'in:image,video,document'],
            'search' => ['nullable', 'string', 'max:150'],
            'sort' => ['sometimes', 'in:name,created_at,size'],
            'direction' => ['sometimes', 'in:asc,desc'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'extensions' => ['sometimes', 'array', 'max:11'],
            'extensions.*' => ['string', 'in:jpg,jpeg,png,webp,mp4,webm,pdf,doc,docx,pptx,txt'],
        ];
    }
}
