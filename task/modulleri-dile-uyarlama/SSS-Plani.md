# SSS (Faqs) dil uyarlama planı

Durum: uygulama ve doğrulama tamamlandı (30.10.2026).

## Mevcut durum

- `Modules/Faqs` şu anda tek dilli. `faqs` tablosunda `category_id`, `name`, `content`, `extra_fields`; `faq_categories` tablosunda `name`, `extra_fields` bulunuyor. `Faq::category()` kategoriye nullable `BelongsTo`, `FaqCategory::faqs()` sorulara `HasMany` ilişkisidir. Kategori silinince `category_id` `null` olur. Soruda hizmet/başka modül bağı yoktur.
- `FaqController` ve `FaqCategoryController`, aynı kayıt üstünde mevcut alanları kaydeder. İstekler `name` (kategori adı global unique), `content`, nullable `category_id` ve özellik alanlarını doğrular. `FaqService` yanıt HTML'ini sanitize eder; iki servis de `extra_fields` dosya/medya ve kullanım kayıtlarını yönetir.
- Soru ve kategori formlarının tek kopyası `resources/views/partials/form.blade.php` ile `categories/partials/form.blade.php`; liste tabloları da şu an doğrudan eski `name` alanlarını gösteriyor. İki DataTable, CRUD işlem/izin akışları ve `features.json` içindeki mevcut `faq` / `category` form anahtarları korunmalı.
- `FaqsFrontendService` şu anda `getAll(?int $categoryId)`, `getFirst(?int $categoryId)`, `find(int $id)` sunuyor; kategori ilişkisini yükleyip modül/tablolar yoksa boş sonuç veriyor. Ziyaretçi SSS rotası veya renderer modülde yok.
- Tek mevcut ziyaretçi tüketicisi `app/Services/Frontend/HomePageService::faqs()`; `resources/views/frontend/themes/default/sections/faqs.blade.php` yalnız soru ve yanıtı gösteriyor, dört kayıtla sınırlandırıyor. Tema kategori göstermiyor. Bu tüketici genel `records('Faqs')` çağrısından geldiği için henüz dil bağlamı geçmiyor.

## Veri modeli ve migration

Blog'daki çeviri tabloları örüntüsünü izleyerek, modül içinde aşağıdaki çevirileri ekle:

- `Modules/Faqs/app/Models/FaqTranslation.php` ve `faq_translations`: `id`, `faq_id`, `lang_id`, `name`, `content`, `extra_fields`, timestamps; `faq_id` → `faqs` ve `lang_id` → `languages` foreign key'leri, `(faq_id, lang_id)` unique. Soru/yanıt ve ek özellik alanları dile bağlıdır.
- `Modules/Faqs/app/Models/FaqCategoryTranslation.php` ve `faq_category_translations`: `id`, `faq_category_id`, `lang_id`, `name`, `extra_fields`, timestamps; aynı cascade/restrict ve `(faq_category_id, lang_id)` unique düzeni. Kategori adı ve özellik alanları dile bağlıdır.
- `Faq.category_id` ana tabloda kalır: seçilen kategori kimliği diller arasında ortaktır; `FaqCategory.faqs` ilişkisi aynı kimlik üzerinden çalışmaya devam eder. İki temel modelde `translations()` ve çeviri modelinde `language()` / üst modele `BelongsTo` eklenir. SSS'de slug alanı yoktur ve eklenmemelidir.
- Sonraki migration, `languages` içindeki `tr` kaydını idempotent biçimde hazırlar; mevcut her FAQ ve kategorinin `name`, `content` ve `extra_fields` değerlerini timestamp'leriyle Türkçe çeviriye kopyalar. Kategori kimlikleri ve nullable `category_id` aynen korunur. Kayıt sayısı ve her kaynak kaydın Türkçe karşılığı doğrulanmadan eski çevrilebilir sütunlar kaldırılmaz.
- Ardından ana tablolardan yalnız çevrilebilir `name`/`content`/`extra_fields` sütunlarını kaldır; FAQ `category_id` kalır. Çeviriler otomatik geri alınamayacağından `down()` veri kaybını önleyecek biçimde açıkça geri çevrilemez olmalı (Blog migration örüntüsü). 
- `extra_fields` için mevcut media/file bağlantılarını da taşı: `media_usages` içinde FAQ ve kategori temel model sahipliğini ilgili çeviri modeline/çeviri PK'sine ve çeviri düzenleme named route'una güncelle. Çeviri silme ve dosya temizleme yolları yalnız hedef dil değerini etkilesin. Blog migration'ındaki `media_usages` sahiplik aktarımını gözden geçir.

## Admin dili ve kayıt akışı

- `Modules/Faqs/Services/FaqLanguageService.php` benzeri bir modül servisi ekle; `Language` kayıtlarını yalnız aktif dillerden al, `tr`'yi öncele, seçili dili `?lang=` ile doğrula ve request attribute'unda tut. Blog'daki `BlogLanguageService` / `partials/languages.blade.php` UI'sini örnek al; Blog modülüne çalışma zamanı bağımlılığı ekleme.
- Soru ve kategori create/edit sayfaları için controller'da aktif diller, seçili dil çevirisi, çevrilmiş dil kimlikleri ve form seçeneklerini hazırla. Formlara gizli `lang_id`, ortak diller seçicisi ve seçilen dile ait translation alan değerleri ekle. İstenen dil aktif değilse 404/validation ile reddet; hiç aktif dil yoksa form kaydetmeyi devre dışı bırakıp Ayarlar > Diller'e yönlendirsin. Bir dilin alanları başka dildeki mevcut çeviriden otomatik kopyalanmamalı.
- `SaveFaqRequest` ve `SaveFaqCategoryRequest` seçili aktif `lang_id`'yi doğrulamalı. Soru `name`/`content`, kategori `name` ve özellik alanlarının kuralları o dilin çevirisine uygulanmalı. Kategori adı tek dilli ana tablo yerine çeviri tablosunda dile göre unique olmalı.
- Servisler ana FAQ kaydında yalnız kategori kimliğini; çeviride metinleri ve ilgili dilin `extra_fields` değerlerini kaydetsin. `firstOrNew` ile aynı dilde idempotent güncelleme, diğer dillerin korunması sağlanmalı. Dosya yolunu Blog'daki gibi `faqs/{id}/translations/{lang_id}/features` ve `faqs/categories/{id}/translations/{lang_id}/features` olarak ayır. Translation owner için medya geri dönüş rotaları ekle. Mevcut HTML sanitizasyonu, transaction/row lock, NotifyService ve izin kontrolleri korunmalı.
- Liste ekranları da bir dilde anlamlı olmalı: `?lang=` ile SSS/kategori DataTable'larında seçili dil çevirisini göster, eksik çeviri için açık ve güvenli bir işaret (`Çeviri eksik (#id)`) göster; satırın edit linki o dili korusun. SSS kategori filtresi/seçenek etiketleri seçili dilin çevirisini kullansın. Admin kategori sayacı ve FAQ-kategori ilişkisi ortak kimlikler üzerinden kalır.

## Frontend servis ve tüketici

- `FaqsFrontendService` metotlarına opsiyonel dil kodu ekle: `getAll(?int $categoryId = null, ?string $languageCode = null)`, `getFirst(?int $categoryId = null, ?string $languageCode = null)`, `find(int $id, ?string $languageCode = null)`. İmzaların çağrı uyumlu kalması için yeni parametreler sona eklenmeli.
- Servis aktif dili `FaqLanguageService` üzerinden çözmeli; dil kodu verilmezse mevcut dil bağlamı (`faqs_language`, `pages_language`, route `lang`) ve son çare `tr` kullanılmalı. Dil aktif değilse veya kaydın o dilde çevirisi yoksa içerik döndürülmemeli; başka dile sessiz fallback yapılmamalı. Aktif modül/tablo korumaları sürmeli ve gerekli yeni translation/language tabloları yoksa koleksiyon boş, tek kayıt `null` dönmeli.
- Sorguda seçili dilin FAQ çevirisi bulunmasını zorunlu tutup yalnız o dilin translation'ını ve mevcut `category` ilişkisini yükle. Sonuç üzerinde metin alanları seçili çeviriden okunur; `category_id` ana kayıtta kalır. Kategori ilişkisine seçili dilin `FaqCategoryTranslation` kaydını yükle; eksik kategori çevirisi eski Türkçe kategoriyi sızdırmamalı. Nullable kategorili sorular ve verilen kategori ID filtresi mevcut davranışını korur; kategori filtresi/çevirisi yok diye soru listesi kendiliğinden daraltılmaz. Servis sıralama veya yayın durumu icat etmez.
- `HomePageService::faqs()` `FaqsFrontendService` çağrısını doğrudan kullanıp `pages_language` / route `lang` bilgisini iletmeli (Testimonial tüketicisindeki bağlamı izleyerek); homepage bölümü mevcut dört soru sınırını, sıralama davranışını ve soru/yanıt map'ini korur. Tema Blade'inde sorgu veya dil mantığı eklenmez. Modülde public SSS route'u/renderer eklenmez.

## Rule ve skill güncellemesi

- `Modules/Faqs/.ai/rules/tema-entegrasyonu.md` ve `Modules/Faqs/skills/faqs-theme-integration/SKILL.md` içindeki `Faq.name`, `Faq.content`, `FaqCategory.name` alanlarının ana kayıtta olduğu anlatımı çeviriye göre güncelle. Kategori kimliğinin ortak kaldığını, `name`/`content`/`extra_fields` alanlarının çeviride bulunduğunu, slug/yayın/sıralama alanı veya modüller arası ilişki olmadığını açıkça yaz.
- Skill'de frontend servis imzalarına dil parametresini ve seçili dil/fallback/eksik çeviri davranışını ekle. Tema okumasında hâlâ yalnız `FaqsFrontendService` kullanılacağını ve servisin visitor route/renderer yaratmadığını belirt. Kategori nullable davranışı ve mevcut kategori seçimi belirsizse kullanıcıya sorma sınırları aynen kalır.

## En küçük regresyon kapsamı

- Yeni `FaqsTranslationsTest` feature testi: migration/backfill sonrası ID, `category_id`, Türkçe soru/yanıt/kategori/`extra_fields` ve media usage sahibinin korunması; İngilizce çeviri oluşturma/düzenlemenin Türkçe değerleri değiştirmemesi; devre dışı dilin reddi; aynı kategori adının farklı dillerde kullanılabilmesi ve aynı dilde çakışmanın reddi.
- `FaqsFrontendServiceTest` kapsamını genişlet: istenen dilde çeviri bulunan/kayıt bulunmayan durum, yanlış/inaktif dilde fallback yapılmaması, aynı FAQ'nın iki dilde localized alanları, kategori filtresi ve nullable kategori, eksik kategori çevirisinde Türkçe metin sızmaması, kapalı modül/eksik tablo güvenliği. Doğrudan servis verilmiş dil ile mevcut request bağlamı ayrı doğrulansın.
- `FaqsManagementTest` içinde aktif dil seçicisinin formda görünmesi, seçili dil çevirisine kaydetme/düzenleme ve eski dil değerlerinin korunması; kategori adı doğrulaması ile bildirim/edit dönüşünün dil parametresini koruması kapsansın. Mevcut CRUD izin, HTML sanitize, özellik alanı, bildirim ve kategori-null davranış testleri tutulur.
- Browser smoke: süper admin olarak SSS ve kategori oluştur; Türkçe/İngilizce seçiciler arasında geçip alanların birbirinden bağımsız kaldığını ve eksik çevirinin tahminle doldurulmadığını gör; dil bazında liste/satır düzenlemesini aç; ziyaretçi ana sayfasında Türkçe ve test istemiyle sağlanan İngilizce dil bağlamında yalnız çevirisi olan soru/yanıtların geldiğini, section'ın mevcut sınırını/markup'ını koruduğunu doğrula. Bu plan aşamasında test, migration veya tarayıcı çalıştırılmaz.

## İncelenen dayanaklar

- Kurallar: `.ai/rules/index.md`, `.ai/rules/moduller.md`, `.ai/rules/mimari-kod-yapisi.md`, `.ai/Mimari-Kararlar.md`, `.ai/workflows/modul-gelistirme.md`, `.ai/rules/admin-arayuz.md`, `.ai/rules/frontend.md`, `.ai/rules/frontend-blade.md`, `Modules/Faqs/.ai/rules/tema-entegrasyonu.md`, `Modules/Faqs/skills/faqs-theme-integration/SKILL.md`.
- Yakın örüntü: `Modules/Blog` çeviri modelleri/migration'ı, `BlogLanguageService`, create/edit form dil seçicisi, save servisleri, frontend servisi ve `BlogTranslationsTest`; dil bağlamı için ayrıca `Modules/Testimonials` ve `Modules/Pages` kullanımları.
- SSS kaynakları: `Modules/Faqs/app/{Models,Http/Controllers,Http/Requests,Services,DataTables}`, `database/migrations`, `resources/views`, `routes`, `features.json`, `tests/Feature`; kamu tüketicisi `app/Services/Frontend/HomePageService.php` ve `resources/views/frontend/themes/default/sections/faqs.blade.php`.
