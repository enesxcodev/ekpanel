@csrf @isset($posting) @method('PUT') @endisset
<div class="row">
    <div class="col-md-6"><x-admin.form.input name="title" label="Pozisyon" :value="$posting?->title" required /></div>
    <div class="col-md-6"><x-admin.form.input name="location" label="Çalışma yeri" :value="$posting?->location" /></div>
    <div class="col-md-6"><x-admin.form.select name="employment_type" label="Çalışma türü" :options="[['value'=>'full_time','label'=>'Tam zamanlı'],['value'=>'part_time','label'=>'Yarı zamanlı'],['value'=>'remote','label'=>'Uzaktan'],['value'=>'internship','label'=>'Staj']]" :value="$posting?->employment_type ?? 'full_time'" required /></div>
    <div class="col-md-6"><x-admin.form.input name="application_deadline" label="Son başvuru" type="date" :value="$posting?->application_deadline?->format('Y-m-d')" /></div>
    <div class="col-md-6"><x-admin.form.boolean name="is_active" label="Yayında" :value="$posting?->is_active ?? true" /></div>
    <div class="col-12"><x-admin.form.textarea name="description" label="Açıklama" :value="$posting?->description" /></div>
</div>
