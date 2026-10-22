<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Konum\Dashboard\Widgets\CitiesCountWidget;
use Modules\Konum\Models\City;

uses(LazilyRefreshDatabase::class);

test('konum.cities.count widget reports the domain count and excludes ineligible records', function () {
    City::factory()->count(2)->create();

    $result = (new CitiesCountWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
