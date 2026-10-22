<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Users\Dashboard\Widgets\ActiveUsersWidget;
use Modules\Users\Dashboard\Widgets\TotalUsersWidget;
use Modules\Users\Models\User;

uses(LazilyRefreshDatabase::class);

test('users.total widget reports the domain count and excludes ineligible records', function () {
    User::factory()->count(2)->create();
    User::factory()->create()->delete();

    $result = (new TotalUsersWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});

test('users.active widget reports the domain count and excludes ineligible records', function () {
    User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);
    User::factory()->create(['is_active' => true])->delete();

    $result = (new ActiveUsersWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});
