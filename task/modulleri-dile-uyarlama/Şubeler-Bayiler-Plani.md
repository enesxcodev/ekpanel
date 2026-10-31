# Şubeler ve Bayiler: dil entegrasyonu planı

## İncelenen ve doğrulanan noktalar

- Ortak akış: `task/modulleri-dile-uyarlama/Uyarlanacak Modüller.md` (aktif diller, formda dil seçici, Blog yalnız örüntü, migration ve hedefli doğrulama kuralları).
- Modül: `Modules/Branches/database/migrations/2026_10_22_000000_create_branch_categories_and_branches_tables.php`; `Branch` ve `BranchCategory` modelleri; `BranchService`, `BranchesFrontendService`; her iki DataTable ve controller/request; `resources/views/partials/form.blade.php`, `resources/views/categories/partials/form.blade.php`, `resources/assets/js/panel.js`, `routes/web.php`.
- İçerik ayrımı: Şube adı, adres detayı, telefon, e-posta, web sitesi, çalışma saatleri, harita URL'si çeviriye uygun. Şube kategorisi adı da çevrilebilir; kategori slug'ı tema filtre API'sinde (`BranchesFrontendService`) kullanıldığı için kategorinin dil bağımsız anahtarında kalmalı. `branch_category_id`, Konum FK'ları, koordinatlar, durum ve sıralama dillere ortak kayıt alanlarıdır.
- Seeder `BranchCategorySeeder` yalnız `branch`/`dealer` için ilk oluşturmayı yapıyor; mevcut kayıtların değiştirilmesini önleyen davranışı koruyun. `.ai/rules/tema-entegrasyonu.md` ile `skills/branches-theme-integration/SKILL.md`, kategori slug'ının frontend filtre anahtarı olduğunu ve Konum alanlarının kopyalanmaması gerektiğini doğruluyor.
- `App\Models\Settings\Language` mevcut ve `languages` şemasına referans veren Blog migration'ı doğrulandı. Blog örüntüsü: `blog_content_translations` ve `blog_category_translations`, `lang_id`, kayıt başına benzersiz dil, Türkçe veri backfill'i; formda aktif dil ve seçilen çevirinin yüklenmesi.

## Önerilen veri modeli ve kritik karar

Branches için iki ayrı çeviri tablosu kurun: `branch_translations` (branch_id, lang_id, name, address_detail, phone, email, website, working_hours, map_url, timestamps) ve `branch_category_translations` (branch_category_id, lang_id, name, timestamps). Her tabloda FK + cascade delete, `lang_id` FK + restrict, `(owner_id, lang_id)` unique/index kullanın. Koordinat, Konum ilişkileri, kategori ilişkisi, aktiflik ve sıralama ana tablolarda kalır.

**Kritik karar:** kategori `slug` değerini çevirmeyin ve kategori kimliğini dile göre değiştirmeyin. Kategori adını çevirin; mevcut `branch`/`dealer` slug'ları tema API filtrelerinde ve modül rule/skill'inde sabit davranış taşır. Böylece Branch kategori seçimi her dilde aynı kategoriye bağlanır ve temanın `category` parametresi kırılmaz.

Migration yeni çeviri tablolarına Türkçe (`languages.code = tr`) için mevcut her şube/kategori değerini kopyalayarak backfill etsin; kaynak kayıt sayısı ile Türkçe çeviri sayısı eşitliğini doğrulasın. Ana tablolardan taşınan sütunları ancak backfill ve ilişki kontrolü başarılı olduktan sonra kaldırın. `down()` çeviri kaybı yaratacaksa Blog'daki gibi otomatik geri dönüş yerine açıkça güvenli olmayan rollback'i engellesin. Ana migration'ı değiştirmeyin; yeni ileri migration ekleyin. `languages` zaten yoksa varsayım yapmayın; projedeki ayarlar/Blog migration ve default Türkçe seed davranışıyla uyumlu, idempotent hazırlık kararı uygulama aşamasında korunmalı.

## Uygulama kapsamı (dosya odaklı)

### Uygulama aşamasında doğrulanan teknik düzeltme

- Yeni kurulumda eski alanları düşürmek mümkün görünse de mevcut modülün sürüm yükseltme/önceki kod sürümüne dönüş sözleşmesi, Blog migration'ındaki otomatik kolon kaldırmayı güvenli kılmıyor. Bu uygulama `branches` ve `branch_categories` legacy metin kolonlarını bırakır; Türkçe kaynak değerleri çeviri tablolarına taşır ve Türkçe düzenlemelerde geriye uyumluluk için legacy alanları eşzamanlı günceller. Böylece veri kaybı olmadan eski tüketiciler çalışır. Yeni uygulama listeleri, formları ve public servis çeviri tablolarını esas alır.
- `branch_category_translations` yalnız ad taşır; kategori slug/aktiflik/sıralama ortak kalır. Slug yalnız kategori ilk oluşturulurken tanımlanır; çeviri düzenlemesinde değiştirilemez.
- Migration `languages` tablosunu kendiliğinden oluşturmaz; bulunmazsa açık hata ile durur. Dil altyapısı ana uygulamada ayrı migration ile kurulduğu için örtük şema oluşturmak modül sınırını aşar. Türkçe dil kaydı idempotent eklenir ve iki kaynağın backfill sayısı kontrol edilir.

- Yeni translation modelleri ve `Branch` / `BranchCategory` `translations()` ilişkileri ekleyin; Türkçe uyumlu getter/fallback gereksinimini liste, public servis ve kategori formu için tanımlayın. Admin seçili dilde eksik çeviriyi sessizce başka dil verisi gibi kaydetmemeli.
- `BranchService` yalnız seçilen dilin çevirisini oluşturup güncellesin; ana kayıtta kategori, lokasyon, koordinat, durum ve sıralamayı yönetsin. Kategori CRUD'unda `BranchCategoryController` ve `SaveBranchCategoryRequest` için çeviri odaklı servis/işlem ekleyin. `lang_id` aktif `languages` kaydına doğrulansın; alan kuralları translation tablosundaki alan/uzunluklara göre ayrılsın.
- `BranchController` form seçeneklerinde sadece aktif dilleri, URL `lang` parametresine göre seçili dili ve kaydın o dildeki çevirisini sağlamalı; geçersiz/pasif dil kabul edilmemeli. Store/update dönüşü seçili dil koduyla edit sayfasında kalsın. Kategori listesi/formu da aynı dil akışını kullanmalı.
- Şube listesi ve kategori listesi DataTable'ları seçili/varsayılan dilin translation tablosuna join ederek çevrilmiş isimleri göstermeli; eksik çeviriler anlaşılır boş/çeviri eksik görünümü vermeli, join çoklama oluşturmamalı. Kategori seçenekleri dil translation adına göre gösterilmeli.
- `resources/views/partials/form.blade.php` için Blog'un iki kolonlu düzenini uyarlayın: geniş içerik alanı (adres/konum, iletişim) ve dar sağ panelde yalnız aktif dillerin seçicisi; 8/4 veya 9/3 için 8/4 önerilir. Dil değişimi `lang` ile sayfayı yükleyip doğru translation'ı göstermeli; ortak alanların her dilde tutarlı olduğu açıklanmalı. `categories/partials/form.blade.php` dil seçicisini ve dil kapsamı açıklamasını içermeli. Pasif dil alanları gönderilememeli; hiç aktif dil yoksa form güvenli biçimde devre dışı/uyarıda kalmalı.
- `BranchesFrontendService` aktif dili (mevcut locale/code altyapısına göre) çözmeli, aktif kayıt + aktif kategori filtresini korumalı, seçilen dil translation'larını eager-load edip seçili çeviriyi sunmalı; kategori slug filtresi aynı dil bağımsız slug ile çalışmalı. Dil tabloları/çeviri tablosu yoksa modülün mevcut boş sonuç korumasını sürdürün. Güncellenen public davranışı `.ai/rules/tema-entegrasyonu.md` ve `skills/branches-theme-integration/SKILL.md` içinde anlatın; `getAll/getFirst/find`, alan kapsamı ve translation erişim örneklerini güncel tutun.

## Hedefli regresyon ve Ego Browser senaryoları

- `tests/Feature/BranchControllerTest.php`: Türkçe verinin migration/backfill sonrası korunması; iki dilde şube alanlarının birbirinden bağımsız kaydı/düzenlemesi; kategori ve konum/koordinat/durum/sıralamanın ortak kalması; seçili dil redirect/form yükü; pasif/olmayan `lang_id` reddi; aktif/pasif kategori ve kategori seçimi.
- `tests/Feature/BranchCategoryControllerTest.php`: aynı kategoride dil bazlı ad güncellemesi; slug sabitliği ve benzersizlik; bağlı şube varken silme engeli; çeviri olmayan dilde doğru boş form.
- `tests/Feature/BranchesFrontendServiceTest.php` (yoksa ilgili mevcut test dosyasına dar kapsam): aktif locale çevirisi, pasif şube/kategori gizliliği, category slug filtresi, eksik translation ve translation tabloları bulunmayan kurulum davranışı. Dashboard/widget sayıları ana kayıt bazında kalmalı.
- Ego Browser: aktif dillerin gösterilmesi; şube formunda dil değiştirince farklı dilde kayıtlı ad/iletişim alanlarının yüklenmesi ve kaydetmede diğer dilin korunması; kategori adında aynı akış ve ortak slug; DataTable ve kategori dropdown'un seçili dilde adı göstermesi; aktif kategori/şube ve lokasyon seçim davranışının bozulmaması; pasif dilin arayüzden ve istek doğrulamasından geçmemesi. Yalnız Branches panelini doğrulayın.

## Doğrulama sınırı

Bu görev planlama ile sınırlı tutuldu. Kod, migration ve test çalıştırma/değişikliği yapılmadı; planlanan senaryolar henüz yürütülmedi.
