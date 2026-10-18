# Blog tema entegrasyonu

- `BlogContent` tek içerik modelidir. Blog, haber ve duyuru ayrımı yalnızca `BlogCategory` ağacındaki kök kategori ve alt kategoriler üzerinden yapılır; tip alanı veya ayrı modül eklenmez.
- Kök kategoriler Blog, Haberler ve Duyurular içerik sınıflarını tanımlar. Yönetim arayüzünde bunları ve alt kategorilerini kullanıcı düzenler. Kök kategori listeleri alt kategori içeriklerini de kapsamalıdır.
- Ziyaretçi tarafına yalnızca `published` durumundaki içerikleri sun. Taslak ve arşiv içeriklerini hiçbir public sorguda döndürme.
- İçerik HTML'sini `HtmlSanitizer` ile kaydet. Temada güvenli içerik gösterimini mevcut servis akışına göre sürdür.
- Öne çıkan görsel medya kütüphanesinden seçilir ve `ContentMediaService` üzerinden bağlanır. Tema uyarlaması görsel yolunu doğrudan tahmin etmemelidir.
- Kategori ve içerik URL davranışı proje genelindeki mevcut route düzenine göre belirlenir; yeni site şablonu veya bağımsız içerik tipi varsayma.
- Ek alanlar `features.json` ve ortak `ModuleFeatures`, `FeatureValues`, `FieldFiles` altyapısından yönetilir.
