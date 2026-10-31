---
name: brand-theme-integration
description: EkAgent Brand modülünü firmanın çalıştığı markaları tanıtan logo şeridi ve marka kartlarına eşlerken kullan; ürün markası veya proje referansı kapsamını otomatik varsayma.
---

# Brand tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Brand rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Alan genişletme yönergelerini [ortak kaynaktan](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) al.

- Modül kökünde `app/Models/Brand.php`, çeviri modeli ve `database/migrations/` dosyalarını okuyup güncel alanları doğrula. `selectedTranslation` üzerinden `name`, `short_description`, `extra_fields`; Brand üzerinden `image/imageMedia`, `link`, `sort_order`, `is_active` oku. Eksik dil içeriği için başka dilden fallback yapma.
- `app/Http/Controllers/BrandController.php` ekleme ve düzenleme formlarını ayrı ekranlarda gösterir; listede ekleme sayfasına yönlendiren buton bulunur. `ModuleFeatures::form('Brand', 'brand')` ve `FeatureValues::fields` her iki formu hazırlar; `resources/views/partials/form.blade.php` ortak ek alan bileşenini render eder. `app/Http/Requests/SaveBrandRequest.php` ek alanları `FeatureValues::rules` ile doğrular; `app/Services/BrandService.php` değerleri `FeatureValues::prepare` üzerinden `extra_fields` içine kaydeder ve görsel değişimini `FieldFiles` ile yürütür. Bu bağlantıları güncel koddan doğrula.
- Tema okumasında `BrandFrontendService` kullan; görsel ve içerik alanlarını güncel modelden doğrula. Admin DataTable sırası tema yayın politikası değildir.
- Modülün kurulu ve etkin olmasını ortak skill'e göre kontrol et. Admin tablosunun ID sıralaması tema yayın sırası politikası değildir. Tema sıralaması, limit veya marka seçimi gerekli ama tanımsızsa kullanıcıya sor; tahminle devam etme.

## Frontend okuma servisi

- Sınıf: `Modules\Brand\Services\BrandFrontendService`; dosya: `app/Services/BrandFrontendService.php`.
- `getAll(?string $languageCode = null)` bir `Collection<int, Brand>`, `getFirst(?string $languageCode = null)` bir `Brand` veya `null`, `find(int $id, ?string $languageCode = null)` görünür kaydı veya `null` döndürür. Parametresiz dil çözümü açık dil → `brand_language` → `pages_language` → route `lang` → `tr` şeklindedir; yalnız etkin dildeki tam çeviriler kullanılır, fallback yoktur. `getAll` kendiliğinden limit uygulamaz.
- Yalnız aktif ve seçilen dilde çevirisi bulunan kayıtları döner. Sıra `sort_order`, `id` artan şeklindedir. Modül kayıtlı/etkin değilse veya gerekli tablolar yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `imageMedia` ve `selectedTranslation`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
