---
name: projects-theme-integration
description: Projeler içeriğini anlamı ve yapısıyla doğru tema bölümüne eşler.
---

# Projeler tema entegrasyonu

[Ortak tema skill’ini](../../../../.claude/skills/theme-integration/SKILL.md), [modül kuralını](../../.ai/rules/tema-entegrasyonu.md) ve [ortak modül kurallarını](../../../../.ai/rules/moduller.md) oku.

- Kategoriye bağlı iş örnekleri, proje kapakları ve tarih/içerik içeren detaylar Projeler adayıdır; müşteri yorumları veya sıradan görsel ızgaralarıyla karıştırma.
- Proje kategori ID'si, proje tarihi, yayın durumu, sıra, kapak ve görsel galerisi diller arasında ortaktır. Kategori ikonu ortaktır. `ProjectTranslation` title/slug/content/extra_fields, `ProjectCategoryTranslation` name/slug/extra_fields alanlarını taşır.
- Public kart metinlerini `selectedTranslation` üzerinden, kategori adını `category.selectedTranslation.name` üzerinden oku. Başka dil fallback'i yapma. Eksik proje veya kategori çevirisi o projeyi gizler.
- Eşleme belirsizse kullanıcıya sor. Mevcut model/servis erişimini ve ortak alan altyapısını kullan; yeni tema facade/helper zorunluluğu getirme.

## Frontend okuma servisi

- Sınıf: `Modules\Projects\Services\ProjectsFrontendService`; dosya: `app/Services/ProjectsFrontendService.php`.
- `getAll(?int $categoryId = null, ?string $languageCode = null)` bir `Collection<int, Project>`, `getFirst(?int $categoryId = null, ?string $languageCode = null)` bir `Project` veya `null`, `find(int $id, ?string $languageCode = null)` görünür kaydı veya `null` döndürür. `getAll` limit uygulamaz.
- Açık dil kodu veya request bağlamı yalnız aktif `Language` ile çözülür; geçersiz/pasif dil boş sonuç verir ve global locale değiştirilmez. Yalnız aktif, seçili dil çevirisi ve aynı dilde kategori çevirisi bulunan kayıtlar görünür. Sıra `sort_order`, `id` artandır.
- Modül devre dışıysa veya gereken modül/çeviri tablosu yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `selectedTranslation`, `category.selectedTranslation`, `coverMedia`, `images`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse tüm görünür ve kategori çevirisi bulunan projeler döner; verilirse ortak kategori ID'sine göre filtrelenir. Bulunmayan kategori boş sonuç verir.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
