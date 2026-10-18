<?php

namespace Database\Factories\Media;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MediaFile> */
class MediaFileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'disk' => 'public', 'path' => '_media/'.fake()->uuid().'.txt',
            'name' => 'belge.txt', 'mime_type' => 'text/plain', 'extension' => 'txt', 'kind' => 'document', 'size' => 100,
        ];
    }
}
