<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Careers\Models\JobPosting;
use Nwidart\Modules\Contracts\ActivatorInterface;

uses(LazilyRefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutVite();
    $disk = Storage::fake('careers-test-statuses');
    $disk->put('statuses.json', json_encode(['Careers' => true], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
});

test('an admin can create a job posting', function () {
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.careers.store'), [
        'title' => 'PHP Geliştirici', 'location' => 'İstanbul', 'employment_type' => 'full_time',
        'application_deadline' => '2026-12-31', 'is_active' => '1',
    ])->assertRedirectToRoute('admin.careers.index');

    $this->assertDatabaseHas('job_postings', ['title' => 'PHP Geliştirici', 'is_active' => true]);
});

test('the job posting create form receives an empty posting value', function () {
    $admin = Admin::factory()->superAdmin()->create();

    $this->actingAs($admin, 'admin')->get(route('admin.careers.create'))
        ->assertOk()
        ->assertSee('Pozisyon');
});

test('an admin can record an application against a job posting', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $posting = JobPosting::factory()->create();

    $this->actingAs($admin, 'admin')->post(route('admin.careers.applications.store'), [
        'job_posting_id' => $posting->id, 'candidate_name' => 'Can Demir', 'email' => 'can@example.test', 'status' => 'new',
    ])->assertRedirectToRoute('admin.careers.applications.index');

    $this->assertDatabaseHas('job_applications', ['job_posting_id' => $posting->id, 'candidate_name' => 'Can Demir']);
});

test('the job posting data table renders row actions with the posting model', function () {
    $admin = Admin::factory()->superAdmin()->create();
    $posting = JobPosting::factory()->create();

    $this->actingAs($admin, 'admin')->getJson(route('admin.careers.data', [
        'draw' => 1,
        'start' => 0,
        'length' => 10,
        'columns' => [['data' => 'title', 'name' => 'title', 'searchable' => 'true', 'orderable' => 'true']],
    ]))->assertOk()->assertJsonPath('data.0.id', $posting->id)->assertJsonPath('data.0.actions', fn (string $actions): bool => str_contains($actions, route('admin.careers.edit', $posting)));
});
