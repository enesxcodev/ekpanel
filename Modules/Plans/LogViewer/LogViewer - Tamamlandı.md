# LogViewer — Tamamlandı

## Aday kararı ve gerekçe

**Karar:** Yeni bağımsız admin modülü — `LogViewer` (görünen ad: Log İzleme, katalog alias'ı: `logviewer`).

**Kullanıcı ihtiyacı:** Laravel uygulama loglarını dosya adıyla sol taraftaki ağaçta bulup, seçilen dosyanın güncel son satırlarını sağda okuyabilmek ve elle yenileyebilmek. Bu, günlük CMS içerik CRUD akışlarından ayrı, kurulum/operasyon hata ayıklama sorumluluğudur. Kayıtlı modül haritasında mevcut modüller içerik, kimlik, ticaret ve adres verisi yönetiyor; log dosyası okuma ve sunucu içi operasyon verisi görüntüleme kapsamı yok. Bu değerlendirme bağımsız modül kararını destekler (yüksek güven, doğrudan kullanıcı talebi); üretim ortamında bu ekranın müşteri-adminlere açılıp açılmayacağına dair karar ayrıca gerekir.

**Sınır:** Salt okunur log listeleme/görüntüleme. Log yazma, temizleme/silme, indirme, canlı stream/polling, arama/filtreleme ve diğer sunucu dosyalarına erişim ilk sürümde yok. Üretim günlüklerinde token, e-posta, kişisel veri gibi sırlar bulunabileceğinden yalnız super-admin erişimi varsayılan güvenli sınırdır; rolü genişletmek ayrıca ürün kararı gerektirir.

## Arayüz konsepti ve etkileşim

Kullanıcının verdiği [Tabler Admin Template önizlemesi](https://tabler.io/admin-template/preview) incelendi. Resmi Tabler sayfası “Logs page” örnek sayfasını sunuyor; bu plan mevcut uygulamadaki Tabler bileşen ve tema dilini esas alır, şablonu projeye kopyalamaz.

- Geniş ekranda sol panel yaklaşık 280–320px: `storage/logs` kökünden türetilmiş, klasörleri aç/kapatılabilen dosya ağacı. Aktif dosya belirgin seçili görünür.
- Sağ içerik paneli: seçili log adı ve göreli yolu, dosya boyutu / son değişiklik bilgisi, satır sayısı seçicisi (varsayılan 200; 100/200/500 sınırlandırılmış seçenekler), **Yenile** düğmesi ve satır numaralı, monospace log alanı.
- İlk açılışta dosya seçili değilse yönlendirici boş durum; klasör/log yoksa ayrı boş durum. Okunamayan veya artık bulunmayan dosyada güvenli hata bildirimi gösterilir.
- Dar ekranda ağaç üstte açılır liste/offcanvas olur, içerik aşağıda tam genişlik gösterilir. Uzun satır yatay kaydırılır; sayfa düzeni genişlemez.
- Log yanıtları HTML olarak yorumlanmaz; özel karakterler metin olarak escape edilir. Hassas veriler olabileceğine dair admin bilgilendirmesi eklenir.

## Kurulum ve modül sözleşmesi

1. Ortak `.ai/rules/moduller.md` sözleşmesine uygun `Modules/LogViewer` paketini hazırla: `module.json`, `composer.json`, `features.json`, PSR-4 autoload, routes, provider, `config/menus.php`, modül tema rule/skill'i. Özel alan formu yoksa mevcut sözleşmeye uygun boş `forms` girdisi kullan.
2. İlk sürüm için DB migration/model/seeder gerekmiyor. Log dosyaları Laravel'in yapılandırılmış `storage/logs` alanından okunur; kullanıcı log verisi modül veritabanına kopyalanmaz. Varsayılan kanal/tek dosyalı ve günlük rotate edilmiş dosyaları keşfet; desteklenmeyen kanal biçimlerini açık hata durumuyla ele al.
3. Sidebar'da `logviewer.menu` ve ekran/endpoint için `logviewer.view` izinlerini tanımla. Ekran ve her veri endpoint'i modül etkinliğini, guard'ı ve gerçek izinleri sunucuda doğrulasın. İlk sürüm sadece `super-admin` rolüne izin vermeli; müşteri-admin için izin tahsisi kapalı kalmalı.
4. Özel JS/CSS gerekiyorsa yalnız modül `resources/assets` içinde, `module.json.panel.assets` manifest'iyle ve ortak panel yükleyicisi üzerinden paketle. Basit yenileme için ortak form/GET navigasyonu yeterliyse modüle özel JS ekleme.
5. Kurulum/etkinleştirme ortak ModuleManager üzerinden çalışmalı. Dış bağımlılık ve otomatik seeder yok. Kapatma ekran/route erişimini kesmeli; kaldırma dosyalara dokunmamalı ve logları silmemeli.

## Dosya güvenliği ve okuma davranışı

- Tek izinli kök Laravel çalışma alanındaki `storage/logs` dizinidir. İstemciden mutlak yol veya keyfi path kabul etme; yalnız sunucunun ürettiği dosya kimliği/relative path seçeneği kabul edilir.
- Kanonik `realpath`/normalize kontrolleriyle hedefin kök altında kaldığını ve sembolik linkle dışarı kaçmadığını doğrula. Yalnız normal dosya, `.log` uzantısı, beklenen isim biçimi ve boyut/okuma limiti karşılanıyorsa listele/oku. Yolu decode ettikten sonra traversal ve null-byte kontrolleri uygula.
- Dosya ağacı sadece alt dizinleri ve uygun log dosyalarını gösterir; dotfile, symlink, klasör dışı dosya, arşiv ve başka kanalların harici yolları gösterilmez.
- Son N satırı belleği sınırlı geriye doğru okuma ile al; dosyanın tamamını `file()`/tek seferde yükleme. Maksimum satır sayısı 500, maksimum yanıt byte sınırı belirle (başlangıç önerisi 1 MiB); çok uzun tek satırları kırp veya byte limiti içinde güvenli biçimde göster. N değerini sunucu allowlist'iyle doğrula.
- Listelemeden okuma anına kadar dosya silinmiş/değişmiş olabilir. Her yenilemede kök, tür, gerçek yol, boyut ve izin kontrolünü tekrar et; yarış halinde 404/uygun 422 yanıtı ver, hata ayrıntısında mutlak sunucu yolunu ifşa etme.
- Okuma salt okunurdur; log truncate, rotate, delete veya download işlemi yoktur. Log içeriği untrusted text kabul edilir; XSS çalıştırma yolu olmamalıdır.
- Log içeriğini uygulama loguna tekrar yazma; istisna raporlarında dosya içeriği/sırları taşımadan sınıf ve korelasyon bilgisi kullan.

## Eşzamanlılık ve tutarlılık

- Birden çok admin aynı dosyayı okurken dosya lock'u gerektirmez; rotasyon sırasında inode/path değişebilir. Her istek anlık görünür dosyayı doğrular ve son N satır döndürür; atomik snapshot garantisi verilmez.
- Büyük/aktif dosya okunurken sınırlı geriye doğru okuma, byte üst sınırı ve istek timeout'uyla kaynak kullanımı sınırlandırılır. Varsayılan 200 satır çoğu hata ayıklama için yeterli; 500 üst sınır kullanıcı yükünü kontrol eder.
- İstemci dosya listesinin eski olabileceğini varsayar; seçim ve refresh endpoint'i her seferinde yeniden doğrulama yapar.
- Otomatik yenileme yoktur. Yenile düğmesi aynı seçili dosyayı tekrar GET eder; başarısızlık mevcut görünür çıktıyı yanlışlıkla yeni veri gibi göstermemeli, hata mesajı ve son başarılı çekim zamanı ayrılmalıdır.

## İlişkili sınırlar ve açık kararlar

- ModuleManager kurulum/izin/asset davranışı ortak kalır; modül paralel modül kurulum sistemi kurmaz.
- Ana uygulamanın merkezi log konfigürasyonu, retention/rotation, observability, Sentry/CloudWatch benzeri entegrasyonlar bu adayın dışında kalır.
- Varsayılan yetki super-admin-only'dir. Ürün ileride müşteri-admin kullanımı istiyorsa log redaksiyonu, kanal/ortam ayrımı ve erişim denetimi kararı uygulamadan önce verilmelidir.
- Tabler’ın online preview sayfasının mevcut DOM/markup'ı bu planlama aşamasında kod olarak kopyalanmadı; görsel referans yalnız sayfa türü ve genel responsive Tabler düzenidir.

## Dar doğrulama planı

Uygulama aşamasında proje kurallarına göre yalnız anlamlı davranış/hata yolları kapsansın:

1. Yetkili super-admin log ağacını görebilir; izinsiz kullanıcı ekranı ve JSON endpoint'lerine erişemez.
2. Aktif/kurulu modül koşulu ve `logviewer.view` izni hem sayfa hem dosya içeriği isteğinde zorunludur.
3. `storage/logs` altındaki geçerli .log listelenir ve son varsayılan N satır doğru sırada döner; satır sayısı allowlist/max ile sınırlandırılır.
4. `../`, URL-encoded traversal, mutlak yol, null byte, symlink ile kök dışına kaçış, log olmayan dosya reddedilir; dış dosya içeriği sızmaz.
5. Eksik, okunamayan veya yarış sırasında rotate edilen dosya güvenli kullanıcı hatası üretir; içerik/absolute path exception veya loglara yazılmaz.
6. Büyük dosyada bounded tail uygulanır; HTML/JS benzeri içerik metin olarak escape edilir.
7. Modül menüsü etkinlik ve izinle görünür; modül kapatma/kaldırma log dosyalarını değiştirmez.

PHP değişirse dar ilgili testleri çalıştır; ardından `vendor/bin/pint --dirty --format agent`. Varsa modül asset değişikliği için modül Vite build ve ana `npm run build`, yayın asset kontrolü ve tarayıcıda geniş/dar ekran görüntü kontrolü. Kapsam bu turda planlamadır; uygulama testi/build/staging yapılmaz.

## Uygulayıcının tamamlanma koşulları

- Kurulum/izin/menü/etkinlik sözleşmesi ortak ModuleManager ile doğrulanmış.
- Yalnız `storage/logs` içindeki allowlist dosyalara güvenli salt okunur tree + bounded tail + manuel refresh çalışır.
- Son 200 satır varsayılan; seçenekler 100/200/500 ve maksimum 500. Byte limiti/çok uzun satır davranışı belgelenmiş/test edilmiş.
- Hassas veriye erişim sadece super-admin; ekran ve endpoint yetkilendirmeleri test edilmiş.
- XSS, traversal, symlink, eksik/rotate edilmiş dosya ve büyük log hata yolları test edilmiş.
- Tema entegrasyon yönergesi, dar testler, Pint, gerekli build ve kaynak staging tamamlanıp görev günlüğüne doğrulukla kaydedilmiş.
- ZIP/release/catalog/push yapılmaz; bunlar ayrıca açık kullanıcı talebi gerektirir.

## Bekleyen onaylar

Harici API veya yeni altyapı gerekmiyor; yerel `storage/logs` dosyalarını okuma modülün bağımsız işidir, bu nedenle bu plan için ayrıca entegrasyon onayı yoktur. Buna rağmen üretimde süper-admin dışına yetki açılması ve logları indirme/silme/temizleme veya harici izleme servisi kapsamı bu planın dışında; ileride istenirse ayrı kapsam/onay gerekir.

## Uygulama sonucu

- Modül ortak `module:make` iskeleti, `module.json`, `composer.json`, `features.json`, route, menü, izin ve modül tema rule/skill dosyalarıyla oluşturuldu.
- Süper-admin-only ekran, `storage/logs` altındaki güvenli `.log` ağacı, son 100/200/500 satır seçimi (varsayılan 200), elle GET yenileme, 1 MiB tail sınırı ve HTML escaping uygulandı.
- Dar feature testleri: 11 geçti, 1 symlink testi Windows'ta sembolik link oluşturulamadığı için atlandı; 21 assertion.
- Pint ve `php artisan view:cache --no-interaction` başarılı. Modüle özel derlenmiş JS/CSS olmadığı için Vite/npm build gerekmedi.
- Kaynak `D:\localhost\ekAgentModules\LogViewer` konumuna 27 dosya olarak kopyalandı ve tüm dosyaların SHA-256 özeti kaynakla eşleşti.
- Modül yerel kurulumda etkinleştirildi. ZIP, release, katalog güncellemesi ve push yapılmadı.
- Arayüz prototipi [prototype.html](prototype.html) olarak plan klasöründe kaldı; gerçek uygulama bu statik prototip yerine ortak admin layout'uyla render edilir.


## Frontend prototipi

Kullanıcının frontend isteği kapsamında açılabilir statik prototip hazırlandı: [prototype.html](prototype.html). Prototip yalnız örnek log verisi kullanır; gerçek dosya okuma, route, backend veya yetkilendirme içermez. Yenile düğmesi yalnız son çekim saatini ve bildirimi günceller. Bu ekran görsel/etkileşim incelemesi içindir; uygulama tamamlanması veya staging değildir.
