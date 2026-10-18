# Fotoğraf ve Video Galerisi tema entegrasyonu

- “Fotoğraf galerisi”, “videolar”, “ofisimiz”, “tesisimiz”, “etkinliklerden kareler” ve görsel ızgaraları eşleme ipuçlarıdır. Bölümün anlamı ve yapısını diğer modüllerle birlikte değerlendir; salt başlığa göre karar verme. Modül/alan eşleşmesi belirsizse kullanıcıya sor.
- GalerilerCategory üst/alt kategori (parent_id), name, icon ve short_description alanlarını; GalerilerItem kategori, type (photo/video), isteğe bağlı title/description, imageMedia ve video_url alanlarını taşır.
- Galeri yayın durumu içermez. Kategorinin items ilişkisi sort_order ve id ile sıralıdır. VideoLink yalnız HTTPS YouTube/Vimeo adreslerini doğrular; video önizlemesi için modelin video alanını kullan. Keyfî URL veya HTML iframe kodunu doğrudan basma. Galeri slider veya albüm değildir.
- Model/servis erişimini kullan; yalnız tema için ek facade/helper oluşturma. Kurulu ve etkin olma kontrolünü koru.
- Ortak alan genişletme için [modül kurallarını](../../../../.ai/rules/moduller.md) izle; features.json ve extra_fields altyapısını kopyalama.
- [Modüle özgü skill](../../skills/galeriler-theme-integration/SKILL.md), [ortak tema skill’i](../../../../.claude/skills/theme-integration/SKILL.md) ve [geliştirme workflow’u](../workflows/modul-gelistirme.md) birlikte kullanılır.
İçerik modülünün kimliği Galeriler, namespace’i Modules\Galeriler ve izin/route öneki galeriler’dir. Mevcut Gallery / Media Library ortak dosya kütüphanesidir; App\Services\Media, admin.media.* ve ortak picker kimliklerini değiştirme. Geçmiş gallery_* içerik tabloları veri korunarak galeriler_* adına taşınmıştır; eski oluşturma migration’ını düzenleme.
