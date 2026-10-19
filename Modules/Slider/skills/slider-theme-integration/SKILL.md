---
name: slider-theme-integration
description: EkAgent Slider modülünü müşteri temasındaki ana hero, banner, carousel veya tek tanıtım görseline eşlerken kullan; her görsel bandı Slider kabul etme.
---

# Slider tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Slider rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Eşleme sınırları ve alanların anlamı rule'da, ortak alan genişletme ise [modül kurallarında](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) tutulur.

- `app/Models/Slider.php`, `database/migrations/` ve `features.json` dosyalarını modül kökünden okuyup rule'daki alan bilgisini güncel kodla karşılaştır. `title/subtitle/image/is_active/extra_fields` ve `slider` formunun `button_text/short_description` alanlarını yeniden kullan; aktif kayıtları `is_active` ile filtrele; olmayan bağlantı veya sıra alanını varsayma.
- `app/Http/Controllers/SliderController.php` içindeki create/edit akışı `ModuleFeatures::form('Slider', 'slider')` ve `FeatureValues::fields` kullanır; `resources/views/partials/form.blade.php` ortak `admin.features.fields` bileşenini render eder. `app/Http/Requests/SaveSliderRequest.php` doğrulamayı `FeatureValues::rules` ile, `app/Services/SliderService.php` kaydı `FeatureValues::prepare` ve `FieldFiles` üzerinden yapar. Entegrasyon sırasında bu bağlantıların hâlâ mevcut olduğunu doğrula; ortak altyapıyı tekrar kurma.
- Tema okumalarında `Modules\Slider\Services\SliderFrontendService` kullan. Tek banner için `getFirst()` aktif bir kayıt veya `null`, çoklu slider için `getAll()` tüm aktif kayıtları bir Eloquent Collection olarak döndürür. İki metot da ana görsel ilişkisini yükler; modül bulunamıyor/devre dışıysa veya tablo yoksa boş sonuç verir. Admin kayıt işlemleri `SliderService` içinde kalır.
- Agent, tasarım tek görsel istiyorsa `getFirst()`, carousel/çoklu slider istiyorsa `getAll()` seçer. Veritabanındaki kayıt sayısı kendiliğinden carousel oluşturmaz. Şablon koleksiyonu uygun slider arayüzüyle render etmelidir. Servis tema HTML'i veya carousel davranışı üretmez.
- Mevcut tek banner çağrısı ilk aktif kaydı kullanır; iş anlamı taşıyan öncelik/sıralama garantisi yoktur. Belirli kayıt seçimi veya carousel sırası gerekiyorsa mevcut alanları ve kullanıcı talebini doğrula; olmayan sıralama alanı varsayma. Çağıran tarafta modül/tablo kontrollerini ve aktiflik filtresini tekrar etme.
- Tek görsel kayıt seçimi, carousel sırası veya alan eşleşmesi belirsizse kullanıcıya sor; yanıt gelmeden ilgili entegrasyonu sürdürme. Modülün kurulu ve etkin olma sınırını ortak skill'e göre koru.
