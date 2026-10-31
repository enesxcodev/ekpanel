---
name: blog-theme-integration
description: Blog, haber ve duyuru içeriklerini müşteri temasına bağla.
---

# Blog tema entegrasyonu

Bu modülün [tema entegrasyonu kurallarını](../../.ai/rules/tema-entegrasyonu.md) izle.

- `BlogContent` ortak içerik kaynağıdır. İçerik grupları kategori ağacındaki Blog, Haberler ve Duyurular kökleri ile tanımlanır.
- Public görünümde yalnızca yayındaki kayıtları göster; taslak veya arşiv kaydını sunma.
- Ana kategori listeleri alt kategorileri de kapsamalıdır.
- HTML içerik `BlogService` sanitizasyonundan geçmelidir.
- Öne çıkan görsel `image_url` erişimcisi ve medya kullanım ilişkisi üzerinden alınır.
- SEO ve müşteri temasına özgü gösterim, dile özgü slug, içerik ve kategori çevirisi ile ortak medya davranışlarını korumalıdır.

## Frontend okuma servisi

- Sınıf: `Modules\Blog\Services\BlogFrontendService`; dosya: `app/Services/BlogFrontendService.php`.
- `getAll(?int $categoryId = null, ?string $languageCode = null)` bir `Collection<int, BlogContent>`, `getFirst(?int $categoryId = null, ?string $languageCode = null)` bir `BlogContent` veya `null`, `find(int $id, ?string $languageCode = null)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- `paginate(int $perPage = 12, ?int $categoryId = null, ?string $languageCode = null)` sayfalayıcı veya dil/modül kullanılamıyorsa `null`; `findBySlug(string $slug, ?string $languageCode = null)` görünür kayıt veya `null` döndürür. Sayfalayıcı mevcut sorgu parametrelerini taşır.
- Örnek: `$blogs = $blogFrontend->getAll(languageCode: 'en');`, `$article = $blogFrontend->findBySlug('article-slug', 'en');`, `$page = $blogFrontend->paginate(languageCode: 'en');`. Dönen kayıtların metin, slug, SEO ve ek alanları seçili çeviriden, `category->path` seçili kategorilerden okunur; kapak ve yazar ortaktır.
- Dil belirtilmezse Blog isteğinin dil bağlamı, bu yoksa Türkçe (`tr`) kullanılır. Dil aktif olmalı; yazının ve kategori zincirinin tamamının çevirisi bulunmalıdır. Başka dile fallback yapılmaz. Metinler ve dinamik alanlar çeviride; kategori ilişkisi, yazar, kapak ve yayın durumu ana kayıttadır.
- Yalnız `status=published` kayıtları döner. Tanımlı bir iş sırası yoktur; ilk/son/en yeni gibi bir öncelik garantisi verilmez. Modül kayıtlı/etkin değilse veya gerekli modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `category`, `imageMedia`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse tüm görünür kayıtlar döner; parametre verilirse ilgili kategori ve alt kategorileri filtrelenir. Bulunmayan kategori boş sonuç verir; sabit kategori ID'si veya adı varsayma.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
