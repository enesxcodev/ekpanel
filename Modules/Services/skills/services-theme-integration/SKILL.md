---
name: services-theme-integration
description: Firmanın hizmet kartlarını, listelerini ve hizmet kategorilerini Services modülüne eşle.
---

# Services tema entegrasyonu

[Ortak tema skill’ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Services rule’unu](../../.ai/rules/tema-entegrasyonu.md) oku.

- Modülün `app/Models/`, `database/migrations/`, `features.json` ve form/servis kodunu inceleyerek güncel alanları doğrula. Hizmet kartını `Service`, kategori grubunu `ServiceCategory` ile eşle; belirsizse sor.
- Tema okumalarında `ServicesFrontendService` kullan; admin yazma işlemlerini mevcut `ServiceService` / `ServiceCategoryService` akışında tut. `features.json` içindeki `service` ve `category` form anahtarlarını koru; alan genişletmede [ortak kurallara](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) başvur.

## Frontend okuma servisi

- Sınıf: `Modules\Services\Services\ServicesFrontendService`; dosya: `app/Services/ServicesFrontendService.php`.
- `getAll(?int $categoryId = null, ?string $languageCode = null)` bir `Collection<int, Service>`, `getFirst(?int $categoryId = null, ?string $languageCode = null)` bir `Service` veya `null`, `find(int $id, ?string $languageCode = null)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Dil kodunu sayfanın dilinden açıkça geçir: `$services = $servicesFrontend->getAll(languageCode: 'en');`, `$service = $servicesFrontend->getFirst(languageCode: 'en');` veya `$service = $servicesFrontend->find($id, 'en');`. Dil kodu verilmezse yalnızca `services_language` istek özelliği, o da yoksa `tr` kullanılır; genel uygulama dili veya başka bir modülün dil bağlamı kendiliğinden okunmaz.
- Yalnız seçilen aktif dilde çevirisi bulunan aktif kayıtlar döner. Pasif ya da bulunmayan dil boş sonuç verir; başka dile fallback yapılmaz. Bağlı kategorinin aktif ve aynı dilde çevrilmiş olması gerekir; kategorisiz hizmetler de gösterilir. Dönen hizmetin metin, slug, SEO ve `extra_fields` alanları ile `category->name` ve `category->extra_fields` seçilen çeviriden, görsel, ikon, kategori ilişkisi, sıra ve durum ortak kayıttan okunur.
- Sıra mevcut `sort_order`, `id` artan şeklindedir. Modül kayıtlı/etkin değilse veya `languages`, `services`, `service_categories`, `service_translations`, `service_category_translations` tablolarından biri yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `category`, `imageMedia`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse kategorisiz olanlar dahil tüm görünür kayıtlar döner; parametre verilirse yalnız ilgili kategori filtrelenir. Bulunmayan kategori boş sonuç verir; sabit kategori ID'si veya adı varsayma.
- Bağlı kategorisi pasif olan hizmetler gizlenir; kategorisiz aktif hizmetler korunur. Ana sayfadaki kart/kompakt liste tercihi tema sunumudur; `extra_fields.homepage_display` eşlemesini mevcut tema veri akışında uygula.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
