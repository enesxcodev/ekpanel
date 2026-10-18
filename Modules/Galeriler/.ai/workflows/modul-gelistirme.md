# Fotoğraf ve Video Galerisi geliştirme akışı

1. [Ortak modül workflow’unu](../../../../.ai/workflows/modul-gelistirme.md), [modül kurallarını](../../../../.ai/rules/moduller.md) ve [tema kuralını](../rules/tema-entegrasyonu.md) oku.
2. Kategori ağacını döngülerden koru. Her fotoğraf ayrı kayıt; video yalnız doğrulanmış YouTube/Vimeo bağlantısıdır. Dinamik kategori sekmeleri ve kart görünümünü koru. Arama/tür filtresi açıkken sıralama kapalıdır; sunucuda kategoriler arası sıralamayı reddet.
3. Ortak Media Library ve kullanım takibini koru; kayıt silmek ortak dosyayı silmez. Form ek alanlarını ModuleFeatures/FeatureValues/FieldFiles ile işle.
4. Modül etkinliği, aktif admin ve işlem izinlerini route/controller/request sınırında denetle. Menüleri config/menus.php içinde tut.
5. İlgili yönetim testlerini çalıştır, PHP değişikliklerini Pint ile biçimlendir ve frontend değiştiyse build al. Tema yönergelerini davranışla birlikte güncelle.
İçerik modülünün kimliği Galeriler, namespace’i Modules\Galeriler ve izin/route öneki galeriler’dir. Mevcut Gallery / Media Library ortak dosya kütüphanesidir; App\Services\Media, admin.media.* ve ortak picker kimliklerini değiştirme. Geçmiş gallery_* içerik tabloları veri korunarak galeriler_* adına taşınmıştır; eski oluşturma migration’ını düzenleme.
