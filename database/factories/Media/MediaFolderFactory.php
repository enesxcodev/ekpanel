<?php

namespace Database\Factories\Media;

use App\Models\Media\MediaFolder;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MediaFolder> */
class MediaFolderFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true)];
    }
}
