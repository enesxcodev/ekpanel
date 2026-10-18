<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sliders', function (Blueprint $table): void {
            $table->foreignId('image_media_id')->nullable()->constrained('media_files')->restrictOnDelete();
        });
        DB::table('sliders')->orderBy('id')->each(function (object $slider): void {
            $media = DB::table('media_files')->where('disk', 'public')->where('kind', 'image')
                ->whereNull('deleted_at')->where('path', $slider->image)->first();
            if ($media === null) {
                return;
            }
            DB::table('sliders')->where('id', $slider->id)->update(['image_media_id' => $media->id]);
            DB::table('media_usages')->updateOrInsert([
                'owner_type' => 'Modules\\Slider\\Models\\Slider', 'owner_id' => $slider->id, 'field' => 'image',
            ], [
                'media_id' => $media->id, 'label' => 'Slider / '.$slider->title, 'route_name' => 'admin.slider.edit',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        DB::table('media_usages')->where('owner_type', 'Modules\\Slider\\Models\\Slider')->delete();
        Schema::table('sliders', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('image_media_id');
        });
    }
};
