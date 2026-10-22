<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Testimonials\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Testimonials\Dashboard\Widgets\RecordsCountWidget;
use Modules\Testimonials\Models\Testimonial;

uses(LazilyRefreshDatabase::class);

test('testimonials.total widget reports the domain count and excludes ineligible records', function () {
    Testimonial::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('testimonials.active widget reports the domain count and excludes ineligible records', function () {
    Testimonial::factory()->create(['is_active' => true]);
    Testimonial::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
