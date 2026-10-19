<?php

namespace App\Services\Forms;

use App\Mail\FormSubmissionReceived;
use App\Models\Forms\FormSubmission;
use App\Models\Forms\SiteForm;
use App\Services\Fields\FieldRuleBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class FormService
{
    public function __construct(private FormSchema $schema, private FieldRuleBuilder $fieldRules, private FormContext $contexts) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?SiteForm $form = null): SiteForm
    {
        return DB::transaction(function () use ($data, $form): SiteForm {
            $form = $form ? SiteForm::query()->lockForUpdate()->findOrFail($form->id) : new SiteForm;
            if ($form->exists && $form->key !== $data['key']) {
                throw ValidationException::withMessages(['key' => 'Form anahtarı oluşturulduktan sonra değiştirilemez.']);
            }
            $form->fill(array_intersect_key($data, array_flip(['name', 'key', 'is_active', 'submit_label', 'success_message', 'notification_email', 'css_class'])))->save();
            $keys = [];
            foreach ($data['fields'] as $order => $field) {
                $keys[] = $field['key'];
                $validation = [];
                if (in_array($field['input_type'], ['text', 'email', 'tel', 'textarea'], true)) {
                    $validation['max'] = $field['max_length'] ?? 10000;
                }
                if ($field['input_type'] === 'file') {
                    $validation = ['extensions' => explode(',', ($field['extensions'] ?? null) ?: 'pdf,docx'), 'max_size' => $field['max_size'] ?? 5120];
                }
                $form->fields()->updateOrCreate(['key' => $field['key']], [
                    ...array_intersect_key($field, array_flip(['label', 'input_type', 'required', 'placeholder', 'description', 'width', 'css_class', 'input_class', 'role', 'options'])),
                    'validation' => $validation, 'sort_order' => $order,
                ]);
            }
            $form->fields()->whereNotIn('key', $keys)->delete();

            return $form;
        });
    }

    public function submit(SiteForm $form, Request $request): FormSubmission
    {
        $newFiles = [];
        try {
            $submission = DB::transaction(function () use ($form, $request, &$newFiles): FormSubmission {
                $form = SiteForm::query()->lockForUpdate()->findOrFail($form->id);
                abort_unless($form->is_active, 404);
                $context = $this->contexts->resolve($request->input('form_context'), $form);
                $form->load('fields');
                abort_if($form->fields->isEmpty(), 404);
                $definition = $this->schema->validation($form);
                $payload = $request->all();
                $payload['answers'] ??= [];
                $validated = Validator::make($payload, $definition['rules'], $this->fieldRules->messages(), $definition['labels'])->validateWithBag('form_'.$form->id);
                $answers = [];
                $summary = ['name' => null, 'email' => null, 'phone' => null];
                foreach ($form->fields as $field) {
                    $value = $validated['answers'][$field->key] ?? null;
                    if ($value instanceof UploadedFile) {
                        $path = $value->store('form-submissions/'.Str::uuid(), 'local');
                        if ($path === false) {
                            throw new \RuntimeException('Başvuru dosyası kaydedilemedi.');
                        }
                        $newFiles[] = $path;
                        $value = ['path' => $path, 'name' => basename($value->getClientOriginalName())];
                    } elseif ($field->input_type === 'boolean') {
                        $value = filter_var($value, FILTER_VALIDATE_BOOL);
                    }
                    $display = in_array($field->input_type, ['select', 'radio'], true) ? collect($field->options)->firstWhere('value', $value)['label'] ?? null : null;
                    $answers[$field->key] = ['label' => $field->label, 'type' => $field->input_type, 'value' => $value, 'display' => $display];
                    if ($field->role && is_scalar($value)) {
                        $summary[$field->role] = mb_substr((string) $value, 0, 255);
                    }
                }

                return $form->submissions()->create(['form_name' => $form->name, 'related_content' => $context, ...$summary, 'status' => 'new', 'answers' => $answers]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($newFiles);
            throw $exception;
        }
        if ($form->notification_email) {
            try {
                Mail::to($form->notification_email)->send(new FormSubmissionReceived($submission));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return $submission;
    }

    public function deleteSubmission(FormSubmission $submission): void
    {
        $paths = DB::transaction(function () use ($submission): array {
            $submission = FormSubmission::query()->lockForUpdate()->findOrFail($submission->id);
            $paths = [];
            foreach ($submission->answers as $answer) {
                if ($answer['type'] === 'file' && is_array($answer['value']) && str_starts_with($answer['value']['path'] ?? '', 'form-submissions/')) {
                    $paths[] = $answer['value']['path'];
                }
            }
            $submission->delete();

            return $paths;
        });
        Storage::disk('local')->delete($paths);
    }
}
