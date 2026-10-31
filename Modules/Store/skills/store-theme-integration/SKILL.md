---
name: store-theme-integration
description: EkAgent Store ürün listesi, detay, varyant seçimi, sepet, yerel checkout ve sipariş ekranlarını mevcut temaya bağla.
---

# Store tema entegrasyonu

Önce [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md), [Store kuralını](../../.ai/rules/tema-entegrasyonu.md) ve ortak [modül kurallarını](../../../../.ai/rules/moduller.md) oku. “Mağaza”, “satın al”, fiyat/stok, varyant seçimi, sepet ve sipariş özeti Store ipuçlarıdır. Salt başlıkla eşleme yapma; kurumsal katalog Products, satış akışı Store'dur. Eşleşme gerçekten belirsizse kullanıcıya sor.

## Veri sahipliği ve kullanılabilirlik

Store kendi ürün, kategori, varyant ve siparişlerinin sahibidir. Products isteğe bağlıdır. Adminin katalogdan aktarımı başlık/açıklama/kapak/sıralı galeriyi tek seferlik kopyalar; ortak medya kayıtlarına ayrı Store kullanım referansları ekler. Kaynak silinmesi veya Products'ın kapanması Store içeriğini güncellemez. Kaynak ID köken bilgisidir; tema Products ilişkisini takip ederek Store içeriğini yeniden okumaz.

Store kodu hiç kurulmamış olabilir. Tema controller'ı sınıfı çözmeden önce `class_exists('Modules\\Store\\Services\\StoreFrontendService')` kullanır. Kullanılabilirlik sonucunu tema Blade'inde keşfetme. Blade'de sorgu, fiyat/vergi/stok hesabı veya sipariş oluşturma çalıştırma.

## Gerçek frontend okuma sözleşmesi

Sınıf: `Modules\\Store\\Services\\StoreFrontendService`, dosya: `app/Services/StoreFrontendService.php`.

| Metot | Dönüş ve davranış |
|---|---|
| `available(): bool` | Modül bulunuyor ve etkinse true; bulunmayan/kapalı modülde false. |
| `products(?int $categoryId = null, ?string $search = null, int $perPage = 12, ?string $languageCode = null)` | `LengthAwarePaginator<int, StoreProduct>`. 1–100 sayfa boyutu. Yalnız seçili aktif dilde çevirisi bulunan aktif ürünler; kategorili ürün için aynı dilde çevirisi olan aktif kategori gerekir. Arama localized title'da, kategori filtresi canonical ID'de. Kapalı modülde boş paginator. |
| `categories(?string $languageCode = null)` | Seçili aktif dilde çevirisi bulunan aktif kategoriler; artan sort_order/id. Kapalı modülde boş collection. |
| `findBySlug(string $slug, ?string $languageCode = null)` | Seçili dilin localized slug'ında görünür tek ürün veya null. Otomatik TR fallback yoktur. |
| `sellable(int $productId, ?int $variantId, bool $lock = false)` | Sepet/sipariş servislerinin kullandığı `[StoreProduct, ?StoreVariant]`. Varyant zorunluluğu, aktiflik ve ürün sahipliği doğrulanır; uygunsuz seçim ValidationException üretir. Tema liste/detay verisi için bu yazma akışı yardımcısını çağırma. |

Ürünlerde `category`, `imageMedia`, `images` ve yalnız aktif/arşivlenmemiş `variants.imageMedia` önceden yüklenir. `image_url` nullable kapak URL'sidir. Galeri MediaFile collection'ıdır; pivot sort_order sırası korunur. Galeri URL'si `Storage::disk($image->disk)->url($image->path)` ile üretilir. Eksik görseli temanın boş durumuyla göster.

Localized alanlar: title, slug, short_description, content, meta_title, meta_description, delivery_description ve extra_fields. Kategori name/slug/extra_fields çevrilir. SKU, fiyat, stok, kategori ID, medya, aktiflik/sıralama, option_groups, option_values ve variant title globaldir. Varyant option metinlerini çevirme; metinler canonical kombinasyon eşleşmesidir. Yeni ürün/kategori TR oluşturulur. Dinamik alan genişletmede ortak ModuleFeatures/FeatureValues/FieldFiles sözleşmesini kullan.

Varyant option_values bir grup adı → seçili değer eşlemesidir; option_groups ürün üzerinde name/virgülle ayrılmış values gruplarıdır. Varyant fiyatı null ise temel fiyat geçerlidir. Varyantlı üründe üst ürün stoğu toplam stok değildir; satılan varyantın stoğu kullanılır. Tema stok düşürmez.

## Liste ve detay bağlama

Controller'da frontend servisini isteğe bağlı çöz; listede paginator ve kategorileri view'a açıkça aktar. Detayda findBySlug null ise 404 ver. Genel örnek akışlar `PublicStoreController` ve `resources/views/public/` altındadır. Müşteri teması kendi layout/section düzenini ve ortak head/SEO çıktısını kullanır; modülün örnek görünümünü yeni tema diye kopyalama.

Detay SEO'su ortak `App\\Services\\Settings\\SeoMetadata::build(['page' => $product])` ile hazırlanır. Meta başlık/açıklama boşsa ürün adı/kısa açıklama kullanılır; paylaşım görseli seçilmişse og/twitter image alanları controller'da onunla değiştirilir. Açıkça hazırlanmış seoMetadata layout tarafından korunur. Bir sayfada yalnız tek SEO çıktısı üret.

Metinleri Blade escaping ile göster. content ortak HtmlSanitizer ile hazırlanır; public controller da görüntülemeden önce temizler. Kullanıcı içeriğini script veya doğrulanmamış HTML olarak ekleme.

## Para, sepet ve checkout

`StoreMoney::format(int $minor, string $currency = 'TRY'): string` tutarı gösterir. Tutarlar en küçük birim tamsayısıdır. Etkin para birimi ilk sürümde TRY; kur dönüşümü yok. Vergi dahil/hariç ve oran Settings'ten gelir. Tema bu hesapları yeniden üretmez; sepet quote sonucunu kullanır.

Route'lar:
- `store.index`, `store.show` (slug): liste ve detay.
- `store.localized.index`, `store.localized.show`: `/{lang}/magaza` ve `/{lang}/magaza/urun/{slug}`. Dil aktif `languages.code` üzerinden çözülür.
- `store.cart`, `store.cart.add` POST: product_id, opsiyonel variant_id ve quantity (1–100). Varyantlı üründe geçerli variant_id zorunludur.
- `store.cart.update` PUT (index): quantity 0 satırı kaldırır, 1–100 adedi günceller.
- `store.checkout.form` GET: güncel fiyat/teslimat özeti ve session checkout anahtarı.
- `store.checkout` POST: doğrulanmış müşteri/adres, delivery_method, checkout_key ve sunucunun ürettiği quote fingerprint.
- `store.order.complete` GET: number + tahmin edilemeyen token; token'ı listelerde veya üçüncü taraflara aktarma.
- `store.account`, `store.account.order`: yalnız etkin Users ve sipariş sahibi web hesabı.

Formlarda CSRF kullan. HTML'den gönderilen fiyat veya toplam güvenilir değildir. StoreCartService::quote gerçek görünürlük/stok/fiyat/vergi/teslimat kurallarını uygular. Fiyat/ayar değişmişse checkout yeniden onay ister. Sipariş oluşturulunca ödeme bekliyor olur; gerçek tahsilat yapılmış gibi gösterme. Sipariş satırı localized ürün adını ve locale kodunu sipariş anında snapshot olarak saklar; sonradan translation değişikliği eski siparişi değiştirmez. Misafir kapalıysa web girişi gerekir; Users bulunmuyorsa admine çözüm gösterilir, hesap zorunluluğu atlanmaz. Giriş sonrasında sepet korunur.

İlk sürüm fiziksel ürün, sabit ücret/ücretsiz eşik/mağazadan teslim, manuel ödeme ve manuel takip bilgisi içerir. Gerçek ödeme/taşıyıcı API'si, dijital indirme, kur dönüşümü ve kısmi iade yoktur. İptal edilen ödenmiş siparişin para iadesi ayrı manuel kayıttır.

## Operasyon ve doğrulama

StoreSettingsSeeder yalnız sistem tanımlarını oluşturur, kullanıcı değerlerini korur. module.json panel.install.ensure_on_enable=true sayesinde ortak queue etkinleştirmesinde de çalışır. Store ayar sekmesi yalnız modül etkin ve admin yetkiliyse gösterilir. Kaynak klasörünü kopyalamak tek başına migration/seeder/asset yayınını çalıştırmaz; ortak modül etkinleştirme akışını kullan.

Ödenmemiş siparişin rezervasyonu 24 saattir. Provider'ın kaydettiği `store:expire-reservations` schedule görevi her dakika süresi dolan siparişi iptal eder ve stoğu bir kez iade eder. Yerelde `php artisan schedule:work`, sunucuda standart Laravel scheduler kurulumu gerekir. Queue worker tek başına scheduler'ı çalıştırmaz.

Feature testleri `tests/Feature/StoreCommerceTest.php`, `StoreBoundaryTest.php`, `StoreLifecycleTest.php` ve `StoreManagementTest.php` içindedir. Liste/detay, kapak/galeri, ilk kayıt ve edit varyantları, mobil sekme/hata, misafir/hesap dalları, stok/fiyat değişimi, token/hesap yetkisi ve modül yok/kapalı durumlarını kapsamına göre kontrol et. Lifecycle testinde Composer/migration süreçleri fake'tir; gerçek panel → queue komutu → gerçek ayar seeder'ı çalıştırılır. Bunu gerçek ZIP kurulumu veya çok süreçli stok stres testi diye raporlama.
