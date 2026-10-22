@csrf
@isset($appointment) @method('PUT') @endisset
<div class="row">
    <div class="col-md-6"><x-admin.form.input name="customer_name" label="Müşteri adı" :value="$appointment?->customer_name" required /></div>
    <div class="col-md-6"><x-admin.form.input name="customer_email" label="E-posta" type="email" :value="$appointment?->customer_email" /></div>
    <div class="col-md-6"><x-admin.form.input name="customer_phone" label="Telefon" :value="$appointment?->customer_phone" /></div>
    <div class="col-md-6"><x-admin.form.input name="service_name" label="Hizmet" :value="$appointment?->service_name" required /></div>
    <div class="col-md-6"><x-admin.form.input name="starts_at" label="Başlangıç" type="datetime-local" :value="isset($appointment) ? $appointment->starts_at?->format('Y-m-d\\TH:i') : null" required /></div>
    <div class="col-md-6"><x-admin.form.input name="duration_minutes" label="Süre (dakika)" type="number" :value="$appointment?->duration_minutes ?? 30" required min="5" /></div>
    <div class="col-md-6"><x-admin.form.select name="status" label="Durum" :options="[['value' => 'pending', 'label' => 'Bekliyor'], ['value' => 'confirmed', 'label' => 'Onaylandı'], ['value' => 'cancelled', 'label' => 'İptal edildi']]" :value="$appointment?->status ?? 'pending'" required /></div>
    <div class="col-12"><x-admin.form.textarea name="notes" label="Notlar" :value="$appointment?->notes" /></div>
</div>
