---
name: slider-theme-integration
description: EkAgent Slider modülünü müşteri temasındaki ana hero, banner, carousel veya tek tanıtım görseline eşlerken kullan; her görsel bandı Slider kabul etme.
---

# Slider tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Slider rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Eşleme sınırları ve alanların anlamı rule'da, ortak alan genişletme ise [modül kurallarında](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) tutulur.

- `app/Models/Slider.php`, `database/migrations/` ve `features.json` dosyalarını modül kökünden okuyup rule'daki alan bilgisini güncel kodla karşılaştır. `title/subtitle/image/extra_fields` ve `slider` formunun `button_text/short_description` alanlarını yeniden kullan; olmayan bağlantı, aktiflik veya sıra alanını varsayma.
- `app/Http/Controllers/SliderController.php` içindeki create/edit akışı `ModuleFeatures::form('Slider', 'slider')` ve `FeatureValues::fields` kullanır; `resources/views/partials/form.blade.php` ortak `admin.features.fields` bileşenini render eder. `app/Http/Requests/SaveSliderRequest.php` doğrulamayı `FeatureValues::rules` ile, `app/Services/SliderService.php` kaydı `FeatureValues::prepare` ve `FieldFiles` üzerinden yapar. Entegrasyon sırasında bu bağlantıların hâlâ mevcut olduğunu doğrula; ortak altyapıyı tekrar kurma.
- Tema verisi için mevcut `Modules\Slider\Models\Slider` sorgu örüntülerini ve `SliderService` sorumluluklarını incele. Serviste mevcut işlemler `save/delete` olduğundan var olmayan tema okuma metodunu çağırma; yerleşik model erişimini kullan. Sırf tema için facade/helper veya veri sınıfı ekleme.
- Tek görsel kayıt seçimi, carousel sırası veya alan eşleşmesi belirsizse kullanıcıya sor; yanıt gelmeden ilgili entegrasyonu sürdürme. Modülün kurulu ve etkin olma sınırını ortak skill'e göre koru.
