# Proje içerik kaynakları — lead ara raporu

**Tarih:** 17 Ekim 2026  
**Ajan/model:** Lead mimari ajanı — GPT-6.1 Sol  
**Kapsam:** Pages, sayfa kategorileri, Services ve hizmet kategorilerinin kaynak kodundan salt okunur incelemesi. Ürün kaynaklarının varlığı ilgili modül/model dosya adlarıyla kontrol edildi. Mevcut menü aranmadı; uygulama kodu değiştirilmedi. Bu rapor öneriye girdidir, onaylanmış mimari karar değildir.

## İnceleme zemini ve sınırlar

`.ai/rules/index.md`, proje amacı, mimari kararlar, mimari kod yapısı, modül, admin ve yetkilendirme kuralları okundu; `.ai/rules` üzerinde ilgili anahtar kelime araması yapıldı. `laravel-best-practices` skill'inin mimari, model/ilişki ve routing yönergeleri kullanıldı. Boost araçları bu oturumda sunulmadığından kaynak dosyaları ve dar kapsamlı Artisan route listeleri kullanıldı. `composer show laravel/framework --no-interaction --no-plugins` kurulu sürümü **v13.36.0** olarak doğruladı. Framework route üretim kaynakları yerelden kontrol edildi; yeni bir paket API'si uygulanmadı.

Veritabanı kayıtları ve canlı şema sorgulanmadı. Aşağıdaki alanlar model, doğrulama ve migration dosyalarının gösterdiği yapıdır; migration'ların bütün ortamlarda çalıştırıldığı iddia edilmez. `php artisan route:list --method=GET --name=pages --no-interaction` ve `--name=services` ile ilgili kayıtlı GET route'ları da doğrulandı. Uygulama davranışı için test çalıştırılmadı.

## Kaynakların karşılaştırması

| Kaynak | Gerçek yapı | Tekil seçim | Otomatik alt öğe listesi | Ziyaretçi hedefi durumu |
|---|---|---|---|---|
| Page | `page_category_id` isteğe bağlı; `title`, `slug`, `status`, `sort_order` | Kayıt ID'si ile elverişli; kategorisiz sayfalar da seçilmeli | Kategori ilişkisi üzerinden yayımlanmış sayfalar listelenebilir | `pages.show` ve `url_path` var |
| PageCategory | `parent_id`, `name`, `slug`, `sort_order`; `children`, `pages`, `descendantIds` | Kategori ID'si seçilebilir | Doğrudan sayfalar, alt kategoriler ve alt ağaç ayrı kapsamlar olarak mümkün | `url_path` var; fakat kategori arşivi route/controller davranışı yok |
| Service | İsteğe bağlı tek `service_category_id`; `title`, `is_active`, içerik/görsel alanları | Kayıt ID'si ile elverişli | Kategoriye bağlı aktif hizmetler sorgulanabilir | İncelenen yapıda public detay route'u, slug ve URL üretimi yok |
| ServiceCategory | `name`, `extra_fields`; `services` ilişkisi | Kategori ID'si seçilebilir | Doğrudan hizmetler listelenebilir | Kategori arşivi ve public URL üretimi yok; `parent_id` yok |
| Ürün / ürün kategorisi | `Modules` altında ürün modülü ve ilgili model dosyaları bulunamadı | Mevcut bir kaynak olarak sunulamaz | Şimdiki yapıda doğrulanamaz | Gelecekte ilgili modülün gerçek modeli ve public hedefiyle netleşmeli |

## Pages bulguları

- `PageCategory::children()` sırası `sort_order`, ardından `name`; `pages()` sırası `sort_order`, ardından `title`. Dinamik menü sırası bu tercihle uyumlu olabilir; eşitliklerde sabit ID sırası ekleme ihtiyacı uygulama aşamasında değerlendirilmeli.
- Sayfalar kategorilere tekil bağlıdır; bir sayfanın birden çok kategoriye bağlı olduğu veya sayfanın başka bir sayfaya ebeveyn olduğu bir ilişki yok. Çok seviyeli ağaç kategorilerdedir. Menü ağacı bundan bağımsız olabilir.
- `status` değerleri `draft` ve `published`. Public `show` yalnız `published` sayfaları çözer. Dinamik listeler taslakları dışlamalı; elle seçilen kayıt taslağa çekilirse hedef yeniden değerlendirilmelidir.
- `Page::url_path`, kategori atalarını ve sayfa slug'ını birleştirir. Kayıt ID'si saklayıp adresi güncel içerikten çözmek, kategori veya slug değişiminde menünün eski URL'yi tutmasını önler. Eski dış bağlantılar için yönlendirme ayrı bir karardır.
- `pages.show` yakalayıcı `/{path}` route'udur. Controller son parçayı **sayfa slug'ı** olarak çözer; `PageCategory::url_path` değerinin bulunması kategori için ziyaret edilebilir arşiv bulunduğunu göstermez. Kategori başlığı şu aşamada grup/dinamik kaynak olabilir; tıklanabilir arşiv için ayrıca karar gerekir. Route adının varlığı, keyfi bir path'in içerik bulacağını da garanti etmez.
- Kategori doğrulaması kendisini/alt kategorisini ebeveyn seçmeyi engeller. Kategori silme servisi, altında sayfa veya alt kategori bulunan kategori silimini reddeder. Bu kurallar gelecekteki menü ağacı için yeniden kullanılabilecek davranış örnekleridir; menü kuralları ayrıca tanımlanmalıdır.
- `PageService::sanitizeContent`, içerik HTML'sindeki `id` niteliğini izinli tutmuyor. Serbest içerikte yazılmış `#bolum` anchor'ının korunacağı varsayılamaz. Gösterim şablonu da otomatik bölüm ID'si üretmiyor. Anchor kaynağı tema tarafından sağlanmalı veya ileride açık bir bölüm kimliği davranışı kararlaştırılmalı.

## Services bulguları

- `ServiceCategory::services()` ve `Service::category()` tek kategori ilişkisini sağlar. Kategori ağaç ilişkisi ve kategori sırası alanı yoktur; Services kategorilerini Pages gibi hiyerarşikmiş gibi göstermek doğru olmaz.
- Hizmetlerde `is_active` var; `slug` ve `sort_order` yok. Dinamik listede yalnız aktif kayıtlar, açık bir sıra kuralıyla (örneğin başlık ve ID) sunulabilir; bu kural henüz mevcut public menü davranışı değildir.
- İlgili route listesi admin liste/ekleme ve kategori CRUD ekranlarını gösterir. Ziyaretçiye açık hizmet detay/arşiv route'u bulunmaz. İlişkili kayıtlar seçilebilse de otomatik bağlantı üretimi hazır değildir. `admin.services.*` adresleri ziyaretçi menüsüne hedef olarak kullanılmamalıdır.
- Hizmet kategorisi silinince migration'daki ilişki hizmetin kategorisini boşaltır (`nullOnDelete`). Gelecekte menü kaynağı kategoriye bağlıysa kategori yokluğu ayrıca ele alınmalıdır.

## Proje uyumu için sonuç

Pages tekil sayfa hedefiyle başlanabilecek somut kaynaktır. Sayfa kategorileri seçim ağacı ve dinamik alt içerik kaynağı olarak uygundur; kategori linki için arşiv beklentisi ayrıca netleşmelidir. Services içerik seçimi için uygundur fakat public hedefi tamamlanmadan tıklanabilir kaynak sayılmamalıdır. Ürün desteği gereksinimde korunmalı, ürün modeli ve route'u bugün varmış gibi tasarlanmamalıdır.

Kaynak modülün kurulu ve etkin olması ayrı koşullardır. Kaynak devre dışı kaldığında kayıtlar silinmeden menü öğelerinin kullanılamaz durumu yönetilmelidir. Proje kuralları bağımsız içerik modellerini, modüler dağıtımı, Tabler admin akışını ve somut servislerle sade katmanları öngörür. Menü önerisi bu sınırları izlemelidir.

## Yerel kanıt dosyaları

- `Modules/Pages/app/Models/Page.php`, `PageCategory.php`
- `Modules/Pages/app/Http/Controllers/PageController.php`, `PageCategoryController.php`
- `Modules/Pages/app/Http/Requests/SavePageRequest.php`, `SavePageCategoryRequest.php`
- `Modules/Pages/app/Services/PageService.php`, `PageCategoryService.php`
- `Modules/Pages/routes/web.php`, `resources/views/pages/show.blade.php`
- `Modules/Pages/database/migrations/2026_10_16_192133_create_page_categories_table.php`, `2026_10_16_192133_create_pages_table.php`, `2026_10_16_194554_make_page_category_nullable_and_default_status_published_on_pages_table.php`
- `Modules/Services/app/Models/Service.php`, `ServiceCategory.php`
- `Modules/Services/routes/web.php`, `app/Http/Controllers/ServiceController.php`, `ServiceCategoryController.php`
- `Modules/Services/database/migrations/2026_10_15_194307_create_service_categories_table.php`, `2026_10_16_171034_create_services_table.php`
- `modules_statuses.json`; `Modules` klasörünün modül listesi
