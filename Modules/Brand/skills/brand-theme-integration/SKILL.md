---
name: brand-theme-integration
description: EkAgent Brand modülünü firmanın çalıştığı markaları tanıtan logo şeridi ve marka kartlarına eşlerken kullan; ürün markası veya proje referansı kapsamını otomatik varsayma.
---

# Brand tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Brand rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Alan genişletme yönergelerini [ortak kaynaktan](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) al.

- Modül kökünde `app/Models/Brand.php`, `database/migrations/` ve `features.json` dosyalarını okuyup güncel alanları doğrula. `name/image/short_description/link` öğelerini marka adı/logo/açıklama/bağlantıyla eşle. Eksik isteğe bağlı değerler için içerik uydurma; modül veya alan eşleşmesi belirsizse kullanıcıya SOR ve yanıtı bekle.
- `app/Http/Controllers/BrandController.php` ekleme ve düzenleme formlarını ayrı ekranlarda gösterir; listede ekleme sayfasına yönlendiren buton bulunur. `ModuleFeatures::form('Brand', 'brand')` ve `FeatureValues::fields` her iki formu hazırlar; `resources/views/partials/form.blade.php` ortak ek alan bileşenini render eder. `app/Http/Requests/SaveBrandRequest.php` ek alanları `FeatureValues::rules` ile doğrular; `app/Services/BrandService.php` değerleri `FeatureValues::prepare` üzerinden `extra_fields` içine kaydeder ve görsel değişimini `FieldFiles` ile yürütür. Bu bağlantıları güncel koddan doğrula.
- Veri okumada mevcut `Modules\Brand\Models\Brand` sorgu örüntüsünü ve `app/DataTables/BrandsDataTable.php` dosyasını incele. `BrandService` mevcut durumda `save/delete` sağlar; olmayan okuma metodu, ilişki veya aktiflik filtresi çağırma. Ek facade/helper veya tema veri sınıfı zorunluluğu yoktur.
- Modülün kurulu ve etkin olmasını ortak skill'e göre kontrol et. Admin tablosunun ID sıralaması tema yayın sırası politikası değildir. Tema sıralaması, limit veya marka seçimi gerekli ama tanımsızsa kullanıcıya sor; tahminle devam etme.
