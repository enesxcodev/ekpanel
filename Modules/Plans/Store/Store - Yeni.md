# Store (e-ticaret) modülü planı

## Amaç ve gerekçe

Katalogdaki `Products` modülü kurumsal ürün kataloğudur. `D:\localhost\ekAgentModules\Products\.ai\rules\tema-entegrasyonu.md` ve `D:\localhost\ekAgentModules\Products\skills\products-theme-integration\SKILL.md`, fiyat, stok, varyant, sepet, sipariş ve ödeme işlevlerini açıkça bu modülün dışında tutar. Satış yapan bir müşteri kurulumu, katalog göstermekten siparişi tamamlamaya kadar bu işlevleri birlikte kurabilmelidir. Bu yüzden tek bir Store adayı, ayrı cart/orders/payments alt modüllerinden daha anlaşılır bir kurulum birimi olur.

Bu doküman bir aday planıdır; uygulama başlangıcı veya ürün kapsamı için son onay değildir. Keşif özeti [README.md](README.md) içindedir.

## Kapsam önerisi

Store modülünün tek kurulum ve izin sınırında değerlendireceği yetenekler:

- Satılabilir ürünler, kategoriler, SKU'lar, varyantlar, fiyatlar ve ürün medyası.
- Stok miktarları ve stok hareket geçmişi.
- Sepet, ziyaretçi/müşteri bilgileri ve sipariş oluşturma akışı.
- Sipariş durumu ve durum geçmişi; iptal, iade ve geri ödeme akışlarının kapsamı ayrıca belirlenir.
- Ödeme sağlayıcısı ayarı, ödeme başlatma/sonuç alma ve tekrar gönderilebilen bildirimlerin güvenli işlenmesi.
- Teslimat seçenekleri, adres ve gönderi bilgileri; kargo etiketi/harici taşıyıcı entegrasyonu ayrıca kapsam kararı ister.
- İndirim kodu veya kampanya kuralları, yalnız MVP önceliği kabul edilirse.
- Ziyaretçi tarafında ürün listeleme/detay, kategori/filtre, sepet ve checkout; sipariş sonucu ve hesap içi sipariş görüntüleme kapsamı karar kapısından geçer.

## Onay bekleyen harici entegrasyonlar

Bu başlıkların durum kaydı [Onay Gerektiren İşler](Onay%20Gerektiren%20İşler.md) içinde tutulur. Store adayının genel kapsam önerisinde görünürler; ancak projede sağlayıcı/altyapı bulunduğu doğrulanmadı. Kullanıcı açıkça onaylayıp gerekli servis ve ürün kararlarını vermeden geliştirilmez:

- **Ödeme sağlayıcısı entegrasyonu:** sağlayıcı seçimi, hesap/anahtar yapılandırması, gerçek ödeme başlatma, callback/webhook ve iade/geri ödeme API akışları. Onay öncesinde sağlayıcıya bağlanma veya canlı/fake entegrasyonu bitmiş gibi sunma yoktur. Sağlayıcıdan bağımsız sipariş/ödeme durumları ve yerel test fake'leri, yalnız uygulama planında açıkça yer alırsa hazırlanabilir.
- **Harici kargo/taşıyıcı entegrasyonu:** taşıyıcı API'si, fiyat teklifi, etiket/gönderi oluşturma, takip senkronizasyonu ve iptal API'leri. Onay öncesinde gerçek gönderi oluşturma veya taşıyıcıya bağlanma yoktur. Yerel teslimat adresi ve manuel gönderi bilgisi gibi sağlayıcıdan bağımsız kapsam ancak ayrıca seçilip planlanırsa uygulanabilir.

6.1 SOL her entegrasyon için onaylanması gereken kararları ve bağımsız uygulanabilen yerel işleri planda ayrı göstermeli; 5.6 TERA açık onay gelmeyen entegrasyonları `waiting_approval` durumunda bırakmalıdır. Onay, GitHub push/release veya katalog yayını yetkisi vermez.

Fiyat para birimi ve vergi gösterimi, misafir checkout ile hesap zorunluluğu, tek/çoklu depo, sağlayıcı ve kargo ülkeleri uygulama öncesi ürün kararlarıdır. Bunları varsayılan kabul etme; modül taslağında netleştir.

### Uygulama kararları

- 21 Ekim 2026: Varsayılan para birimi TL (`TRY`) olarak onaylandı. Tutarlar kuruş tamsayısı ve üç harfli para birimi koduyla saklanır; bu, ileride başka birimlerin eklenmesine şema değişikliği olmadan izin verir.
- Vergi gösterimi ve misafir checkout/hesap zorunluluğu henüz karar bekliyor. Bu kararlar, sipariş toplamı ve müşteri verisi saklama davranışını belirlediğinden yerel checkout tamamlanmadan önce netleştirilmelidir.

## Var olan Products modülüyle sınır

Products kurumsal katalog işlevini sürdürür. Store bu modülü değiştirmeden veya onun tablo şemasını sessizce genişletmeden tasarlanır. Store'un ürün/veri sahipliği için uygulama öncesi bir kısa karar kaydı hazırla:

1. Store satılabilir ürün ve varyantları kendi modül sınırında mı tutacak?
2. Products kaydı ile ticari ürün arasında isteğe bağlı bağlantı gerekli mi; veri aktarımı/bağlama kullanıcı tarafından açıkça seçilecek mi?
3. Products modülünün kurulu olmadığı müşteri kurulumunda Store çalışabilecek mi?

Öneri: Store temel ticari akışı için `products` modülüne zorunlu bağımlılık getirmesin. İsteğe bağlı bağlama gerçekten ihtiyaçla doğrulanırsa sahiplik, silme, senkronizasyon ve kapalı/kurulu olmayan modül davranışları ayrıca tanımlansın. Aynı ürünün iki yerde düzenlenmesine yol açacak örtük çift yönlü senkronizasyon yapılmasın. Son model/table/ilişki seçimi, mevcut kod ve Modül Kuralları incelendikten sonra uygulayıcı tarafından gerekçelendirilsin; belirli bir mimari kalıp şart koşulmaz.

`Users` modülü web hesabı ve oturum sağlar; satış hesabı politikası netleşmeden zorunlu bağımlılık veya kullanıcı rolü varsayma. Web kullanıcıları ile admin guard/izinlerini karıştırma. Misafir siparişi varsa sipariş için gereken iletişim/adres anlık görüntüsü müşteri hesabı silinmesinden bağımsız korunmalıdır.

## Admin ürün oluşturma/düzenleme formu

ImageGen ile üretilen Store ürün formu konsepti: [Store-Ürün-Formu.png](Store-Ürün-Formu.png). Görsel tasarım referansıdır; ürün formunu gösterir, sepet ve sipariş ekranları için ayrıca arayüz gerekir.

Form ekle ve düzenle aynı alan gruplarını ve doğrulamayı kullanmalı; düzenlemede sipariş geçmişine bağlı ticari değerlerin nasıl değişeceği ayrıca korunmalı. Blog referansındaki iki kolonlu Tabler düzeni (ana form solda, durum/medya gibi kartlar sağda), koyu sidebar ve yeşil ana eylem korunarak sekmeli içerik gereksinimi:

| Sekme | Gerekli alanlar |
|---|---|
| Ürün bilgileri | Ürün adı, benzersiz slug, kısa açıklama, açıklama, kategori, marka alanı (marka kapsamı seçilirse), kapak görseli, sıralı galeri; ürün taslağı/yayını sağ kartta. |
| Fiyat ve stok | SKU, temel fiyat, indirimli/karşılaştırma fiyatı gerekiyorsa, vergilendirme gösterimi kararı, stok izleme anahtarı, eldeki miktar, stok eşiği ve stokta yok davranışı. Geçersiz negatif değerler sunucuda reddedilir. |
| Varyantlar | Varyant kullanımı, seçenek grupları ve değerleri (ör. beden/renk), varyanta özgü SKU/fiyat/stok, varyant ekle-düzenle-sil ve sıralama. Varyant kapatıldığında eski sipariş satırları bozulmamalı. |
| Teslimat | Fiziksel/dijital ürün kararı, ağırlık ve ölçüler gerekliyse, kargolanabilirlik; dijital ürün için dosya erişimi ve indirme sınırı kararı. Uygun olmayan alanda ilgisiz alan gösterilmemeli. |
| SEO | Sayfa başlığı, meta açıklaması, paylaşım görseli; değer boşsa varsayılan oluşturma davranışı ayrıca belirlenir. |

Sekme seçimi tarayıcı yenilemesinde kaybolmamalı; doğrulama hatasında hata olan sekme bulunup etkinleştirilmeli ve alan hatası sekme içinde görünür olmalı. Klavye erişimi ve mobilde sekme taşması ele alınmalı. Görsel yalnız konsept olarak üretilir; kaynak tasarımdaki alanlar uygulama gerçeği kabul edilmez. Ortak `features.json`/`ModuleFeatures`/`admin.features.fields` sözleşmesi yalnız gerçekten özelleştirilebilir ek alanlar için kullanılmalı; ürünün fiyat/SKU/stock gibi çekirdek kurallarını ek alanlara koyma.

## Uygulama planı — 5.6 TERA, medium

### A. Başlangıç ve keşif

1. Uygulama deposunda `AGENTS.md`, `.ai/rules/index.md` ve `Modules/` altındaki dosyaları kapsayan tüm kuralları oku; `.ai/rules` içinde `modül`, `kurulum`, `ürün`, `fiyat`, `stok`, `sipariş`, `ödeme`, `sepet`, `frontend`, `form`, `özellik` anahtarlarıyla kural grep'i yap.
2. Modül deposu katalog sırasını ve release sözleşmesini oku. Önce mevcut Products, Users ve Blog admin create/edit/list akışlarını, form alanları ve izin yaklaşımını incele; belirsiz yerlerde yalnız ilgili uygulama koduna geç.
3. PHP/Laravel/Nwidart, mevcut ödeme/medya/tablo ve frontend paketlerinin kurulu sürümlerini doğrula. API kullanımı gerekiyorsa Laravel Boost `search-docs` kullan; Nwidart kapsamı yoksa sürüme uygun resmi dokümana bak. Bağımlılık ekleme gereğini plana yaz, kendiliğinden dependency değiştirme.
4. İlk koddan önce yukarıdaki iş kararlarını ve Store ürününün Products ile ilişkisini kısa bir karar listesi olarak netleştir. Tasarım/ImageGen aşamasını yalnız seçilen plan uygulama kuyruğuna alındığında başlat.

### B. Modül iskeleti ve yüklenebilirlik

5. Nwidart modül kurallarına uygun klasör, `composer.json`, `module.json`, `features.json`, route, migrations, admin menüsü ve izin tanımlarını oluştur; proje kuralına göre dosyaları Artisan `make:` komutlarıyla üret.
6. Modülü public `ekAgentModules` kataloğu için dağıtılabilir tek paket sınırında tut. Katalog kimliği, PascalCase klasör adı, Composer paketi, `module.json` sürümü, PHP/Laravel sürüm koşulları, gerekiyorsa zorunlu modül bağımlılıkları ve install seeder listesi kurallarla uyumlu olsun. Var olmayan Composer API/koşul uyumluluğu varsayma.
7. Panel kurulumu ortak `ModuleCatalog`, `ModuleDownload`, `ModuleArchive`, `ModuleCompatibility`, `ModuleManager`, `ModulePanelAssets` akışını kullanmalı. Kurulumda bağımlılık çözümü/autoload, mutlak modül migration yolu, yalnız açıkça listelenmiş idempotent kurulum seeder'ları, asset yayını, izin senkronizasyonu ve cache yenileme başarısına göre etkinleşme uygulanmalı. Mevcut kurucu sürüm yükseltmesi yapmıyorsa upgrade desteği gibi sunma.

### C. Veri ve iş kuralları

8. Ürün, varyant, stok hareketi, sipariş, sipariş satırı, ödeme denemesi ve teslimat ilişkilerini gerçek ihtiyaçlara göre modelle. Her tablo ve alan için kaynak veri/snapshot farkını tanımla: sipariş satırı oluşturulduğunda ürün adı, seçilen varyant/SKU, fiyat, vergi/indirim ve teslimat adresi gibi geçmişi açıklayan değerler değişmez sipariş anlık görüntüsü olarak korunmalı.
9. Sipariş toplamı ve fiyat sunucu tarafında yeniden hesaplanmalı; istemciden gelen toplam, fiyat veya stok sonucu güvenilir sayılmamalı. Para değerleri kayan nokta yerine doğrulanmış hassasiyetle saklanmalı; seçilen para birimi ve yuvarlama kuralı tek ve tutarlı olmalı.
10. Eşzamanlı checkout ve stok azaltımında son birimi iki siparişin satmasını önle. Uygun transaction, satır kilidi veya atomik koşullu güncelleme yaklaşımını mevcut DB/iş yüküne göre seç; stok değişiklikleri ile sipariş/ödeme durum geçişlerinin tutarlılığını ve rollback sınırını açıklığa kavuştur.
11. Ödeme sağlayıcısı callback/webhook, imza doğrulama, gerçek ödeme başlatma ve iade API'si yalnız kullanıcı açık onayı sonrası kapsama girer. Onay verilirse tekrarlar idempotent işlenmeli, imza ve tutar/sipariş eşleşmesi doğrulanmalı, harici çağrı DB transaction süresinden ayrılmalı ve timeout/tekrar deneme davranışı tanımlanmalı. Gizli anahtarları kodda veya normal form alanında tutma. Onay yoksa gerçek sağlayıcı entegrasyonu geliştirme; bağımsız yerel sipariş durumları ve test fake'leri ancak ayrıca planlanmışsa uygulanabilir.
12. Sipariş durum geçişlerini izin verilen akışla doğrula; aynı olay iki kez geldiğinde stok iki kez artıp azalmamalı. İptal, ödeme reddi/zaman aşımı, kısmi/başarılı geri ödeme, kısmi stok ve webhook sırasız gelme senaryolarında tutarlı sonuç veya telafi edilebilir hata üret.

### D. Admin ve ziyaretçi arayüzü

13. Admin ürün, kategori/varyant, stok ve sipariş ekranlarını ayrı iş akışları olarak kur; ortak admin Tabler düzeni, server-side tablolar için `<x-admin.data-table>`, Yajra ve ortak başlatıcı kurallarını izle. Menüler `config/menus.php`, route'lar modül etkinlik ve işlem izni kontrollerini uygulasın.
14. Ürün create/edit sekmelerini yukarıdaki tabloya göre tamamla. Ekleme ve düzenleme aynı validation tanımını paylaşsın; sekme içi invalid alan, eski değer, dosya/media hatası ve varyant kaldırma durumu kullanıcıya açıklanır. Kaydedilmiş siparişleri etkileyen fiyat/varyant değişiklikleri sipariş snapshot'larını geriye dönük güncellemesin.
15. Ziyaretçi ürün listesi/detay, filtre ve sepet gibi entegrasyondan bağımsız ekranları uygula. Adres/checkout ve sonuç ekranları yalnız seçilmiş yerel kapsamda; gerçek ödeme veya harici kargo adımı yalnız kullanıcı açık onayı sonrası uygulanır. Blade içinde DB sorgusu/iş kuralı çalıştırma; veri controller/uygun serviste hazırlanmalı. Ürün stok dışı, modül kapalı/kurulu değil, ürün yayından kaldırılmış veya sepet satırı değişmiş durumları açıkça ele alınmalı.
16. Görsel konsept aşamasında `output/playwright/blog-form-desktop.png` referans alınarak ImageGen ile Store ürün create/edit ekranı tasarımı üret. Bu, plan seçilip tasarım aşamasına geçildikten sonraki iştir; tasarım görseli plan dosyalarının parçası değildir. Konseptin ardından uygulayıcı gerçek mevcut bileşenleri kullanır, görseli birebir kod sözleşmesi saymaz ve gerekli alan/sekme erişilebilirliğini korur.
17. Store'a özgü frontend CSS/JS'yi modülün `resources/assets` ve `resources/dist` sınırında tut; `module.json.panel.assets`, modül Vite yapılandırması ve `publicDir: false` kuralını uygula. Ortak panel koduna modül başına import ekleme; frontend yapılandırmasında yalnız kullanılmış varlıkları derle/yayınla.

### E. Hata dayanıklılığı ve tamamlama

18. Bozuk/eksik modül metadata'sı, sürüm/PHP/Laravel uyumsuzluğu, hash/ZIP doğrulama, traversal/symlink, Composer, migration, install seeder ve asset yayın hatalarında modül kapalı kalmalı; tekrar deneme veriyi bozmayıp sonucu kayıt altına almalı. Etkin bağımlı varken kapatma, kurulu bağımlı varken kaldırma kurallarını koru. Kaldırma migration rollback veya mağaza verisi silme olarak yorumlanmamalı.
19. Yetki matrisi modül ve eylem bazında (menü, ürün okuma/yazma, stok, sipariş görme/güncelleme, ödeme/iade, kategori/ayar gibi) tanımlansın. Yalnız aktif süper admin modül kurulumunu yönetir; müşteri admini yalnız atanmış işlem izinlerine erişir. Hassas ödeme verileri log ve bildirimlere konmaz.
20. Modül içi Feature testlerinde kurulum uyumluluğu ve başarısız adımlar, fiyat doğrulama, varyant/SKU benzersizliği, stok yarış koşulu, sipariş snapshot'ları, ziyaretçi görünürlüğü, izin ve etkinlik davranışı yer alır. Ödeme callback/idempotency ve iade testleri yalnız onaylanmış entegrasyon uygulanırsa eklenir. Mevcut uygulama araçlarının test/fake yeteneklerini kullan; testte gerçek ödeme/kargo servisine veya müşteri verisine bağlanma.
21. PHP dosyaları değiştiyse `vendor/bin/pint --dirty --format agent`; ilgili dar testler; modül panel asset'i varsa gerekli modül build'i ve kök `npm run build` çalıştır. Erişilebilir gerçek browser UI'sini ve daraltılmış görünümde sekmeleri/checkout'u gözle doğrula. Başarısızlıkları raporla ve düzeltene kadar yerel uygulama işini tamamlandı sayma.
22. Kod, ilgili test/build ve tema entegrasyonu rule/skill dokümanları tamamlanınca kaynakları farkları koruyarak `D:\localhost\ekAgentModules\Store` konumuna staged kopya olarak aktar; bunu push veya release olarak yorumlama. Değişen dosyalar, test/build sonuçları ve staged kaynak yolunu görev kaydında raporla. Release, GitHub push/asset, katalog güncellemesi ve yayın doğrulaması yalnız kullanıcı açıkça istediğinde `.ai/rules/moduller.md` ve `.ai/workflows/modul-yayinlama.md` adımlarına göre yürütülür. Test/build başarısızsa staging/release aşamasına geçme.

## Eşzamanlılık, tutarlılık ve hata kararları

Bu plan bir tek altyapı kalıbını dayatmaz. Uygulayıcı mevcut Laravel/DB yapısına uyan en küçük tasarımı seçer ve aşağıdaki sonuçları garanti eder:

- Aynı anda verilen son stok siparişlerinde en fazla bir işlem başarılı stok ayırır; diğer işlem stok yetersizliği alır.
- Sipariş oluşturma, satır snapshot'ları, stok ayırma ve ödeme başlangıcı kısmi/yarım görünür durumda bırakılmaz; transaction sınırı iş adımlarına uygun seçilir.
- Harici ödeme başarısızlığı/timeout'u, siparişin ve stok ayırmanın sonraki davranışını belirli duruma geçirir; güvenli retry veya insan tarafından müdahale edilebilir hata kaydı bulunur.
- Tekrarlanan webhook, retry edilen form POST'u veya çift tıklanan ödeme aynı sipariş/ödeme/stok etkisini iki kez üretmez.
- Para/ürün/sipariş geçmişinin sahibi açıkça belirlenir; ürün düzenleme sipariş geçmişini değiştirip tutarsızlaştırmaz.
- Admin ve frontend hata yanıtları müşteriye güvenli, operatöre teşhis edilebilir olur; sır, kart verisi veya kişisel veri loga/bildirime eklenmez.

## Tamamlanma ve ilerleme kapıları

1. **Planlama tamamlandı:** bu plan ve [özet](README.md) okunabilir; aday gerekçesi mevcut Products kuralıyla doğrulanır.
2. **Uygulama başlangıcı:** Products ilişkisi ve yerel MVP kararları cevaplanır; görev 5.6 TERA medium uygulayıcıya verilir. Ödeme/kargo gibi harici entegrasyonlar ayrı açık kullanıcı onayı almadıkça `waiting_approval` kalır ve uygulanmaz. Planlamadan uygulamaya otomatik geçilmez.
3. **Yerel modül tamamlandı:** kod, frontend, testler ve build doğrulanır; uygulama kurulum/başarısızlık kurallarını karşılar.
4. **Release/dağıtım:** yalnız açık kullanıcı talebiyle başlar ve `.ai/rules/moduller.md` içindeki tam yayın akışını izler. İki saatlik yeni keşif süresi modül işi tamamlanınca başlar; release talep edilmişse uygulama ve release/asset/katalog/push işleri de tamamlanmış olmalıdır. Modül işi sürerken keşif çalıştırılmaz.
