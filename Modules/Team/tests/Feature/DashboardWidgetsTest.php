<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Team\Dashboard\Widgets\ActiveRecordsWidget;
use Modules\Team\Dashboard\Widgets\RecordsCountWidget;
use Modules\Team\Models\TeamMember;

uses(LazilyRefreshDatabase::class);

test('team.total widget reports the domain count and excludes ineligible records', function () {
    TeamMember::factory()->count(2)->create();

    $result = (new RecordsCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('team.active widget reports the domain count and excludes ineligible records', function () {
    TeamMember::factory()->create(['is_active' => true]);
    TeamMember::factory()->create(['is_active' => false]);

    $result = (new ActiveRecordsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
