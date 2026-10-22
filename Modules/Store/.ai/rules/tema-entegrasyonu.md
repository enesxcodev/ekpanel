# Store tema entegrasyonu

- Satış fiyatı, stok/varyant seçimi, sepete ekleme, checkout ve sipariş içeren alanlar Store kapsamıdır. Salt başlık eşleştirme yapma; kurumsal Products kataloğuyla anlam belirsizse kullanıcıya sor.
- Store ürünlerinin sahibi Store'dur. Products isteğe bağlı tek seferlik içerik/kapak/galeri aktarım kaynağıdır; otomatik senkronizasyon ve zorunlu bağımlılık yoktur. Store medya kullanım referansları kaynak üründen bağımsız korunur.
- Veri giriş noktası `Modules\\Store\\Services\\StoreFrontendService` sınıfıdır. Liste için products(categoryId, search, perPage) paginator'ını, detay için findBySlug(slug), kategori için categories() kullan. Gerçek imzalar, alanlar ve sınırlar [Store skill'inde](../../skills/store-theme-integration/SKILL.md) belgelenmiştir.
- Sınıf hiç kurulu değilse class_exists kontrolü yap; çekirdek controller'da zorunlu constructor bağımlılığı kurma. Servis kapalı modülde boş liste/paginator veya null döndürür. Satış/hesap route'ları etkinlik ve sahiplik denetimini sunucuda uygular.
- Blade'de sorgu, kayıt seçimi, vergi/stok hesabı ve sipariş oluşturma çalıştırma. Ürün/varyantın güncel fiyat/uygunluk kontrolünü Store sepet/sipariş servislerine bırak. Varyantlı ürünü variant_id olmadan sepete ekleme.
- Para en küçük birim tamsayısı + ISO koduyla saklanır; StoreMoney ile gösterilir. İlk etkin birim TRY, vergi dahil/hariç ve misafir checkout Store Settings'tendir. Kullanıcı fiyatı TL olarak girer.
- Kapak image_url, sıralı galeri images ve varyant görsellerini kullan. Görselleri başka kayıttan tahminle seçme. SEO'yu ortak SeoMetadata ile üret; boş meta alanları ürün içeriğine düşer, paylaşım görseli kapağı geçersiz kılabilir.
- Beş ürün sekmesi gerçek kayıt akışına bağlıdır: ürün bilgileri/medya, fiyat/stok, varyantlar, teslimat ve SEO. Ortak slug JS, medya izin/kullanım referansı, HTML temizleme, ikonlu tablo işlemleri ve validation sekmesi davranışını koru.
- Sipariş oluşturma ödeme tahsilatı değildir. İlk sürüm fiziksel ürün, yerel sipariş, manuel ödeme/takip ve sabit teslimat içindir. Gerçek ödeme/taşıyıcı API'leri ayrıca onay gerektirir.
- Misafir sonuç token'ı ve checkout anahtarları hassastır; sipariş listesinde ifşa edilmez. Hesap siparişleri yalnız sahibine açılır. Ürün/ayar değişimi sipariş snapshot'ını değiştirmez.
- Alan genişletme için [ortak modül kurallarını](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) izle. Dinamik alan şemasını burada çoğaltma. Yeni API/alan eklendiğinde skill'i gerçek kodla eş zamanlı güncelle.
