---
name: slider-theme-integration
description: EkAgent Slider modülünü müşteri temasındaki ana hero, banner, carousel veya tek tanıtım görseline eşlerken kullan; her görsel bandı Slider kabul etme.
---

# Slider tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Slider rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Eşleme sınırları ve alanların anlamı rule'da, ortak alan genişletme ise [modül kurallarında](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) tutulur.

- `app/Models/Slider.php`, `app/Models/SliderTranslation.php`, `database/migrations/` ve `features.json` dosyalarını modül kökünden okuyup rule'daki sahiplik bilgisini güncel kodla karşılaştır. `title/subtitle/extra_fields` seçili dil çevirisindedir; `image/image_media_id/is_active` global ana kayıttadır. Slider formunun metinsel özellikleri çeviriye dahildir.
- `app/Http/Controllers/SliderController.php` içindeki create/edit akışı `ModuleFeatures::form('Slider', 'slider')` ve `FeatureValues::fields` kullanır; `resources/views/partials/form.blade.php` ortak `admin.features.fields` bileşenini render eder. `app/Http/Requests/SaveSliderRequest.php` doğrulamayı `FeatureValues::rules` ile, `app/Services/SliderService.php` kaydı `FeatureValues::prepare` ve `FieldFiles` üzerinden yapar. Entegrasyon sırasında bu bağlantıların hâlâ mevcut olduğunu doğrula; ortak altyapıyı tekrar kurma.
- Tema okumalarında `Modules\Slider\Services\SliderFrontendService` kullan. `getFirst($language)` aktif dil çevirisi olan ilk aktif Slider'ı veya `null`, `getAll($language)` bu kayıtları Eloquent Collection olarak döndürür. İki metot seçili çeviriyi ve global ana görsel ilişkisini yükler. Aktif dilde çeviri olmayan Slider gösterilmez ve otomatik fallback yapılmaz; modül/devre dışı ya da gerekli tablo yoksa boş sonuç alınır. Admin kayıt işlemleri `SliderService` içinde kalır.
- Agent, tasarım tek görsel istiyorsa `getFirst()`, carousel/çoklu slider istiyorsa `getAll()` seçer. Veritabanındaki kayıt sayısı kendiliğinden carousel oluşturmaz. Şablon koleksiyonu uygun slider arayüzüyle render etmelidir. Servis tema HTML'i veya carousel davranışı üretmez.
- `getFirst()` sıralama alanı olmayan ilk eşleşeni döndürür; iş anlamı taşıyan öncelik garantisi yoktur. Belirli kayıt seçimi veya carousel sırası gerekiyorsa mevcut alanları ve kullanıcı talebini doğrula; olmayan sıralama alanı varsayma. Çağıran tarafta modül/tablo kontrollerini ve aktiflik filtresini tekrar etme.
- Tek görsel kayıt seçimi, carousel sırası veya alan eşleşmesi belirsizse kullanıcıya sor; yanıt gelmeden ilgili entegrasyonu sürdürme. Modülün kurulu ve etkin olma sınırını ortak skill'e göre koru.
