# Blog tema entegrasyonu

- `BlogContent` tek içerik modelidir. Blog, haber ve duyuru ayrımı yalnızca `BlogCategory` ağacındaki kök kategori ve alt kategoriler üzerinden yapılır; tip alanı veya ayrı modül eklenmez.
- Kök kategoriler Blog, Haberler ve Duyurular içerik sınıflarını tanımlar. Yönetim arayüzünde bunları ve alt kategorilerini kullanıcı düzenler. Kök kategori listeleri alt kategori içeriklerini de kapsamalıdır.
- Ziyaretçi tarafına yalnızca `published` durumundaki içerikleri sun. Taslak ve arşiv içeriklerini hiçbir public sorguda döndürme.
- İçerik HTML'sini `HtmlSanitizer` ile kaydet. Temada güvenli içerik gösterimini mevcut servis akışına göre sürdür.
- Öne çıkan görsel medya kütüphanesinden seçilir ve `ContentMediaService` üzerinden bağlanır. Tema uyarlaması görsel yolunu doğrudan tahmin etmemelidir.
- Kategori ve içerik URL davranışı proje genelindeki mevcut route düzenine göre belirlenir; yeni site şablonu veya bağımsız içerik tipi varsayma.
- Ek alanlar `features.json` ve ortak `ModuleFeatures`, `FeatureValues`, `FieldFiles` altyapısından yönetilir.
- Yazı ve kategori kimlikleri, kategori ilişkisi, kapak, yazar ve yayın durumu ortaktır; başlık, içerik, slug, SEO ve ek alan değerleri dil çevirisindedir. Tema çeviriyi doğrudan sorgulamaz, `BlogFrontendService` kullanır.
- Yalnız aktif dilde çevirisi bulunan yazıları göster; yazının bütün kategori zincirinde aynı dilde çeviri bulunmalıdır. Eksik çeviride başka dile fallback yapma. Kategori filtrelerinde ortak kategori kimliği kullan.
- Türkçe adresler `blog.public.index` ve `blog.public.show`, diğer diller `blog.localized.index` ve `blog.localized.show` route adlarını kullanır; detayda seçili dilin slug değerini geçir.

- Tema okumasında `BlogFrontendService` kullan. Güncel metotlar, parametreler, dönüş türleri ve yayın/sıralama davranışı [modülün tema skill'inde](../../skills/blog-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce bu sözleşmeyi oku.
