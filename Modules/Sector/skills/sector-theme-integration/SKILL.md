---
name: sector-theme-integration
description: Firmanın çalıştığı iş alanlarını anlatan sektör bölümlerini Sector modülüne eşle.
---

# Sector tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Sector rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku.

- Güncel `app/Models/Sector.php`, `app/Models/SectorTranslation.php`, migration, `features.json` ve servis kodunu incele. Sektör içeriğini hizmet, marka ve müşteri/proje referanslarından bölümün amacı ve görsel yapısıyla ayır.
- Çevrilen alanlar `selectedTranslation` üzerindedir: `title`, `slug`, `short_description`, `content`, `meta_title`, `meta_keywords`, `meta_description`, `extra_fields`. `image`, `imageMedia`, `sort_order`, `is_active` ve kimlik Sector kaydında ortaktır.
- Yalnız seçili aktif dilde çevirisi olan sektörler frontend sonucuna girer. Çeviri yoksa başka dilden metin kullanma. Public detay route'u tanımlı olmadığından slug'a bağlı URL veya detay sayfası uydurma.
- Veriyi `SectorFrontendService` üzerinden al. Nullable sıralamayı mevcut `sort_order` alanından kullan; seçim ve filtre kurallarını kodda veya kullanıcı gereksiniminde yoksa uydurma.

## Frontend okuma servisi

- Sınıf: `Modules\Sector\Services\SectorFrontendService`; dosya: `app/Services/SectorFrontendService.php`.
- `getAll(?string $languageCode = null)` bir `Collection<int, Sector>`, `getFirst(?string $languageCode = null)` bir `Sector` veya `null`, `find(int $id, ?string $languageCode = null)` görünür kaydı veya `null` döndürür. İsteğe bağlı dil kodu yalnız etkin `Language` kaydı olmalıdır; verilmezse request `sector_language` bağlamı, o da yoksa `tr` kullanılır. Pasif veya geçersiz dil boş sonuç verir.
- Yalnız aktif Sector ve seçilen dilde çevirisi olan kayıtlar döner; çevirisiz kayıtlarda metin fallback'i yoktur. Sıra `sort_order`, ardından `id` artandır.
- `selectedTranslation`, `translations` (yalnız seçilen dil) ve `imageMedia` ilişkileri yüklenir. Çevrilen alanları `selectedTranslation` üzerinden, görsel/sıra/yayını Sector üzerinden oku.
- Modül devre dışıysa veya `sectors`, `languages`, `sector_translations` tablolarından biri yoksa koleksiyon metodu boş koleksiyon, tek kayıt metotları `null` döndürür.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
