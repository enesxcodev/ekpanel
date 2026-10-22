<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Sector\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Sector\Dashboard\Widgets\RecordsCountWidget;
use Modules\Sector\Models\Sector;

uses(LazilyRefreshDatabase::class);

test('sector.total widget reports the domain count and excludes ineligible records', function () {
    Sector::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('sector.active widget reports the domain count and excludes ineligible records', function () {
    Sector::factory()->create(['is_active' => true]);
    Sector::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
