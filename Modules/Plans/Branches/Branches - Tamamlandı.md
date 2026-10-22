# Branches — Şubeler / Bayiler birleşik modül planı

- Durum: Yerel uygulama, dar test/build ve kaynak staging tamamlandı; GitHub release/push yapılmadı, 22 Ekim 2026.
- Görev: `TASK-20261022-0832-branches-plan`.
- Teknik aday adı: `Branches`; panel adı önerisi: **Şubeler / Bayiler**.
- Yetki sınırı: Kullanıcı 22 Ekim 2026'da bu planı uygulamamı istedi. Yerel kod, ilgili test/build ve kaynak staging yapıldı. Bu istek Git push, release paketi, GitHub Release, katalog değişikliği veya yayın yetkisi vermedi.

## Kesinleşen karar ve gerekçe

**Şube ve Bayi iki ayrı modül olmayacak.** Tek modül, tek kayıt modeli, ortak liste ve ortak ekle/düzenle formu kullanılacak. Ayrım kategoriyle yapılacak; kurulum seeder'ı iki başlangıç kategorisini “Şube” ve “Bayi” olarak idempotent biçimde oluşturacak. İlk sürümde kategori yönetimi ayrı CRUD ekranı değildir; seed edilen iki kategori kullanılır. Panel adı “Şubeler / Bayiler”, teknik ad `Branches` olarak uygulandı.

Kullanıcı, bu modülün Türkiye Konum modülüne bağlı çalışmasını istedi. Branches kullanımı için Konum kurulu, uyumlu ve etkin olmalı; adres verisi importu kullanılabilir durumda bulunmalıdır. Konum coğrafi referansın sahibidir; işletmenin şube/bayi kaydının sahibi Branches olur. Konum'da şube CRUD'u veya yeni il/ilçe/mahalle tanımlama akışı kurulmaz.

CMS'nin küçük işletme siteleri hedefi içinde çok konumlu işletmelerin tekrar eden adres, iletişim ve çalışma saati kayıtları makul ihtiyaçtır. Settings tekil genel iletişim ayarlarını taşır; kayıt bazlı CRUD, durum ve sıralama için uygun değildir. Brand çalışılan markaları, Sector hizmet verilen sektörleri tanıtır. Branches bu kayıtları kopyalamaz; bunlara ilişki, senkronizasyon veya zorunlu bağımlılık eklemez.

21 Ekim keşfindeki orta güvenli öneri ve `Keşif Durumu.json` içindeki `recommended_not_started` kaydı geçmiş keşif durumudur. Bu planın ilerleme dayanağı yeni kullanıcı kararıdır; geçmiş baseline'ın diğer modüller için vardığı sonuçları değiştirmez. README/keşif durumunun plan hazır olarak güncellenmesi ana görev kaydında yapılmalıdır.

## İlk sürüm kapsamı önerisi

| Alan | Davranış / sahiplik |
| --- | --- |
| Ad | Zorunlu şube veya bayi adı; aynı adlı farklı adreslere izin önerilir. |
| Kategori | Her kayıt tek kategoriye bağlanır; install seeder'ı Şube ve Bayi başlangıç kategorilerini idempotent oluşturur. Yönetilebilir ek kategori kapsamı ayrıca netleştirilir. |
| Konum bağlantısı | Kaynak ID'leriyle il, ilçe, kaynak ara bölgesi, mahalle, cadde/sokak; tüm seviyeler isteğe bağlıdır. Seçilen her seviye yine kendi üst zinciriyle tutarlı olmalıdır. |
| Adres detayı | Bina, kapı, kat ve tarif gibi Branches'a ait serbest metin; kaynak yer adlarını tekrar yazdırmaz. |
| Telefon | İsteğe bağlı tek iletişim telefonu; uygun bağlantı gösterimi. |
| E-posta, web sitesi | İsteğe bağlı, biçimi doğrulanmış iletişim alanları. |
| Çalışma saatleri | İsteğe bağlı kısa metin. Tabler içinde kullanılabilir saat picker bileşeni varsa kullanılır; yoksa yeni picker bağımlılığı eklemeden kısa metin alanı kullanılır. Haftalık yapılandırılmış takvim kapsam dışıdır. |
| Harita bağlantısı / koordinat | İsteğe bağlı HTTPS URL ve/veya elle girilen enlem-boylam çifti; geçerli aralıklar doğrulanır. Harita sağlayıcı API'si yok. |
| Aktiflik ve sıra | Pasif kayıt ziyaretçiye gösterilmez; sıralama sıra, ardından kararlı ID ile yapılır. |
| Ek alanlar | Ortak `features.json` / `extra_fields` sözleşmesi; ilk sürümde ek tanım gerekmiyorsa boş forms nesnesi. |

Kapsam kararı: Yönetim tablosunun top bar'ında kategori ve durum filtresi bulunur. Tema kullanımı modülün public okuma servisiyle yapılır; Branches'a özel public liste route'u veya detay sayfası eklenmedi. Servis kategori slug'ıyla filtrelemeyi destekler. Ayrı slug/SEO alanları, fotoğraf/galeri, mesafeye göre en yakın konum, çalışma saatinden anlık açık/kapalı hesabı, bölge ataması, bayi başvurusu, CRM ve stok işlemleri bu ilk sürüme dahil değildir.

## Konum sözleşmesi ve veri tutarlılığı

Kaynak hiyerarşisi `cities → towns → districts → neighbourhoods → streets` biçimindedir: **il → ilçe → kaynak ara bölgesi → mahalle → cadde/sokak**. `districts` mahalle anlamına gelmez. Form etiketleri bu ayrımı açıkça taşımalıdır. Kaynak kimlikleri yeniden numaralandırılmaz; çocuk JSON özetleri ilişki sorgusu yerine kullanılmaz. Posta kodu gerekiyorsa kaynak seviyesinden beş karakterli metin olarak okunur.

İncelenen mevcut sözleşme `Modules\Konum\Services\KonumFrontendService` üzerindedir:

- `ready(): bool`: etkin modül, gerekli tablolar ve yapılandırılan kaynak commit'i için `complete` veya `warnings` importu denetlenir.
- `search(string $entity, array $parents = [], string $search = '', int $limit = 25)`: aktif, silinmemiş, tutarlı kayıtları ad/ID sırasıyla döndürür. Önek araması, varsayılan 25, üst sınır 100; geçerli seviyeler cities/towns/districts/neighbourhoods/streets. Üst filtreler city_id/town_id/district_id/neighbourhood_id'dir.
- Seçenekler bu servisle üst seçime göre sınırlanır. Tüm sokaklar yüklenmez. Güncel seçili ID'nin ilk 25 arama sonucunda bulunmaması geçersiz olduğu anlamına gelmez; düzenleme ve sunucu doğrulaması ID ve tüm üst zinciriyle ayrıca doğrulanmalıdır. Sınırsız `query()` tema listesi olarak kullanılmaz.

Önerilen veri kararı: Coğrafi ID'ler ve canlı kaynak adları asıl gerçektir; il/ilçe tabloları veya adları Branches'a kopyalanmaz. Adres detayı ve iletişim Branches'ın kendi verisidir. Tarihsel teslimat/sipariş kanıtı olmadığı için ilk sürümde adres snapshot'ı tutulması önerilmez. Kaynak adının değişmesi canlı gösterime yansır; kaynak ilişki değişimi otomatik olarak yeni adrese dönüştürülmez.

Seçilen kaynak ID'lerinin her biri üst zinciriyle tutarlı olmalıdır. Kullanıcı geçersiz kaynak kayıtları nedeniyle public listeden dışlama/uyarı iş akışı istemedi; bu özel akış için ek geliştirme yapılmayacak. Kaynak seçimleri ve geçerli üst ilişki doğrulaması yine normal form ve sunucu doğrulamasının parçasıdır.

Üst seçim değiştiğinde bütün alt seçimler temizlenir; geç gelen AJAX cevabı eski üst seçime ait seçenekleri geri getiremez. İstemcideki seçeneklere güvenilmez: kayıtta her ID'nin aktifliği, tutarlılığı ve aynı üst zincire ait olduğu sunucuda tekrar doğrulanır. İl/ilçe dışındaki boş seviyeler için hayali kaynak kayıt üretilmez. Cadde seçilmişse gereken bütün üst ID'ler bulunmalıdır.

Konum'un kaynak dökümü 11 Mayıs 2024 tarihlidir; güncel resmî kapsama garantisi verilmez. Kaynakta olmayan yer için Branches formundan coğrafi kayıt CRUD'u açılmaz. Manuel adres detayı desteklenir; zorunlu konum seviyelerinin bulunamaması kayıt yapmayı engelliyorsa kullanıcıya açık hata verilir. Polygon veya spatial sorgu gerekmez; kaynak polygon'u koordinat alanı yerine kullanma.

## Admin akışı

1. Yetkili yönetici ortak sidebar'dan Şubeler / Bayiler listesine gelir. Liste ad, kategori, il/ilçe, telefon, aktiflik ve sıra sütunlarını; arama, table top bar'da kategori filtresi ve durum filtresini sunar. Yajra backend, Tabler ve `<x-admin.data-table>` ile mevcut ortak başlatıcı kullanılır.
2. Listedeki ekle düğmesi ayrı ekleme sayfasını açar. Form önerisi üç alan grubu/sekme: **Genel** (ad, kategori, aktiflik/sıra), **Adres** (isteğe bağlı hiyerarşik seçim ve detay), **İletişim** (telefon, e-posta/site, saatler, harita).
3. Konum alanları isteğe bağlıdır. Seviye seçildiğinde alt seçenekler üst seçime göre yüklenir; bağımlılık/import hazır değilse adres seçimi kullanılamaz ve yöneticiye nedeni gösterilir. Adres seçilmeden de kayıt oluşturulabilir.
4. Kaydetme başarılı olunca listeye dönülür. Hatalarda eski değerler korunur, hata bulunan sekme açılır. Düzenleme ayrı sayfadır; silme ortak onay akışını kullanır. Satır işlemleri erişilebilir etiketli ikonlarla gösterilir.
5. Başarılı CRUD işlemlerinden sonra ortak `NotifyService` ve CRUD bildirimi kullanılır. Aktör/süper admin davranışı ortak servise bırakılır; hassas adres/iletişim ayrıntıları bildirim içine dökülmez.

Konum seçenek araması Branches admin akışına ait yetkili endpoint üzerinden sunulur. Konum modülü mevcutta herkese açık HTTP adres API'si sağlamaz; böyle bir API varmış gibi davranılmaz. Endpoint yalnız izin verilen entity/üst alanlarını kabul eder; mevcut uygulama hız sınırları ve admin yetkisi uygulanır. Branches yöneticisine adres seçebilmesi için Konum yönetim ekranı izni otomatik verilmez.

## Ziyaretçi akışı ve okuma servisi

`Modules/Branches/app/Services/BranchesFrontendService.php` tema tüketimine sıralı, aktif kayıtların sınırlı listesini sağlar. `getAll()` varsayılan 50, üst sınır 100 kayıt döndürür ve isteğe bağlı kategori slug'ıyla filtreler; `getFirst()` ve `find()` tek kayıt okuması sunar. Konum hazır değilse servis boş sonuç döndürür. Tema Blade'i sorgu, modül keşfi veya kaynak doğrulama yapmaz.

Şubeler kartında ad, kategori, varsa okunabilir adres, telefon ve saatler gösterilebilir; boş alanlar için boş buton oluşmaz. Harita URL'si varsa dış bağlantı sunulur; koordinat tek başına sağlayıcı entegrasyonu sayılmaz. Harita iframe'i veya dış script otomatik yüklenmez. Modül özel public kaynak-geçerlilik filtresi eklemez; bulunmayan ilişki adı gösterilmez.

Branches veya Konum kapalı/eksikse isteğe bağlı tema bölümü boş sonuçla gizlenir; çekirdek sayfa sınıf çözümleme hatası vermez. Branches'a ait doğrudan liste route'u varsa modül erişim politikası uygulanır. Çekirdek controller'a kurulu olmayan modül sınıfı zorunlu constructor bağımlılığı eklenmez; bağımlılık denetimi sınıf çözümlemeden önce yapılır.

## Paketleme, kurulum, izin ve yaşam döngüsü

- Önerilen kimlik `Branches` / `branches`; kendi composer.json, module.json, features.json, migration, menü, route ve izinleri bulunacak. Paket adı ve PHP/Laravel koşulları uygulama sırasında kurulu sürümler ve mevcut şablonlardan doğrulanacak; bu plan yeni bağımlılık kurulumu yapmaz.
- Mevcut Konum module.json sürümü **0.1.0**, paket adı **ekagent/konum**. Branches `module.json.requires` içinde `konum` için doğrulanmış uyumlu sürüm koşulu taşımalı; başlangıç önerisi `^0.1.0`, nihai koşul servis uyumluluğu doğrulanınca kesinleşir. Modül bağımlılığı ile Composer paket bağımlılığını karıştırma; kurucunun gerçek çözümleme yolunu incele.
- **Dağıtım önkoşulu:** `D:\localhost\ekAgentModules\Konum` var, ancak ortak `catalog.json` içinde Konum kaydı yok. Katalogdan Branches kurulabilmesi için Konum'un ayrıca onaylı release/asset/hash ve katalog kaydıyla dağıtılabilir olması gerekir. Bu görev katalog güncellemez veya push yapmaz. Yerel kaynak varlığı müşteri kurulumuna hazır yayın kanıtı değildir.
- Kurulum sırası: Konum paketi ve bağımlılıkları → migration/install seeder ve kullanılabilir import → Konum etkinlik/hazırlık kontrolü → Branches uyumluluk ve kurulum adımları → izin/asset/cache senkronizasyonu → Branches etkinleştirme. Eksik/uyumsuz bağımlılıkta açık hata ve kapalı durum korunur. Konum kodu etkin olsa da import hazır değilse Branches kayıt/gösterim akışları kullanılamaz.
- Ortak ModuleCatalog/ModuleDownload/ModuleArchive/ModuleCompatibility/ModuleManager/ModulePanelAssets kullanılacak. İkinci kurucu yazılmaz. ZIP açmak kurulum değildir. Yeni paket müşteri kurulumunu kendiliğinden yükseltmez.
- İzinler önerisi: `branches.menu`, `branches.view`, `branches.create`, `branches.update`, `branches.delete`. Menü için menu+view gerekir; her işlem route/controller seviyesinde denetlenir. Admin auth/aktif hesap ve `module.enabled:Branches` yanında Konum önkoşulu da denetlenir; süper admin bağımlılık etkinliğini aşamaz. Müşteri rollerine izinler otomatik atanmaz.
- Etkin Branches varken Konum kapatılması; kurulu Branches varken Konum kaldırılması mevcut bağımlı modül kurallarıyla engellenmelidir. Elle dosya kaybı gibi yollar için çalışma zamanı koruması ayrıca gerekir. Branches'ın kapatılması kendi menü/route/verisini erişime kapatır.
- Kaldırma veri silmez/migration rollback yapmaz; kod ortak geri alınabilir yedek akışına girer. Coğrafi silinmenin Branches kayıtlarını cascade ile yok etmesine izin verilmez. Kaynak ID koruma/soft-delete ve olası fiziksel silme davranışına uygun DB kısıtı uygulama keşfinde seçilir.
- Normal etkinleştirme örnek şube/bayi kaydı üretmez. Yalnız `BranchCategorySeeder` açık install seeder'ı olarak çalışır ve Şube/Bayi kategori referanslarını tekrar çalıştırılabilir biçimde hazırlar. Yeniden etkinleştirme kullanıcı kayıtlarını veya sıra/aktiflik seçimini sıfırlamaz.

## Tutarlılık, hatalar ve eşzamanlılık

Branches kaydı, gerekli ek alanlar ve varsa seçilen kategori doğrulaması bir bütün olarak kaydedilir; kısmi kayıt ve başarısız işlem bildirimi oluşmaz. Kaynak kayıt doğrulaması ile yazım arasındaki değişim riski uygulayıcı tarafından Konum import/deaktivasyon davranışına göre ele alınır; kaydetme anı ve okuma anı kontrolleri beraber korunur. Transaction tek başına modül dosyasının silinmesini veya dış import sürecini kilitlemez.

Tekrarlı kurulum/seed denemesi tanım çoğaltmaz. Formun çift gönderimi ve eşzamanlı düzenlemede yinelenen kayıt/kayıp güncelleme riski mevcut admin örüntüsü üzerinden değerlendirilir; ad için gereksiz unique kısıtı konmaz. Özel dağıtık kilit veya sipariş tipi idempotency altyapısı varsayılmaz. Sıra eşitliğinde ID belirleyicidir. Başarısız istek doğrulanmış eski kullanıcı değerlerini korur; kaynak eksikken gizli fallback ile serbest yer adı kaydetmez.

Önemli hata durumları: bağımlılık eksik/kapalı/uyumsuz, tablolar veya tamamlanmış import yok, `warnings` içindeki tutarsız kaynak, sahte ID ve çapraz il/ilçe zinciri, boş alt seçenek, düzenleme sırasında silinen/pasifleşen kaynak, eski AJAX yanıtı, geçersiz iletişim URL'si/koordinat, yetkisiz işlem ve başarısız kurulum. Yöneticiye düzeltilebilir neden gösterilir; ziyaretçi tarafında teknik hata veya eski adres yayımlanmaz.

## Uygulayıcı için sıra ve tamamlanma koşulları

1. Kullanıcının aşağıdaki kararlarını ve plan incelemesini al; onaylı kapsamı plana işle. Görsel aşaması ancak sonraki açık görev kapsamında yürütülür.
2. İlgili kuralları tekrar doğrula; paket API'sine geçmeden Composer/JS sürümlerini ve Boost dokümanlarını kontrol et. En yakın tek mevcut CRUD modülünü route/request/controller/model/Blade/JS/izin/bildirim/test bağlantılarıyla incele; yalnız eksik konu için ikinci örneğe bak.
3. Konum kaynak ID türlerini, ilişkileri, import/silme ve kurucu bağımlılık kontrolünü dar incelemeyle doğrula. Veri sahipliği ve önkoşulu koruyarak metadata, migration ve kayıt akışını uygula; repository veya her sınıfa interface ekleme.
4. Admin liste/form ve izinli adres aramasını ortak panel bileşenleriyle uygula; asset'leri modül içinde tut. features.json/ek alan kayıt akışını gerekiyorsa ortak altyapıya bağla.
5. Onaylanan public okuma/listeme kapsamını uygula. Modüle özgü tema rule/skill dosyalarında gerçek alan, servis ve boş sonuç sınırlarını belgele; henüz olmayan metot yazma.
6. Davranış değişiklikleri için en küçük anlamlı testleri ekle/güncelle. Test yazmadan önce testing-best-practices skill'ini oku; aşağıdaki hedefleri karşılayan mevcut testleri yeniden kullan. Planlama aşamasında test veya build çalıştırılmaz.
7. Uygulama görevi sırasında dar testler, PHP değiştiyse Pint, asset değiştiyse gerekli build ve masaüstü/dar ekran akış doğrulaması tamamlanır. Başarılı feature testlerinden sonra tam suite'i kullanıcıdan çalıştırması istenir. Kaynak staging ancak kod/doğrulama başarılı olduğunda yapılır; yayın için ayrıca açık talep gerekir.
8. Aynı görev kaydında değişen dosyaları, mimari/veri kararını, test/build sonuçlarını, gerçek doğrulama sınırlarını ve staging yolunu raporla. Temel akış eksikken planı Tamamlandı olarak yeniden adlandırma.

Dar test hedefleri: CRUD yetkileri ve menu/view ayrımı; başarı bildirimi/aktör davranışı; Konum önkoşulu ve bozuk zincir reddi; üst seviye değişiminde alt ID temizliği; pasif/geçersiz kaynak ve kayıtların public listeden dışlanması; geçerli filtre/sıralama ve boş sonuç; gerçek panel/komut giriş yoluyla bağımlılık kurulum-kapatma-kaldırma sınırları ve başarısız kurulumun kapalı kalması. Ortak kurucunun bütün ZIP güvenlik testlerini Branches içinde kopyalama; değişen bağımlılık davranışına ait mevcut testleri çalıştır. Kaynak ilişkilerini yapay biçimde hazırlayıp yaşam döngüsündeki eksik seeder adımını gizleme; HTTP/süreç sınırlarını fake ile yalıt. Stil/copy için yeni test yazılmaz.

## Kullanıcı kararları ve uygulama varsayımları

1. **Kararlaştırıldı:** Panel adı “Şubeler / Bayiler”, teknik ad Branches; tek modül.
2. **Kararlaştırıldı:** Kurulumda Şube/Bayi kategorileri seed edilir; ilk sürümde kategori CRUD'u yok, her kayıt tek kategori alır.
3. **Kararlaştırıldı:** Tüm Konum hiyerarşisi alanları isteğe bağlıdır; seçilen alanlar kendi üst zinciriyle tutarlı olmalıdır.
4. **Kararlaştırıldı:** Çalışma saatleri isteğe bağlı kısa metindir. Tabler saat picker bulunmadı; kısa metin alanı kullanıldı.
5. **Uygulama varsayımı:** Ziyaretçi sunumu tema sorumluluğudur; modül özel public liste/detay route'u yoktur. Okuma servisi kategori filtrelemesini destekler.
6. **Kararlaştırıldı:** Geçersiz Konum kaynağı nedeniyle public listeden çıkarma/uyarı için özel iş akışı geliştirilmeyecek.

## Dağıtım önkoşulu — ayrı iş

Bu ilk sürüm için harici API, harita hesabı veya anahtar gerekmez. Elle harita URL'si/koordinat girişi yerel kapsamda kalır. Harita gömme, geocoding veya en yakın şube gibi daha sonraki istekler sağlayıcı/hesap/maliyet kararı ve ayrı onay olmadan geliştirilmez.

Ortak `catalog.json` dosyasında Konum 0.1.0 kaydı mevcut. Branches katalog/release adımları bu görevde yapılmadı; Konum asset URL'si ve hash'i de bu görevde uzaktan doğrulanmadı. Bu görev yalnız yerel uygulama ve `D:\localhost\ekAgentModules\Branches` kaynak staging'ini tamamladı; Git push, GitHub Release veya katalog değişikliği yapmadı.

## Uygulama sonucu — 22 Ekim 2026

- Branches modülü admin CRUD'u, kategori filtresi, isteğe bağlı hiyerarşik adres seçimi, Konum doğrulaması, kategori seed'i ve `BranchesFrontendService` ile uygulandı.
- Konum bağımlılığı `module.json` içinde `konum: ^0.1.0` olarak tanımlandı. Yerel katalogda Konum 0.1.0 kaydı mevcut; URL/hash bu görevde uzaktan doğrulanmadı. Geçersiz kaynak nedeniyle public gizleme/uyarı akışı eklenmedi.
- Modül tema rule/skill ve boş `features.json` eklendi. Tabler saat picker bulunmadığından çalışma saatleri kısa metindir.
- Dar doğrulama: Branches feature testleri 12/12 geçti (49 assertion); `npm run build` başarılı. Build mevcut app uyarıları verdi (fontaine opsiyonu, login wallpaper yolu ve büyük chunk).
- Kaynaklar `D:\localhost\ekAgentModules\Branches` konumuna stage edildi; 43 dosyanın SHA-256 karşılaştırması kaynakla eşleşti. Git push, ZIP, GitHub Release, katalog değişikliği veya uzak kurulum doğrulaması yapılmadı.
- Tarayıcıda masaüstü/dar ekran doğrulaması bu görevde yapılmadı; HTTP feature testleri admin liste/form render'ını kontrol etti.

## İncelenen dayanaklar

AGENTS.md; `.ai/rules/index.md`, Proje.md, Mimari-Kararlar.md, moduller.md, mimari-kod-yapisi.md, admin-arayuz.md, yetkilendirme.md, frontend.md, frontend-blade.md; modul-gelistirme workflow'u; Plans İş Akışı/README/Keşif Durumu/Görev Günlüğü. `.ai/rules` içinde konum/location/şube/bayi eşleşmeleri tarandı. Konum'un tema rule/skill'i, module/composer metadata'sı ve yalnız servis sözleşmesi belirsizliğini doğrulamak için KonumFrontendService incelendi. Ortak tema skill'i referans olarak okundu; herhangi bir tema entegrasyonu yapılmadı.
