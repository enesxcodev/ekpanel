<?php

use App\Models\Admin\Admin;
use App\Models\Forms\SiteForm;
use App\Services\Forms\FormSchema;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

/** @return array<string, mixed> */
function regressionFormField(string $key, string $type, array $overrides = []): array
{
    return array_replace(['key' => $key, 'label' => $key, 'input_type' => $type, 'required' => false, 'placeholder' => '', 'description' => '', 'width' => 'full', 'css_class' => '', 'input_class' => '', 'role' => '', 'options' => [], 'max_length' => null, 'max_size' => 5120, 'extensions' => 'pdf,docx'], $overrides);
}

test('existing form creation editing ordering and submission still use the shared builder without module options', function () {
    Mail::fake();
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin');
    $fields = [regressionFormField('email', 'email', ['required' => true, 'role' => 'email', 'width' => 'half']), regressionFormField('subject', 'select', ['options' => [['value' => 'service', 'label' => 'Servis']]])];
    $payload = ['name' => 'İletişim', 'key' => 'regression-contact', 'is_active' => true, 'submit_label' => 'Gönder', 'success_message' => 'Alındı', 'notification_email' => null, 'fields' => json_encode($fields)];

    $this->get(route('admin.forms.create'))->assertOk()->assertSee('data-form-builder', false)
        ->assertSee('data-form-preset="contact"', false)->assertSee('data-import-fields', false)->assertDontSee('data-feature-form', false);
    $this->post(route('admin.forms.store'), $payload)->assertSessionHasNoErrors();
    $form = SiteForm::query()->where('key', 'regression-contact')->firstOrFail();
    $this->get(route('admin.forms.edit', $form))->assertOk()->assertSee('data-form-preview', false);
    $fields = array_reverse($fields);
    $fields[0]['label'] = 'Konu';
    $this->put(route('admin.forms.update', $form), [...$payload, 'fields' => json_encode($fields)])->assertSessionHasNoErrors();
    expect(app(FormSchema::class)->editorFields($form->fresh()->load('fields')))->sequence(
        fn ($field) => $field->key->toBe('subject')->label->toBe('Konu'),
        fn ($field) => $field->key->toBe('email')->role->toBe('email')->width->toBe('half'),
    );
    $this->post(route('forms.submit', $form->key), ['answers' => ['email' => 'visitor@example.com', 'subject' => 'service']])->assertSessionHasNoErrors();

    $submission = $form->submissions()->firstOrFail();
    expect($submission->email)->toBe('visitor@example.com');
    expect($submission->answers['subject'])->toBe(['label' => 'Konu', 'type' => 'select', 'value' => 'service', 'display' => 'Servis']);
    Mail::assertNothingSent();
});

test('existing form HTML import keeps supported fields and excludes scripts and hidden fields', function () {
    $this->actingAs(Admin::factory()->superAdmin()->create(), 'admin')
        ->postJson(route('admin.forms.import'), ['html' => '<form><script>alert(1)</script><input type="hidden" name="token"><label for="mail">E-posta</label><input id="mail" type="email" name="email" required><select name="subject"><option value="service">Servis</option></select></form>'])
        ->assertOk()->assertJsonCount(2, 'fields')->assertJsonPath('fields.0.key', 'email')
        ->assertJsonPath('fields.0.required', true)->assertJsonPath('fields.1.options.0.label', 'Servis');
});

test('existing visitor forms reject invalid answers without creating submissions', function () {
    $form = SiteForm::factory()->create(['is_active' => true]);
    $form->fields()->create(regressionFormField('email', 'email', ['required' => true, 'validation' => ['max' => 100]]));

    $this->post(route('forms.submit', $form->key), ['answers' => ['email' => 'invalid']])->assertSessionHasErrors('answers.email', errorBag: 'form_'.$form->id);

    $this->assertDatabaseCount('form_submissions', 0);
});
