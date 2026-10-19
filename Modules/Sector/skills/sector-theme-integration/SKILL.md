---
name: sector-theme-integration
description: Firmanın çalıştığı iş alanlarını anlatan sektör bölümlerini Sector modülüne eşle.
---

# Sector tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Sector rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku.

- Güncel `app/Models/Sector.php`, migration, `features.json` ve servis kodunu incele. Sektör içeriğini hizmet, marka ve müşteri/proje referanslarından bölümün amacı ve görsel yapısıyla ayır; eşleşme belirsizse kullanıcıya sor.
- Mevcut alanları yeniden kullan: `title`, `slug`, `sort_order`, `short_description`, `content`, `image`, `is_active`, `extra_fields`. Nullable sıralamayı mevcut `sort_order` alanından al; detay sayfası veya başka modülle ilişki varmış gibi davranma.
- Veriyi `SectorFrontendService` üzerinden al. Yönetilebilir yeni içerik gerekiyorsa [ortak alan genişletme kurallarına](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) uy.

## Frontend okuma servisi

- Sınıf: `Modules\Sector\Services\SectorFrontendService`; dosya: `app/Services/SectorFrontendService.php`.
- `getAll()` bir `Collection<int, Sector>`, `getFirst()` bir `Sector` veya `null`, `find(int $id)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Yalnız aktif kayıtları döner. Sıra mevcut `sort_order`, `id` artan şeklindedir. Modül kayıtlı/etkin değilse veya gerekli modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `imageMedia`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
