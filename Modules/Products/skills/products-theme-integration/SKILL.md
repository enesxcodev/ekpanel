---
name: products-theme-integration
description: EkAgent kurumsal ürün kataloğunun kartlarını, kategori filtrelerini, ürün detayını, galerisini ve opsiyonel mevcut formunu Products modülüne bağla. E-ticaret işlemleri veya yeni tema tasarımı üretmek için kullanma.
---

# Products tema entegrasyonu

Önce [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Products kuralını](../../.ai/rules/tema-entegrasyonu.md) oku. İlgili bölümün anlamını ve mevcut modülleri birlikte değerlendir. “Ürünlerimiz”, “Katalog”, “Koleksiyon” gibi başlıklar; ürün fotoğrafları, kategori düğmeleri, detay/galeri düzeni ipucudur. Salt anahtar kelimeyle eşleme yapma; hizmet/proje/ürün veya alan eşleşmesi belirsizse kullanıcıya SOR ve yanıtı bekle.

Alan genişletmede [ortak modül kurallarına](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) başvur; şemayı burada çoğaltma. `product` ve `category` form anahtarlarını koru. Bu skill, verilen temanın veri entegrasyonunu yürütür; tasarım veya örnek müşteri verisi oluşturma izni vermez.

## Gerçek okuma sözleşmesi

Sınıf: `Modules\Products\Services\ProductsFrontendService`, dosya: `app/Services/ProductsFrontendService.php`.

| Metot | Parametreler | Dönüş ve davranış |
|---|---|---|
| `available()` | Yok | `bool`; Products kurulu ve etkinse true. Sınıfın hiç bulunmadığı durumda çağırmadan önce `class_exists` kontrolü gerekir. |
| `products(?int $categoryId = null, ?int $limit = null)` | Kategori ID'si null ise tüm kategoriler ve kategorisizler; limit null ise tüm uygun kayıtlar | `Illuminate\Database\Eloquent\Collection<int, Product>`. Yalnız yayındaki ürünler; kategorisiz veya aktif kategoriye bağlı olanlar. Artan `sort_order`, ardından artan `id`. Olmayan/pasif kategori boş sonuç; limit <= 0 boş koleksiyon. `category`, `imageMedia`, `images` önceden yüklenir. |
| `categories()` | Yok | `Collection<int, ProductCategory>`; yalnız aktif kategoriler, artan `sort_order`, ardından `id`. Boş kategoriler de listelenir. |
| `findBySlug(string $slug)` | Tam ürün slug'ı | `?Product`; listedeki görünürlük kuralları aynıdır. Bulunamazsa null. Aynı ilişkiler yüklenir. Detayda rastgele ilk kaydı seçme. |
| `formFor(Product $product)` | Servisten okunan ürün | `?array{key: string, context: string}`. Ürün veritabanından yeniden okunur. Seçilmiş, aktif ve alanı bulunan form yoksa null; varsayılan forma düşmez. |
| `formContext(int $id, SiteForm $form)` | Ürün ID'si ve çekirdek form modeli | `?array{type: string, id: int, title: string, slug: string}`. Çekirdek `FormContext` için doğrulama callback'i; temadan doğrudan çağırmak yerine `formFor` kullan. Yayın, aktif kategori ve güncel ürün/form bağlantısını kilitli sorguyla doğrular. |

Kapalı modülde liste/kategori metotları boş koleksiyon, tekli/form metotları null döndürür. Servis sıralamayı zaten uygular; tüketici yeni bir öncelik kuralı uydurmaz. Limit sayfalama değildir. Büyük katalog için sayfalama ancak açık ihtiyaçla eklenir.

`Product`: `title`, `slug`, `short_description`, `content`, `image_url`, `images`, `category`, `site_form_id`, `is_active`, `sort_order`, `extra_fields`. Galeri `MediaFile` modelleridir ve pivot `sort_order` ile sıralıdır; kapağın galeriye dahil olması zorunlu değildir. `content`, admin kayıt servisinde ortak HTML temizleyicisinden geçer. Diğer metinleri Blade'in escaping davranışıyla göster. Fiyat/stok/ödeme alanları mevcut değildir.

## Controller'da veri hazırlama

Aşağıdaki view yollarındaki `<tema>` mevcut müşteri temasının adıdır; bu örnekler yeni bir tema veya hazır public route oluşturmaz. Route/controller tema katmanında tanımlanır; bağlantılar mevcut named route ile üretilir. Blade içinde sorgu veya modül keşfi yapma.

Liste action'ı için:

```php
$class = 'Modules\Products\Services\ProductsFrontendService';
$catalog = class_exists($class) ? app($class) : null;
$filters = $request->validate(['category_id' => ['nullable', 'integer', 'min:1']]);
$categoryId = isset($filters['category_id']) ? (int) $filters['category_id'] : null;

return view('frontend.themes.<tema>.products.index', [
    'products' => $catalog?->products($categoryId) ?? collect(),
    'categories' => $catalog?->categories() ?? collect(),
    'selectedCategoryId' => $categoryId,
]);
```

Temanın layout'unu kullanan liste sayfasında bölüme veriyi açıkça geçir:

```blade
@include('frontend.themes.<tema>.sections.products', [
    'products' => $products,
    'categories' => $categories,
    'selectedCategoryId' => $selectedCategoryId,
])
```

Bölüm mevcut tasarımın kartlarını `@forelse($products as $product)` ile doldurur; `{{ $product->title }}`, `{{ $product->short_description }}` ve nullable `$product->image_url` kullanır. Liste boşsa temanın boş durumunu gösterir veya ilgili bölümü kaldırır. Kategori filtresi aynı liste route'una `category_id` gönderir; tümü seçeneği parametreyi kaldırır. Pasif/olmayan kategori seçimi başka kategoriye sessizce geçmez.

Detay action'ı için:

```php
$class = 'Modules\Products\Services\ProductsFrontendService';
abort_unless(class_exists($class), 404);
$catalog = app($class);
$product = $catalog->findBySlug($slug);
abort_if($product === null, 404);

return view('frontend.themes.<tema>.products.show', [
    'product' => $product,
    'productForm' => $catalog->formFor($product),
]);
```

Detay sayfası mevcut tema layout'unu kullanır ve bölümlerine yalnız gereken `product` / `productForm` verisini aktarır. Galeri `$product->images` sırasını korur; medya URL'si `Storage::disk($image->disk)->url($image->path)` ile üretilir. Eksik kapak/galeri için tasarımın boş durumunu uygula.

## Opsiyonel mevcut form

Detayın ilgili bölümünde:

```blade
@if($productForm !== null)
    <x-site-form :key="$productForm['key']" :context="$productForm['context']" />
@endif
```

Mevcut form varyantı gerekiyorsa `variant` / `unstyled` seçeneklerini mevcut form bileşeni sözleşmesine göre ekle. Form seçilmemiş, pasif veya boşsa alanı gösterme. Ayrı teklif/başvuru sistemi, zorunlu modül bağımlılığı veya varsayılan form oluşturma.

`context`, `FormContext::issue` ile şifrelenip bütünlüğü korunan, iki saat geçerli ürün/form bağlamıdır. Render sırasında ve gönderimde çözümlenir; ürünün varlığı, yayını, kategori açıklığı ve aynı forma bağlılığı sunucuda tekrar denetlenir. Değiştirilmiş/süresi dolmuş/geçersiz bağlam 422 doğrulama hatası üretir; sayfa yenilenerek yeni bağlam alınır. Uzun süre önbelleğe alınan HTML'de süresi geçmiş token dağıtma. URL/referrer veya doğrulanmamış gizli ürün alanından bağlam çıkartma.

Başvurular mevcut `form_submissions` içinde kalır. `related_content` JSON alanında doğrulanmış `type`, `id`, `title`, `slug` kopyası saklanır ve admin başvuru detayında gösterilir. Ürün değişse/silinse de geçmiş bilgi korunur. Normal `<x-site-form key="iletisim" />` çağrısı ürünsüz çalışır; `form_context` yoksa ürün bağlamı kaydedilmez. Modül kodu kurulu değilken çekirdek form akışı Products sınıfına bağımlı değildir.

## Tamamlama kontrolü

Liste/kategori filtresi, doğru slug detayı, kapak ve galeri sırası, boş liste/eksik görsel, taslak/pasif kategori, kapalı ve hiç kurulu olmayan modül durumlarını kontrol et. Formlu ve formsuz ürünleri; geçerli, değiştirilmiş, süresi dolmuş ve ürün/form bağı sonradan değişmiş gönderimleri doğrula. `tests/Feature/ProductsCatalogTest.php` bu modülün davranış testlerini içerir. Yeni alan gereksinimini ortak alan altyapısıyla çöz; servis kontrollerini Blade'e kopyalama.
