# Branches — Şubeler / Bayiler birleşik modül planı

- Durum: Yeni; kullanıcı incelemesi bekleyen plan, 22 Ekim 2026.
- Görev: `TASK-20261022-0832-branches-plan`.
- Teknik aday adı: `Branches`; panel adı önerisi: **Şubeler / Bayiler**.
- Yetki sınırı: Yalnız planlama istendi. Bu belge uygulama, ImageGen, test/build, staging, paketleme, katalog değişikliği veya yayın yetkisi vermez. Kullanıcı planı incelemeden sonraki görsel ve kod aşamalarına geçilmez.

## Kesinleşen karar ve gerekçe

**Şube ve Bayi iki ayrı modül olmayacak.** Tek modül, tek kayıt modeli, ortak liste ve ortak ekle/düzenle formu kullanılacak. Ayrım gerekiyorsa aynı kayıtta tür/kategori alanıyla yapılacak. Bu alanın sabit Şube/Bayi seçimi mi yoksa yönetilebilir kategori mi olacağı henüz açık ürün kararıdır; iki paralel kayıt modeli oluşturulmayacak.

Kullanıcı, bu modülün Türkiye Konum modülüne bağlı çalışmasını istedi. Branches kullanımı için Konum kurulu, uyumlu ve etkin olmalı; adres verisi importu kullanılabilir durumda bulunmalıdır. Konum coğrafi referansın sahibidir; işletmenin şube/bayi kaydının sahibi Branches olur. Konum'da şube CRUD'u veya yeni il/ilçe/mahalle tanımlama akışı kurulmaz.

CMS'nin küçük işletme siteleri hedefi içinde çok konumlu işletmelerin tekrar eden adres, iletişim ve çalışma saati kayıtları makul ihtiyaçtır. Settings tekil genel iletişim ayarlarını taşır; kayıt bazlı CRUD, durum ve sıralama için uygun değildir. Brand çalışılan markaları, Sector hizmet verilen sektörleri tanıtır. Branches bu kayıtları kopyalamaz; bunlara ilişki, senkronizasyon veya zorunlu bağımlılık eklemez.

21 Ekim keşfindeki orta güvenli öneri ve `Keşif Durumu.json` içindeki `recommended_not_started` kaydı geçmiş keşif durumudur. Bu planın ilerleme dayanağı yeni kullanıcı kararıdır; geçmiş baseline'ın diğer modüller için vardığı sonuçları değiştirmez. README/keşif durumunun plan hazır olarak güncellenmesi ana görev kaydında yapılmalıdır.

## İlk sürüm kapsamı önerisi

| Alan | Davranış / sahiplik |
| --- | --- |
| Ad | Zorunlu şube veya bayi adı; aynı adlı farklı adreslere izin önerilir. |
| Tür/kategori | Tek kayıtta Şube/Bayi ayrımı; sabit seçenek veya yönetilebilir kategori kararı bekliyor. |
| Konum bağlantısı | Kaynak ID'leriyle il, ilçe, kaynak ara bölgesi, mahalle, isteğe bağlı cadde/sokak. İl ve ilçe zorunlu önerilir; alt seviyelerin zorunluluğu kaynak kapsamına göre kullanıcıyla netleşir. |
| Adres detayı | Bina, kapı, kat ve tarif gibi Branches'a ait serbest metin; kaynak yer adlarını tekrar yazdırmaz. |
| Telefon | İsteğe bağlı tek iletişim telefonu; uygun bağlantı gösterimi. |
| E-posta, web sitesi | İsteğe bağlı, biçimi doğrulanmış iletişim alanları. |
| Çalışma saatleri | İlk sürüm için isteğe bağlı kısa metin önerisi; haftalık yapılandırılmış takvim ancak kullanıcı isterse. |
| Harita bağlantısı / koordinat | İsteğe bağlı HTTPS URL ve/veya elle girilen enlem-boylam çifti; geçerli aralıklar doğrulanır. Harita sağlayıcı API'si yok. |
| Aktiflik ve sıra | Pasif kayıt ziyaretçiye gösterilmez; sıralama sıra, ardından kararlı ID ile yapılır. |
| Ek alanlar | Ortak `features.json` / `extra_fields` sözleşmesi; ilk sürümde ek tanım gerekmiyorsa boş forms nesnesi. |

Kapsam varsayımı: Ziyaretçi bir şube/bayi listesi veya tema içindeki aynı veriyle beslenen iletişim bölümü görecek; isteğe bağlı tür, il ve ilçe filtresi kullanılabilecek. Ayrı detay sayfası, slug/SEO alanları, fotoğraf/galeri, mesafeye göre en yakın konum, çalışma saatinden anlık açık/kapalı hesabı, bölge ataması, bayi başvurusu, CRM ve stok işlemleri bu ilk öneriye dahil değildir. Kullanıcı bunlardan birini isterse plan kapsamı yeniden belirlenir.

## Konum sözleşmesi ve veri tutarlılığı

Kaynak hiyerarşisi `cities → towns → districts → neighbourhoods → streets` biçimindedir: **il → ilçe → kaynak ara bölgesi → mahalle → cadde/sokak**. `districts` mahalle anlamına gelmez. Form etiketleri bu ayrımı açıkça taşımalıdır. Kaynak kimlikleri yeniden numaralandırılmaz; çocuk JSON özetleri ilişki sorgusu yerine kullanılmaz. Posta kodu gerekiyorsa kaynak seviyesinden beş karakterli metin olarak okunur.

İncelenen mevcut sözleşme `Modules\Konum\Services\KonumFrontendService` üzerindedir:

- `ready(): bool`: etkin modül, gerekli tablolar ve yapılandırılan kaynak commit'i için `complete` veya `warnings` importu denetlenir.
- `search(string $entity, array $parents = [], string $search = '', int $limit = 25)`: aktif, silinmemiş, tutarlı kayıtları ad/ID sırasıyla döndürür. Önek araması, varsayılan 25, üst sınır 100; geçerli seviyeler cities/towns/districts/neighbourhoods/streets. Üst filtreler city_id/town_id/district_id/neighbourhood_id'dir.
- Seçenekler bu servisle üst seçime göre sınırlanır. Tüm sokaklar yüklenmez. Güncel seçili ID'nin ilk 25 arama sonucunda bulunmaması geçersiz olduğu anlamına gelmez; düzenleme ve sunucu doğrulaması ID ve tüm üst zinciriyle ayrıca doğrulanmalıdır. Sınırsız `query()` tema listesi olarak kullanılmaz.

Önerilen veri kararı: Coğrafi ID'ler ve canlı kaynak adları asıl gerçektir; il/ilçe tabloları veya adları Branches'a kopyalanmaz. Adres detayı ve iletişim Branches'ın kendi verisidir. Tarihsel teslimat/sipariş kanıtı olmadığı için ilk sürümde adres snapshot'ı tutulması önerilmez. Kaynak adının değişmesi canlı gösterime yansır; kaynak ilişki değişimi otomatik olarak yeni adrese dönüştürülmez.

Silinmiş, pasif veya tutarsız kaynak/üst zincir tespitinde kayıt korunur, yönetici listesinde düzeltme uyarısı ve kayıt kimliği gösterilir; geçersiz adres ziyaretçiye sunulmaz. Bu durum kaydın kullanıcının belirlediği aktiflik değerini sessizce değiştirmez; geçerlilik ayrı hesaplanır. Yönetici formu mevcut bozuk ID'yi gizlice başka seçenekle değiştirmez. Konum yeniden kullanılabilir olduğunda kayıt yeniden doğrulanabilir. Bu yayın davranışı kullanıcı incelemesine sunulan öneridir.

Üst seçim değiştiğinde bütün alt seçimler temizlenir; geç gelen AJAX cevabı eski üst seçime ait seçenekleri geri getiremez. İstemcideki seçeneklere güvenilmez: kayıtta her ID'nin aktifliği, tutarlılığı ve aynı üst zincire ait olduğu sunucuda tekrar doğrulanır. İl/ilçe dışındaki boş seviyeler için hayali kaynak kayıt üretilmez. Cadde seçilmişse gereken bütün üst ID'ler bulunmalıdır.

Konum'un kaynak dökümü 11 Mayıs 2024 tarihlidir; güncel resmî kapsama garantisi verilmez. Kaynakta olmayan yer için Branches formundan coğrafi kayıt CRUD'u açılmaz. Manuel adres detayı desteklenir; zorunlu konum seviyelerinin bulunamaması kayıt yapmayı engelliyorsa kullanıcıya açık hata verilir. Polygon veya spatial sorgu gerekmez; kaynak polygon'u koordinat alanı yerine kullanma.

## Admin akışı

1. Yetkili yönetici ortak sidebar'dan Şubeler / Bayiler listesine gelir. Liste ad, tür, il/ilçe, telefon, aktiflik, kaynak geçerliliği ve sıra sütunlarını; arama, tür ve durum filtrelerini sunar. Yajra backend, Tabler ve `<x-admin.data-table>` ile mevcut ortak başlatıcı kullanılır.
2. Listedeki ekle düğmesi ayrı ekleme sayfasını açar. Form önerisi üç alan grubu/sekme: **Genel** (ad, tür, aktiflik/sıra), **Adres** (hiyerarşik seçim ve detay), **İletişim** (telefon, e-posta/site, saatler, harita).
3. İl seçilir, ilçe seçenekleri yüklenir; kaynakta gereken alt seviyeler sırayla açılır. Bağımlılık/import hazır değilse seçim yerine neden ve süper adminin yapacağı işlem gösterilir; kayıt işlemi engellenir.
4. Kaydetme başarılı olunca listeye dönülür. Hatalarda eski değerler korunur, hata bulunan sekme açılır. Düzenleme ayrı sayfadır; silme ortak onay akışını kullanır. Satır işlemleri erişilebilir etiketli ikonlarla gösterilir.
5. Başarılı CRUD işlemlerinden sonra ortak `NotifyService` ve CRUD bildirimi kullanılır. Aktör/süper admin davranışı ortak servise bırakılır; hassas adres/iletişim ayrıntıları bildirim içine dökülmez.

Konum seçenek araması Branches admin akışına ait yetkili endpoint üzerinden sunulur. Konum modülü mevcutta herkese açık HTTP adres API'si sağlamaz; böyle bir API varmış gibi davranılmaz. Endpoint yalnız izin verilen entity/üst alanlarını kabul eder; mevcut uygulama hız sınırları ve admin yetkisi uygulanır. Branches yöneticisine adres seçebilmesi için Konum yönetim ekranı izni otomatik verilmez.

## Ziyaretçi akışı ve okuma servisi önerisi

Önerilen `Modules/Branches/app/Services/BranchesFrontendService.php` tema tüketimine aktif ve geçerli kayıtların sınırlı/sayfalı listesini sağlar. Tür/il/ilçe filtreleri, kabul edilen değerler, sayfa sınırı, dönüş biçimi ve boş sonuç sözleşmesi uygulama öncesi kesinleştirilir; bugün bu servis veya metotlar mevcut değildir. Tema Blade'i sorgu, modül keşfi ve zincir doğrulaması yapmaz.

Şubeler kartında ad, varsa tür, okunabilir adres, telefon ve saatler görünür; boş alanlar için boş buton oluşmaz. Harita URL'si varsa dış bağlantı sunulur; koordinat tek başına sağlayıcı entegrasyonu sayılmaz. Harita iframe'i veya dış script otomatik yüklenmez. Filtre sonucu yoksa kısa boş durum gösterilir. Kullanıcı aktif kayıt görürken pasif veya geçersiz kayıt ID'siyle veri alamamalıdır.

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
- Normal etkinleştirme örnek müşteri kaydı üretmez. Gerekli sistem tanımları varsa yalnız açık install seeder'ında idempotent hazırlanır. Sabit tür enum'unda kategori seeder'ı gerekmez. Yeniden etkinleştirme kullanıcı değerlerini veya sıra/aktiflik seçimini sıfırlamaz.

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

## Kullanıcı kararı bekleyenler

1. Panel adı “Şubeler / Bayiler” ve teknik ad Branches uygun mu? Tek modül kararı kesindir.
2. Ayrım sabit Şube/Bayi türü mü, kullanıcı tarafından eklenebilen kategori mi? Tür alanı zorunlu mu? Aynı kayıt birden çok kategoriye girecek mi? İlk sürüm önerisi tek, sabit Şube/Bayi seçimi.
3. Adreste il/ilçe zorunlu; ara bölge/mahalle/cadde seçimi kaynakta varsa ilerlenen isteğe bağlı seviyeler mi olacak? Eksik kaynak adresinde kullanıcı hangi alt seviyeye kadar kaydedebilmeli?
4. Telefon, e-posta/site, çalışma saatleri ve harita alanlarından hangileri gerekli? Saatler kısa metin mi haftalık yapı mı? Öneri: isteğe bağlı kısa metin ve harita bağlantısı.
5. Ziyaretçide yalnız liste/tema bölümü yeterli mi; tür ve il/ilçe filtreleri isteniyor mu? Ayrı detay sayfası gerekecek mi?
6. Canlı Konum adları ve geçersiz kaynakta kaydı public listeden geçici dışlama önerisi kabul ediliyor mu? Tarihsel adres snapshot'ı gerektiren somut iş ihtiyacı varsa ayrıca belirtilmeli.

## Onay bekliyor / dağıtım önkoşulları

Bu ilk sürüm için harici API, harita hesabı veya anahtar gerekmez. Elle harita URL'si/koordinat girişi yerel kapsamda kalır. Harita gömme, geocoding veya en yakın şube gibi daha sonraki istekler sağlayıcı/hesap/maliyet kararı ve ayrı onay olmadan geliştirilmez.

Konum'un katalogda yayımlanması mevcut dağıtım altyapısıyla ayrı bir yayın işidir. Onay gelene kadar bu belgeyle yerel plan incelemesi yapılabilir; katalogdan temiz müşteri kurulumunun dağıtım zinciri tamamlanmış sayılmaz. Bu plan Konum veya Branches için release/push izni içermez.

## İncelenen dayanaklar

AGENTS.md; `.ai/rules/index.md`, Proje.md, Mimari-Kararlar.md, moduller.md, mimari-kod-yapisi.md, admin-arayuz.md, yetkilendirme.md, frontend.md, frontend-blade.md; modul-gelistirme workflow'u; Plans İş Akışı/README/Keşif Durumu/Görev Günlüğü. `.ai/rules` içinde konum/location/şube/bayi eşleşmeleri tarandı. Konum'un tema rule/skill'i, module/composer metadata'sı ve yalnız servis sözleşmesi belirsizliğini doğrulamak için KonumFrontendService incelendi. Ortak tema skill'i referans olarak okundu; herhangi bir tema entegrasyonu yapılmadı.
