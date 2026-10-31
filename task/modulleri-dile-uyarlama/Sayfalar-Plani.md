# Sayfalar dil uyarlama planı

Tarih: 29.10.2026. Durum: inceleme tamamlandı, uygulama bekliyor. Bu belge uygulayıcı ajan içindir; uygulama kodu değiştirilmedi ve test çalıştırılmadı.

## Doğrulanan mevcut yapı

- Dil modeli `App\Models\Settings\Language` (`app/Models/Settings/Language.php`); yalnız `code` ve `is_active` alanları var. `LanguageSeeder` aktif dili `config(app.locale)` üzerinden oluşturuyor. `php artisan config:show app.locale` bu ortamda `en` döndürdü. Dolayısıyla Türkçe varsayılanlığı uygulama locale ayarına bırakılmamalı; Pages içerik dilinde birincil kod `tr` olmalı ve mevcut Türkçe kaydı aktif hale getirilmeli.
- Blog referansında gerçek tablolar `blog_contents`, `blog_content_translations`, `blog_categories`, `blog_category_translations`. Türkçe kodu BlogLanguageService içinde `tr`; görüntü/veri ilişkisi örnek alınabilir, Blog alanları ve global slug kuralı Pages'e kopyalanamaz.
- Pages `Page` ve `PageCategory` modellerinden oluşuyor. Sayfanın kategorisi isteğe bağlı. Kategori ağacı çok seviyeli; URL kategori slug zinciri ve sayfa slug'ından oluşuyor. Aynı slug farklı kategorilerde geçerli; kategori slug'ı aynı ebeveyn altında benzersiz. Sayfa yayın durumu `draft/published`.
- Sayfa metinleri: `title`, `slug`, `short_description`, `content`, `meta_title`, `meta_keywords`, `meta_description`, `extra_fields`. Kategori metinleri: `name`, `slug`, `short_description`, `extra_fields`. Ana görsel (`image`, `image_media_id`), sıralama, sayfa-kategori ve kategori-ebeveyn ilişkileri mevcut ortak kayıt özellikleridir.
- Admin işlemleri PageController/PageCategoryController, SavePageRequest/SavePageCategoryRequest, PageService/PageCategoryService ve iki DataTable üzerinden geçiyor. Formlar içerik/SEO sekmelerini ve ortak FeatureValues/FieldFiles/ContentMediaService akışını kullanıyor.
- Public route tek fallback `/{path}`; açık route önceliği mevcut testlerle korunuyor. Menü kaynakları `PageMenuSource` ve `PageCategoryMenuSource` da başlık/slug okuyor; PagesFrontendService yanı sıra bu kaynaklar da kapsamda.
- Sağ dil alanı için Blog modülündeki `partials/languages` ve form, birebir kopyalanmadan örnek alınacaktır. Pages geliştirme skill'i yok; tema rule/skill'i mevcut ve okundu.
- Çalışma ağacında Blog, Services ve ortak dil altyapısına ait çok sayıda mevcut değişiklik var. Pages dosyaları başlangıçta temiz. Mevcut değişiklikler korunacak; toplu geri alma/commit yapılmayacak.

## Modüle özel veri tasarımı

1. `page_translations` ve `page_category_translations` oluştur. Her biri kendi ana kayıt FK'sı, `lang_id` FK'sı, metin alanları ve timestamps içersin; `(page_id, lang_id)` / `(page_category_id, lang_id)` benzersiz olsun. Aktif dil doğrulaması servis/request sınırında uygulanır. Dil FK'sı silmeyi kısıtlar; ana kaydın silinmesi çevirilerini kaldırır.
2. Sayfa çeviri tablosuna yukarıdaki tüm sayfa metin alanlarını, kategori çeviri tablosuna tüm kategori metin alanlarını taşı. `meta_keywords` ve kategori `short_description` unutulmayacak. Ortak medya ve yayın/sıralama davranışı ana kayıtta kalır. `extra_fields` değerleri dile özel çeviri kaydına geçer; dosya/medya sahipliği de aynı kayda geçirilir.
3. Kategori ağacının kimlik/ebeveyn ilişkisi ve sayfanın kategori kimliği ortak kalır; seçim listesi ve public çözümleme seçili dilde çevirisi bulunan kategori ve tüm atalarını gerektirir. Böylece aynı içerik çevirileri kategori kimliğini paylaşırken görünen ad/slug zinciri dile göre değişir. Yönetim ekranında bu ortak alanların tüm dillere etki ettiği açıklanır.
4. Blog'un dil başına global slug benzersizliğini kullanma. Sayfa için dil+kategori kapsamı, kategori için dil+ebeveyn kapsamı korunur. Nullable FK'ların benzersiz indekslerde kök kayıtları korumadığı dikkate alınır. Uygulayıcı, normalize edilmiş sıfır/kategori kapsam anahtarı ve `(lang_id, scope, slug)` DB unique indeksi gibi veritabanında yarışa dayanıklı bir çözüm kullanmalı; taşınan sayfa/kategoride kapsamları aynı transaction içinde güncellemelidir. Form validasyonu aynı kapsamı kontrol eder.
5. Public eksik çeviri davranışı: başka dilde metin sızdırma yok; seçili dil/çeviri veya gerekli kategori atası yoksa 404, frontend servisinde boş/null. Admin mevcut ana kayıt için çeviri yoksa boş çeviri formunu açar; başka dilin verisini varsayılan gibi doldurmaz.

## Migration ve mevcut veri koruma

- Yeni ileri migration yaz; eski migration dosyalarını değiştirme. Canlı şemayı Boost database-schema aracı varsa onunla doğrula. PHP/Laravel/Nwidart kurulu sürümlerini doğrula; Laravel davranışı değişikliğinden önce search-docs kullan; araç yoksa resmi sürüme uygun belgeleri kullan.
- Tablolar oluşturulmadan önce aynı ebeveyn/dil kapsamındaki eski slug çakışmalarını, bozuk kategori/ebeveyn zincirlerini ve çeviri için gerekli languages tablosunu kontrol et. Çakışmada açıklayıcı hata ver; sessiz yeniden adlandırma veya kayıt silme yapma.
- `tr` dilini yoksa ekle, varsa `is_active=true` yap. Mevcut Türkçe kaydın ID'sini yeniden kullan. Tüm eski Page/PageCategory metinlerini Türkçe çevirilerine chunk ile aktar; ana ID, ortak FK, timestamps, yayın, görsel ve sıralama korunur.
- `extra_fields` dosya yollarını ilk geçişte taşımak zorunlu değil; mevcut yolları koru. `media_usages` içindeki extra_fields sahiplerini çeviri modeline/kimliğine ve dile yönlendiren çeviri düzenleme route'una geçir; ana görsel sahipliği Page üzerinde kalır.
- Kaynak/çeviri adetleri ve alanlar doğrulandıktan sonra eski metin alanlarını kaldır. Mevcut unique indekslerini kaldırma sırasına ve SQLite test/MySQL hedef uyumuna dikkat et. Geçiş yarıda kalabilir: DDL atomikliği varsayma, önkontrolleri başa al ve gerçek veriye uygulamadan önce yedek al.
- Otomatik down ile diğer dil çevirilerini düşürme; Blog örneğindeki gibi açık ileri düzeltme/yedek dönüşü açıklaması ver. Kullanıcı verisini silen migrate:fresh/reset komutları kullanılmayacak.

## Dosya bazlı uygulama adımları

1. Yeni `PageTranslation`, `PageCategoryTranslation` modelleri ve `PagesLanguageService`: aktif diller, Türkçe önceliği, admin `?lang=`, frontend/public dil çözümü ve mevcut route adlarıyla URL üretimi. İstek bağlamı Pages'e özgü olmalı, Blog'a runtime bağımlılık eklenmemeli.
2. `Page.php`, `PageCategory.php`: translations/language ilişkileri ve seçili dil metin erişimi, image ilişkisi korunur. `url_path` bütün ataları aynı dilde okur; sınırsız ağaç derinliği/cycle koruması sağlanır. SQL'de artık bulunmayan `title/name/slug` alanlarına orderBy/select kalmaz.
3. `PageService.php`, `PageCategoryService.php`: ortak kayıt ve seçili dil çevirisini tek FieldFiles transaction'ında kaydet; aktif `lang_id` doğrula. Seçili çevirinin dinamik alanları ve dosyalarını güncelle, diğer dilleri koru. Tüm çeviri dosyaları/medya kullanımları silmede temizlensin; kategori doluluk ve cycle koruması korunsun.
4. Request'ler: aktif `lang_id`, kapsam/dil bazlı slug ve kategori çevirisi/ata uygunluğu, doğru çevirinin extra_fields doğrulaması. Diğer dilin slug'ı engel olmaz; aynı dil/kapsam çakışması reddedilir. Kategori hiyerarşisinde kendine/altına bağlanma engeli korunur.
5. Controller'lar: index/create/edit/data seçili dili çözsün; forms `translation`, `languages`, `selectedLanguage`, `translatedLanguageIds` alsın. Kategori ağacı/seçenekleri seçili dilde hazırlanır; kaydetme sonrası çevirisi yeni eklenen aynı kayıt ve dilin düzenleme ekranı açılır. Bildirim linki dili korur; yetki ve NotifyService davranışı korunur. Çeviri medya kullanım linki için ilgili translation edit redirect route'ları eklenir.
6. DataTable ve index/actions partial'ları: seçili dile join/filter, başlık/slug/kategori/ebeveyn adında arama ve sıralama. Aktif dil filtresi ortak tablo mekanizmasıyla taşınır; JSON'da ilişkiler veya diğer dil metinleri yanlışlıkla dönmez. Düzenle/görüntüle linkleri dili korur.
7. Her iki form: sol içerik/SEO alanı, sağ aktif diller listesi için Tabler `col-lg-8/4` veya `9/3`. Seçili dil belirgin, çeviri var/yok etiketi; gizli `lang_id`. Ana görüntü ve ortak alanlar ayrı anlaşılır bölümde. Alan değerleri translation'dan, validation old değerleri seçili dilden gelir. Dil tıklaması seçili dil formunu yükler. Kaydedilmemiş değişiklik varsa mevcut projedeki koruma kalıbını incele ve uyarıyla geçişi yönet. Özel JS gerekiyorsa modül asset sözleşmesi ve build metadata'sıyla ekle; ortak panele modüle özel import ekleme.
8. Public `PageController::show` ve `routes/web.php`: Türkçe eski `/{path}` adresleri korunur; diğer aktif diller `/{lang}/{path}` olarak aynı fallback çözümlemesinde ilk segmentten ayrıştırılır. Açık route önceliğini bozan geniş yeni normal route ekleme. `tr` öneki canonical adrese yönlendirilebilir; aktif olmayan dil/eksik içerik 404. Seçili dilde kategori ata slug'ları ve son sayfa slug'ı çözülür. İki farklı dilde aynı slug desteklenir. Dil koduyla çakışan eski kök yollar için migration önkontrolü yap ve belirsiz yolu sessizce yönlendirme.
9. `PagesFrontendService`: mevcut `getAll(?int $categoryId)`, `getFirst`, `find` sözleşmesini koruyarak isteğe bağlı dil kodunu sona ekle; başlığa göre sıralamayı çeviri SQL'ine geçir, kategori/ata çevirisi ve yayın/etkinlik/tablo varlığı filtrelerini koru. Çağrı yapan temalar varsayılan Türkçe çalışmaya devam etsin.
10. `PageMenuSource`/`PageCategoryMenuSource`: kaynak kimlikleri ana kayıt ID'si kalır. Etiketler ve URL'ler mevcut dil bağlamında çözülür; çevrilmemiş hedef yanlış dil URL'si üretmez. Menus interface'ini bu aşamada değiştirmeden mevcut dil bağlamını kullan; Menus kendi sırasına geldiğinde entegrasyon yeniden kontrol edilir. Dinamik çocuklar doğrudan yayınlanmış sayfalardır.
11. SEO composer/`App\Services\SeoMetadata` gerçek dosyasını keşfet: Page metin erişimlerinin seçili çeviriyi kullandığını, canonical'ın dil URL'si ve HTML lang'ın içerik dili olduğunu doğrula; yeni paralel SEO üretimi ekleme. `pages/show.blade.php` başlık/breadcrumb/sanitizasyon aynı dilde, mevcut ortak görüntü alanı korunur.
12. Factory'ler ve boş PagesDatabaseSeeder: testlerin `Page::factory()->create(['title'=>...])` ve kategori karşılığını destekleyen mevcut proje örüntüsüyle varsayılan Türkçe çeviri üret. Test yardımcılarına/çağrılara gerekirse dil parametresi ekle. Örnek müşteri içeriği ekleme.
13. `Modules/Pages/.ai/rules/tema-entegrasyonu.md` ve `skills/pages-theme-integration/SKILL.md`: çeviri alanları, ortak alanlar, dil parametreleri, eksik çeviri, lokal URL/kategori zinciri, frontend servis imzaları ve sanitizasyon sözleşmesini gerçek uygulamaya göre güncelle.

## Test ve tarayıcı kabul ölçütleri

- Yeni PagesTranslations feature testleri: Türkçe ve ikinci aktif dil kayıt/düzenleme; çeviri değiştirme diğer dili bozmaz; pasif/olmayan lang reddedilir; çevirisi olmayan form boş açılır; kategori ve bütün ataları seçili dilde bağlanır; cycle koruması ve dil/kapsam slug çakışmaları.
- Migration regression: eski kategorisiz/kategorili sayfalar ve derin kategori ağacı, meta_keywords/açıklamalar/extra_fields/görsel/medya kullanımları Türkçeye eksiksiz aktarılır; çakışma açık hata verir; başka dil veri kaybı yaratmaz. İzole test veritabanı kullanılır.
- Mevcut `Modules/Pages/tests/Feature/{PagesRoutingTest,PagesFrontendServiceTest,DashboardWidgetsTest}.php`; ayrıca kök `tests/Feature/PagesRoutingTest.php`, `ModuleEnhancementsTest.php` ve Menus `MenuWorkspaceTest.php` ilgili etkilenen senaryoları çalıştır. Eski yinelenen testler silinmez.
- Public: eski Türkçe yollar, diğer dilde derin kategori yolları, kategori taşınması, eksik ata/çeviri, taslak, pasif dil, unknown path, açık route önceliği; SEO ve içerik sanitizasyonu.
- Frontend service: dil bazlı sonuç/sıra, descendants, eksik tabloda boş/null, modül devre dışıyken boş/null, diğer dilin veri sızıntısı olmaması. Menü URL/etiket/dinamik çocukları seçili dilde kontrol edilir.
- Yetki testleri mevcut create/update/delete ve etkin modül sınırlarını korur; normal admin bildirim linki dil taşır ve süper admin bildirim üretmez.
- Test ajanı proje testing-best-practices skill'inin ilgili kurallarını ve Ego Browser skill'ini okur. Ego Browser/Playwright üzerinden admin sayfa ve kategori create/edit dil tıklaması, TR/ikinci dil kayıt ve tekrar açma, SEO sekmesi, kaydedilmemiş veri uyarısı, public doğru metin/URL ve konsol hatalarını doğrular. Kullanıcıdan gerçek giriş bilgisi gerekiyorsa bunu tek somut engel olarak bildirir; doğrulanmamış kontrolü başarılı saymaz.
- PHP değiştiyse proje Pint komutu; modüle özel JS varsa npm build ve yayınlanmış asset kontrolü. Başarılı değişiklikler hedefteki farklı/yeni işleri koruyarak `D:/localhost/ekAgentModules/Pages` staging'ine aktarılır. Release/ZIP/katalog/push yapılmaz.
- Başarı sonrası Raporlar.md'ye en çok 500 karakterlik özet yazılır ve Sayfalar satırına ✓ konur. Bu planlama adımında tik atılmaz.

## Uygulama öncesi dikkat / engeller

- Kesin blokaj bulunmadı. `app.locale=en` ve LanguageSeeder'ın config bağımlılığı Türkçe aktif/default kararıyla çelişiyor; PagesLanguageService ve güvenli Türkçe upsert bunu çözmeli, global uygulama locale'si gereksiz değiştirilmemeli.
- Blog modülü dil seçici ve çeviri örüntüsü için fikir kaynağıdır; Pages kendi gereksinimlerine göre uyarlanmalı, Blog birebir kopyalanmamalıdır. Blog/Services'teki sağ dil alanı incelenebilir.
- Blog ve Services mevcut değişiklikleri devam eden kaynaklar; bunları uygulama sırasında ezme. Staging hedef farklarını ayrıca incele.
- Boost araçlarının bu oturumda erişilebilirliği ve gerçek DB şeması henüz doğrulanmadı; uygulayıcı bunları keşfetmeli. Planlama için yalnız dosya ve Artisan config/route okuması yapıldı.



