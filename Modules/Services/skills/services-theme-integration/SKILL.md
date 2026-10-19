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
- `getAll(?int $categoryId = null)` bir `Collection<int, Service>`, `getFirst(?int $categoryId = null)` bir `Service` veya `null`, `find(int $id)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Yalnız aktif kayıtları döner. Sıra mevcut `sort_order`, `id` artan şeklindedir. Modül kayıtlı/etkin değilse veya gerekli modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `category`, `imageMedia`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse kategorisiz olanlar dahil tüm görünür kayıtlar döner; parametre verilirse yalnız ilgili kategori filtrelenir. Bulunmayan kategori boş sonuç verir; sabit kategori ID'si veya adı varsayma.
- Bağlı kategorisi pasif olan hizmetler gizlenir; kategorisiz aktif hizmetler korunur. Ana sayfadaki kart/kompakt liste tercihi tema sunumudur; `extra_fields.homepage_display` eşlemesini mevcut tema veri akışında uygula.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
