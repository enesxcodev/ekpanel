# Projeler geliştirme akışı

1. [Ortak modül workflow’unu](../../../../.ai/workflows/modul-gelistirme.md), [modül kurallarını](../../../../.ai/rules/moduller.md) ve [tema kuralını](../rules/tema-entegrasyonu.md) oku.
2. Kategori iş aşamasıdır; yayın durumu ayrı tutulur. Tarih tek proje tarihidir. Çoklu medyada yalnız proje görsellerinden kapak seç. Liste için ortak DataTable kullan.
3. Ortak Media Library ve kullanım takibini koru; kayıt silmek ortak dosyayı silmez. Form ek alanlarını ModuleFeatures/FeatureValues/FieldFiles ile işle.
4. Modül etkinliği, aktif admin ve işlem izinlerini route/controller/request sınırında denetle. Menüleri config/menus.php içinde tut.
5. İlgili yönetim testlerini çalıştır, PHP değişikliklerini Pint ile biçimlendir ve frontend değiştiyse build al. Tema yönergelerini davranışla birlikte güncelle.