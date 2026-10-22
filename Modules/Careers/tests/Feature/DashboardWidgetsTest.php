<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Careers\Dashboard\Widgets\ActivePostingsWidget;
use Modules\Careers\Dashboard\Widgets\NewApplicationsWidget;
use Modules\Careers\Database\Factories\JobApplicationFactory;
use Modules\Careers\Models\JobPosting;

uses(LazilyRefreshDatabase::class);

test('careers.applications.new widget reports the domain count and excludes ineligible records', function () {
    JobApplicationFactory::new()->create(['status' => 'new']);
    JobApplicationFactory::new()->create(['status' => 'reviewing']);

    $result = (new NewApplicationsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(1);
});

test('careers.postings.active widget reports the domain count and excludes ineligible records', function () {
    $this->freezeTime();
    JobPosting::factory()->create(['is_active' => true, 'application_deadline' => today()]);
    JobPosting::factory()->create(['is_active' => true, 'application_deadline' => null]);
    JobPosting::factory()->create(['is_active' => true, 'application_deadline' => today()->subDay()]);
    JobPosting::factory()->create(['is_active' => false, 'application_deadline' => null]);

    $result = (new ActivePostingsWidget)->data(Admin::factory()->make());

    expect($result['value'])->toBe(2);
});
