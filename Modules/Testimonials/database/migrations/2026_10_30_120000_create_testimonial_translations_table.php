<?php

use App\Models\Settings\Language;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Testimonials\Models\Testimonial;
use Modules\Testimonials\Models\TestimonialTranslation;

return new class extends Migration
{
    public function up(): void
    {
        $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
        if (! $language->is_active) {
            $language->is_active = true;
            $language->save();
        }

        Schema::create('testimonial_translations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('testimonial_id')->constrained('testimonials')->cascadeOnDelete();
            $table->foreignId('lang_id')->constrained('languages')->restrictOnDelete();
            $table->string('customer_name');
            $table->string('company')->nullable();
            $table->string('job_title')->nullable();
            $table->longText('content');
            $table->json('extra_fields')->nullable();
            $table->timestamps();
            $table->unique(['testimonial_id', 'lang_id']);
        });

        Testimonial::query()->orderBy('id')->chunkById(100, function ($testimonials) use ($language): void {
            foreach ($testimonials as $testimonial) {
                TestimonialTranslation::query()->firstOrCreate(
                    ['testimonial_id' => $testimonial->id, 'lang_id' => $language->id],
                    ['customer_name' => $testimonial->customer_name, 'company' => $testimonial->company, 'job_title' => $testimonial->job_title, 'content' => $testimonial->content, 'extra_fields' => $testimonial->extra_fields, 'created_at' => $testimonial->created_at, 'updated_at' => $testimonial->updated_at],
                );
            }
        });

        $this->moveExtraFieldMediaUsages($language->id);

        if (Testimonial::query()->count() !== TestimonialTranslation::query()->where('lang_id', $language->id)->count()
            || Testimonial::query()->whereDoesntHave('translations', fn ($query) => $query->where('lang_id', $language->id))->exists()) {
            throw new RuntimeException('Testimonials Turkish translation backfill verification failed; legacy columns were preserved.');
        }

        Schema::table('testimonials', function (Blueprint $table): void {
            $table->dropColumn(['customer_name', 'company', 'job_title', 'content', 'extra_fields']);
        });
    }

    public function down(): void
    {
        throw new RuntimeException('Rollback is disabled because it would discard testimonial translations. Restore from backup with an explicit data migration.');
    }

    private function moveExtraFieldMediaUsages(int $languageId): void
    {
        if (! Schema::hasTable('media_usages')) {
            return;
        }
        $legacyType = (new Testimonial)->getMorphClass();
        Testimonial::query()->with('translations')->orderBy('id')->chunkById(100, function ($testimonials) use ($languageId, $legacyType): void {
            foreach ($testimonials as $testimonial) {
                $translation = $testimonial->translations->firstWhere('lang_id', $languageId);
                if ($translation === null) {
                    continue;
                }
                DB::table('media_usages')->where('owner_type', $legacyType)->where('owner_id', $testimonial->id)
                    ->where('field', 'like', 'extra_fields.%')->update([
                        'owner_type' => $translation->getMorphClass(), 'owner_id' => $translation->id,
                        'route_name' => 'admin.testimonials.translation.edit', 'updated_at' => now(),
                    ]);
            }
        });
    }
};
