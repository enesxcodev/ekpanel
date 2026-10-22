@csrf @isset($event) @method('PUT') @endisset
<div class="row">
    <div class="col-md-6"><x-admin.form.input name="title" label="Etkinlik adı" :value="$event?->title" required /></div>
    <div class="col-md-6"><x-admin.form.input name="venue" label="Mekân" :value="$event?->venue" /></div>
    <div class="col-md-6"><x-admin.form.input name="starts_at" label="Başlangıç" type="datetime-local" :value="isset($event) ? $event->starts_at?->format('Y-m-d\\TH:i') : null" required /></div>
    <div class="col-md-6"><x-admin.form.input name="ends_at" label="Bitiş" type="datetime-local" :value="isset($event) ? $event->ends_at?->format('Y-m-d\\TH:i') : null" /></div>
    <div class="col-md-6"><x-admin.form.input name="capacity" label="Kontenjan" type="number" :value="$event?->capacity" min="1" /></div>
    <div class="col-md-6"><x-admin.form.boolean name="is_active" label="Yayında" :value="$event?->is_active ?? true" /></div>
    <div class="col-12"><x-admin.form.textarea name="description" label="Açıklama" :value="$event?->description" /></div>
</div>
