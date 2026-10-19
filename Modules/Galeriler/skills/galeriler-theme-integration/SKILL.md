---
name: galeriler-theme-integration
description: Fotoğraf ve Video Galerisi içeriğini anlamı ve yapısıyla doğru tema bölümüne eşler.
---

# Fotoğraf ve Video Galerisi tema entegrasyonu

[Ortak tema skill’ini](../../../../.claude/skills/theme-integration/SKILL.md), [modül kuralını](../../.ai/rules/tema-entegrasyonu.md) ve [ortak modül kurallarını](../../../../.ai/rules/moduller.md) oku.

- Fotoğraf/video ızgaraları ve kategori sekmeleri galeri adayıdır; slider, marka logosu veya proje kayıtlarıyla karıştırma.
- GalerilerCategory üst/alt kategori (parent_id), name, icon ve short_description alanlarını; GalerilerItem kategori, type (photo/video), isteğe bağlı title/description, imageMedia ve video_url alanlarını taşır.
- Galeri yayın durumu içermez. Kategorinin items ilişkisi sort_order ve id ile sıralıdır. VideoLink yalnız HTTPS YouTube/Vimeo adreslerini doğrular; video önizlemesi için modelin video alanını kullan. Keyfî URL veya HTML iframe kodunu doğrudan basma. Galeri slider veya albüm değildir.
- Eşleme belirsizse kullanıcıya sor. Mevcut model/servis erişimini ve ortak alan altyapısını kullan; yeni tema facade/helper zorunluluğu getirme.
İçerik modülünün kimliği Galeriler, namespace’i Modules\Galeriler ve izin/route öneki galeriler’dir. Mevcut Gallery / Media Library ortak dosya kütüphanesidir; App\Services\Media, admin.media.* ve ortak picker kimliklerini değiştirme. Geçmiş gallery_* içerik tabloları veri korunarak galeriler_* adına taşınmıştır; eski oluşturma migration’ını düzenleme.

## Frontend okuma servisi

- Sınıf: `Modules\Galeriler\Services\GalerilerFrontendService`; dosya: `app/Services/GalerilerFrontendService.php`.
- `getAll(?int $categoryId = null)` bir `Collection<int, GalerilerItem>`, `getFirst(?int $categoryId = null)` bir `GalerilerItem` veya `null`, `find(int $id)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Modelde aktiflik/yayın alanı yoktur; böyle bir filtre uydurulmaz, tüm kayıtlar okunabilir. Sıra mevcut `sort_order`, `id` artan şeklindedir. Modül kayıtlı/etkin değilse veya gerekli modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `category`, `imageMedia`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse kategorisiz olanlar dahil tüm görünür kayıtlar döner; parametre verilirse ilgili kategori ve alt kategorileri filtrelenir. Bulunmayan kategori boş sonuç verir; sabit kategori ID'si veya adı varsayma.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
