# Store (e-ticaret) modülü planı

## Yerel uygulama sonucu — 21 Ekim 2026

Güncel yerel kapsam uygulandı: bağımsız ürün/kategori, Products'tan kapak ve sıralı galeriyle isteğe bağlı aktarım, beş sekmeli ürün formu, ilk kayıt/edit varyantları, stok hareketleri, Settings ve queue etkinleştirmesi, yerel sepet/checkout/sipariş, manuel ödeme ve teslimat, güvenli sipariş erişimi, SEO ve frontend okuma servisi. Gerçek ödeme/taşıyıcı API'leri onay kaydındaki kapsam dışında kalır. Aşağıdaki ilk plan ve yeniden planlama metinleri karar geçmişidir.

Doğrulama: Store'un 21 feature testi; ortak modül yönetimi/queue ile birlikte 60 test; Settings/Products/Blog regresyonlarında 66 test geçti. Son checkout anahtarı değişikliği ilgili testle ayrıca doğrulandı. Pint, Blade derleme ve npm build başarılı. Tarayıcıda admin varyant kombinasyonu, 390px taşmasız görünüm, ikonlu liste işlemleri, Store Settings sekmesi ve public ürün → sepet → hesap gerektiren checkout kontrol edildi. Tam misafir sipariş POST'u feature testinde doğrulandı; gerçek ödeme, ZIP kurulumu veya çok süreçli stok stres testi yapılmış sayılmaz.

Store skill/rule ve StoreFrontendService sözleşmesi gerçek kodla güncellendi. Kaynak staging: `D:\localhost\ekAgentModules\Store`, 21:42 +03:00; tüm dosyaların SHA-256 eşitliği doğrulandı. Ortak ModuleManager, Settings görünürlüğü/alan doğrulaması ve SEO composer düzeltmeleri ana uygulamadadır; yalnız modül klasörünü kopyalamak bu çekirdek düzeltmelerin başka kurulumlara dağıtımı değildir. Rezervasyon süresi işi Laravel scheduler gerektirir. Release/push/katalog yayını yapılmadı.

## Amaç ve gerekçe

Katalogdaki `Products` modülü kurumsal ürün kataloğudur. `D:\localhost\ekAgentModules\Products\.ai\rules\tema-entegrasyonu.md` ve `D:\localhost\ekAgentModules\Products\skills\products-theme-integration\SKILL.md`, fiyat, stok, varyant, sepet, sipariş ve ödeme işlevlerini açıkça bu modülün dışında tutar. Satış yapan bir müşteri kurulumu, katalog göstermekten siparişi tamamlamaya kadar bu işlevleri birlikte kurabilmelidir. Bu yüzden tek bir Store adayı, ayrı cart/orders/payments alt modüllerinden daha anlaşılır bir kurulum birimi olur.

Bu doküman 21 Ekim 2026 tarihinde mevcut eksik uygulama ve kullanıcı geri bildirimleriyle yeniden planlandı. Aşağıdaki **Netleştirilmiş uygulama kapsamı** güncel uygulama sözleşmesidir; sonraki tarihsel önerilerle çelişirse bu bölüm önceliklidir. Bu çalışma plan revizyonudur; uygulama tamamlanmış sayılmaz. Keşif özeti [../README.md](../README.md) içindedir.

## Netleştirilmiş uygulama kapsamı

### Kararların durumu ve veri sahipliği

Kullanıcı tarafından kesinleştirilen kararlar: varsayılan TRY/TL, ileride başka para birimlerine genişleme, verginin fiyata dahil/hariç olmasının Store ayarından seçilmesi, misafir checkout'un ayarla açılması ve Store ayar sekmesinin yalnız modül etkin olduğunda görünmesi.

Yeniden planlama tercihi: Store kendi satılabilir ürünlerinin sahibidir ve Products kurulmadan çalışır. Ürün adı, açıklama, kapak, galeri, SEO, kategori, fiyat, stok ve varyantlar Store'da yönetilir. Products kurumsal katalog olarak kalır. Bu sahiplik, aşağıdaki isteğe bağlı aktarımı engellemez. Aktarım ve aşağıdaki ilk sürüm sınırları bu planın önerdiği kararlardır; geçmiş kullanıcı onayı olarak sunulmaz.

Products etkin ve adminin Products okuma izni varsa Store ürün ekleme ekranında **Katalogdan ürün aktar** seçeneği bulunur. Ad ve mevcut katalog kimliği üzerinden sayfalı arama yapılır; seçim önizlemesinde başlık, açıklama, kapak ve galeri gösterilir. Aktarım, Store taslağına tek seferlik içerik kopyasıdır. Fiyat, SKU, stok ve varyant kullanıcı tarafından Store'da doldurulur. Slug çakışması doğrulamayla çözülür; Products kategorisi Store kategorisine sessizce bağlanmaz, kullanıcı Store kategorisini seçer.

Aktarılan ürün mevcut ortak medya kayıtlarını kullanır; Store için ayrıca medya kullanım referansları oluşturulur. Kaynak ürün silinse, değiştirilse veya Products kapatılsa bile Store içeriği ve medya kullanım referansları korunur. Kaynak modül/ID yalnız köken bilgisi olarak saklanır; zorunlu foreign key ve otomatik senkronizasyon kurulmaz. Tekrarlı aktarımda mevcut Store kaydı gösterilip uyarılır; mevcut kayıt kendiliğinden üzerine yazılmaz. Products kurulu/etkin değilse arama kontrolü gösterilmez ve Store ürün oluşturma eksiksiz çalışır.

### İlk sürümün sınırı

İlk sürüm tek stok havuzu, fiziksel ürün, ürün/kategori yönetimi, varyant, medya, SEO, sepet, adres, yerel sipariş, manuel teslimat ve manuel ödeme kaydı içerir. Dijital dosya satışı, çoklu depo, kur dönüşümü, kupon/kampanya, müşteri iade portalı ve kısmi geri ödeme sonraki kapsamdır. Dijital ürün seçeneği çalışmayan bir alan olarak gösterilmez. Gerçek ödeme ve taşıyıcı API'leri mevcut onay kaydındaki durumunu korur.

Sağlayıcı bağlanmadan checkout sonucu **Sipariş alındı, ödeme bekleniyor** olur. Sipariş oluşması ödeme tahsil edildi anlamına gelmez. Yetkili admin, referans ve açıklamayla manuel ödeme alındı kaydı girebilir; değişiklik denetim geçmişinde tutulur. Kart formu veya çalışmayan ödeme yöntemi gösterilmez.

### Ürün formunun beş çalışan sekmesi

Ekleme ve düzenleme aynı alanları ve doğrulama kurallarını kullanır. Taslak kaydetme mümkündür; yayınlama, ürünü gerçekten satılabilir yapan alanları doğrular. Tablo satır işlemleri ortak admin ikonlarını kullanır.

| Sekme | Davranış ve kabul şartı |
|---|---|
| Ürün bilgileri | Ad, kategori, kısa açıklama, temizlenen zengin açıklama, slug, kapak ve sıralı galeri. Ortak medya seçimi/yükleme/silme/sıralama ve izinleri çalışır; başka kayıtta kullanılan medya fiziksel olarak silinmez. Slug ortak `data-slug-title` / `data-slug-field` bağlantısıyla addan üretilir, elle düzenlenebilir ve sunucuda benzersizliği doğrulanır. |
| Fiyat ve stok | Kullanıcı TL cinsinden ondalık tutar girer; kuruş girmesi beklenmez. Satış fiyatı, isteğe bağlı karşılaştırma fiyatı, SKU, stok takibi, düşük stok eşiği ve stokta yokken satış ayarı bulunur. Karşılaştırma fiyatı varsa satış fiyatından büyük olmalı. Manuel stok düzeltmesi gerekçe ve hareket kaydı üretir. |
| Varyantlar | Varyant kullan anahtarı, seçenek grupları/değerleri, kombinasyon üretme ve satır bazında SKU, fiyat, stok, görsel, aktiflik ve sıralama. İlk ürün oluşturulurken de varyantlar hazırlanıp ürünle birlikte kaydedilebilir. Kayıt sonrasında aynı gerçek editör açılır; statik “kaydedince kullanılabilir” metni kabul edilmez. |
| Teslimat | Fiziksel üründe ağırlık (gram), en/boy/yükseklik (cm), hazırlık süresi ve teslimat açıklaması; yalnız kullanılan alanlar zorunludur. Ürün bilgileri teslimat hesaplama ve sipariş snapshot'ına bağlanır. Harici kargo teklifi varmış gibi davranılmaz. |
| SEO | Ortak Blog SEO örüntüsü üzerinden meta başlık, meta açıklama ve paylaşım görseli. Boş başlık ürün adına, açıklama kısa açıklamaya, paylaşım görseli kapağa düşer. Public detay gerçek metadata üretir; slug'dan tek kanonik ürün adresi kullanılır. |

Sağ kartta yayın durumu, sıralama, kapak önizlemesi ve kaydet/vazgeç bulunur. Sekmeler klavyeyle ve mobilde erişilir; hata olan sekme açılır, girilen alanlar/galeri sırası/varyant satırları korunur. Sekme tercihi ürün/form kapsamında tutulur. Placeholder sekme tamamlanmış özellik sayılmaz.

### Varyant ve stok kuralları

Varyantsız ürünün fiyat/stok kaynağı ürün kaydıdır. Varyantlı ürünün satılabilir birimi varyanttır; üst ürün stoğu ikinci kez azaltılmaz. Varyant fiyatı boşsa ürün temel fiyatını kullanır. Varyant ve basit ürün SKU'ları satılabilir birimler arasında benzersizdir. Aynı ürün altında aynı seçenek kombinasyonu iki kez oluşturulamaz; seçenek ve varyant ID'leri sunucuda ürün sahipliğiyle doğrulanır.

Kombinasyon üretme mevcut satırları silip yeniden yaratmaz; aynı kombinasyonun ID, SKU, stok ve fiyatını korur. Yayımlanan varyantlı üründe en az bir aktif geçerli kombinasyon gerekir. Ziyaretçi gerekli seçenekleri seçmeden sepete ekleyemez; başka ürüne ait veya kapatılmış varyant reddedilir. Kombinasyon değiştiğinde fiyat, görsel ve stok durumu güncellenir.

Varyant modunu değiştirmek stok miktarını otomatik taşımaz. Admin yeni modun fiyat/stoklarını açıkça doğrular; eski satırlar ve sipariş snapshot'ları korunur. Kullanılmış varyantlar arşivlenir. Stok düzeltme, sipariş ayırma ve iptal iadesi ayrı hareketlerdir. Stokta yokken satış kapalıysa stok negatif olamaz; açık olduğunda negatif bakiyeyi destekleyen saklama ve doğrulama kullanılır.

### Para ve vergi sözleşmesi

Para modeli tamsayı en küçük birim + ISO para kodudur. TRY iki basamaklıdır; yeni para birimi eklendiğinde hassasiyeti para birimi tanımından gelir, her para biriminin kuruş olduğu varsayılmaz. İlk sürümde etkin para birimi TRY'dir. Bir sepet ve sipariş tek para birimi taşır; kur dönüşümü yapılmaz. Ayarı değiştirmek mevcut fiyatları dönüştürmez veya geçmiş siparişi yeniden etiketlemez; ileride para birimi ekleme mevcut fiyatlar için açık geçiş gerektirir.

Vergi oranı ilk sürümde mağaza genelinde tek oran, varsayılan %0'dır. Fiyatlar vergi dahil varsayılanıyla gelir; mevcut kullanıcı değeri korunur. Dahil modunda satır vergisi brüt tutardan ayrıştırılır, hariç modunda net tutara eklenir. Ondalık/float hesap yerine kesin oran ve tamsayı hesap kullanılır; vergi satır toplamı üzerinden en yakın en küçük birime yarım yukarı yuvarlanır. Sipariş toplamı yuvarlanmış satırların toplamıdır. İlk sürüm teslimat ücreti de aynı oran ve dahil/hariç kuralını izler. Vergi oranı, mod ve hesaplanan tutarlar siparişte sabitlenir; ayar değişikliği geçmişi etkilemez.

### Store ayar sekmesi ve yaşam döngüsü

Mevcut Settings tab yaklaşımına **Mağaza** sekmesi eklenir: para birimi, vergi dahil/hariç, vergi oranı, misafir checkout, sabit teslimat ücreti, ücretsiz teslimat eşiği (boşsa kapalı), mağazadan teslim alma ve manuel ödeme açıklaması. Genel ayar anahtarları korunur: `store.currency`, `store.tax_included`, `store.tax_rate`, `store.guest_checkout_enabled`. Yeni alanlar aynı bölümde kararlı anahtarlarla tanımlanır; desteklenmeyen Settings alan türleri uydurulmaz.

Misafir checkout varsayılan kapalıdır. Kapalıysa ziyaretçi web hesabıyla girişe yönlenir; giriş sonrası sepet korunur. Users kurulu/etkin değilken misafir checkout da kapalıysa checkout kullanılamaz; admine çözüm gösterilir, hesap zorunluluğu sessizce kaldırılmaz. Users opsiyoneldir; modül yokken Store migration veya provider'ı Users tablosu/sınıfına zorunlu bağımlı olamaz.

Paket kurulumu, mevcut kaynak modülün ilk etkinleştirilmesi, yeniden etkinleştirme ve başarısız işin tekrarı aynı zorunlu tanımları oluşturabilmelidir. İşlem idempotent olur; kullanıcı ayarlarını sıfırlamaz. Tanım oluşturma view render'ına bırakılmaz. Etkinleştirme ortak queue/progress akışından yürür ve gerekli hazırlık başarıyla tamamlanınca modül açılır. Ayar sekmesi etkinlik ve yetkiye göre görünür; doğrudan okuma/güncelleme aynı kontrolleri uygular. Kapatma ayarları ve siparişleri silmez.

### Sepet, sipariş ve teslimat

Public akış: sayfalı ürün/kategori listesi → slug detayı ve varyant seçimi → adet güncelleme/silme içeren sepet → giriş veya izinli misafir → iletişim/teslimat/fatura adresi → sipariş özeti → yerel sipariş sonucu. Her adım aktif ürün/kategori/varyant, güncel fiyat, para birimi, stok ve ayarları sunucuda doğrular. Fiyat değişmişse yeni toplam gösterilip yeniden onay alınır. İstemciden fiyat/vergi/toplam kabul edilmez.

Sabit teslimat ücreti veya açık mağazadan teslim seçeneği uygulanır. Ücretsiz teslimat eşiği, ürünlerin vergi dahil toplamına göre değerlendirilir; teslim alma ücreti sıfırdır. İletişim ve adresler sunucuda doğrulanır; teslim almada gereksiz teslimat adresi zorunlu tutulmaz. Manuel takip numarası ve gönderim tarihi siparişe kaydedilebilir.

Sipariş durumları bekliyor → onaylandı → hazırlanıyor → gönderildi → tamamlandı; teslim almada gönderildi yerine teslimata hazır kullanılır. Gönderim öncesi iptal mümkündür. Ödeme durumu ayrı tutulur: bekliyor, manuel ödendi, manuel iade kaydı. Her geçiş işlem izni ve önceki durumla doğrulanır. Ödenmiş sipariş iptali otomatik para iadesi anlamına gelmez; iade gereksinimi görünür kalır. Kısmi gönderim/iade ilk sürüm dışındadır.

Sipariş oluşturma ürün/varyant kilitleri, satır snapshot'ları ve stok ayırmayı tek transaction'da tamamlar. Aynı checkout anahtarıyla çift POST tek sipariş üretir; farklı sepet/veriyle anahtar yeniden kullanılırsa reddedilir. İptal veya süre dolması ayrılan stoğu yalnız bir kez geri verir. Ödenmemiş sipariş için plan varsayılanı 24 saat rezervasyondur; süre sonunda ortak scheduler/queue işi iptal eder. Ödeme ve süre dolma yarışı aynı sipariş üzerinde kilitle çözülür.

Sipariş satırları ad, SKU, seçenekler, birim fiyat, miktar, vergi, toplam ve para birimini; sipariş adresleri ve teslimat şartlarını kopya olarak tutar. Ürün/kategori/varyant veya müşteri hesabı değişimi geçmişi bozmaz. Misafir sonuç sayfası tahmin edilemeyen erişim anahtarıyla, hesap içi sipariş yalnız sahibine açılır; sıra numarası kişisel verilere erişim sağlamaz.

### Mevcut koddan doğrulanan bulgular

- `Modules/Products/resources/views/partials/form.blade.php`: ortak slug bağları, rich text, kapak, çoklu medya seçimi ve galeri sırası zaten var.
- `Modules/Products/app/Services/ProductService.php`: ortak medya seçim yetkisi, kullanım referansı, içerik temizleme ve ek alan kayıt akışları var; bunların Store'a bağlantıları açıkça kurulmalı.
- `Modules/Blog/resources/views/contents/partials/form.blade.php`: SEO alanlarının mevcut ortak form örneği. Public Blog sayfaları `x-seo-meta` tüketiyor.
- `resources/js/admin/slug.js`: otomatik üretim ve elle düzenleme davranışının ortak sahibi.
- Mevcut Store ürün formunda yalnız üç sekme var; varyant bölümü statik metin. Medya, teslimat ve SEO akışları bu formda bulunmuyor. Bunlar planlanmış olmakla tamamlanmış olmaz.

### Uygulama sırası ve teslim ölçütleri

1. Mevcut Store kodu, migration ve verileri incele; mevcut veriyi koruyan ek migration planla. Çalıştırılmış migration'ı yeniden yazarak veri sıfırlama. Products örneğini uçtan uca, Blog'u yalnız eksik SEO/sekme davranışı için karşılaştır; bulunan yeniden kullanım bağlantılarını görev kaydına yaz.
2. Ürün/kategori, medya, slug, SEO ve isteğe bağlı katalog aktarımını tamamla. Store yalnız kurulu olduğunda da ürün oluşturma/düzenleme/yayınlama çalışmalı.
3. İlk ekleme ve sonraki düzenlemede varyant editörünü, kombinasyon korumayı ve stok hareketlerini tamamla.
4. Settings tanımları ve gerçek queue etkinleştirme yaşam döngüsünü tamamla; tanımlar testte elle seed edilerek eksik etkinleştirme gizlenmemeli.
5. Teslimat, sepet, misafir/hesap dalları, sipariş snapshot, tekrar istek ve rezervasyon süresi akışlarını tamamla.
6. Admin sipariş listesi/detayı, izinli durum/manuel ödeme/stok işlemleri, kullanıcıya güvenli sipariş erişimi ve public ekranları tamamla.
7. Anlamlı regresyon kontrolleri: Products yok/kapalı/izinsiz ve aktarım sonrası kaynak silme; medya kullanımı; slug elle değiştirme; varyant oluştur/güncelle/arşivle ve yabancı varyant reddi; dahil/hariç vergi ve yuvarlama; misafir açık/kapalı ve Users yokluğu; çift checkout/son stok yarışı; iptalin tekrarı/süre dolması; gerçek etkinleştirmeden Settings görünürlüğü ve değer koruması; sipariş erişim yetkisi. Yalnız gereken dar testleri çalıştır, PHP değişirse Pint ve asset değişirse build uygula.
8. Beş sekmeyi create/edit, doğrulama hatası ve dar ekranda; public varyant seçimi/sepet/checkout'u gerçek tarayıcıda kontrol et. Her özelliği uygulandı/doğrulandı/eksik olarak raporla. Placeholder veya başarısız temel akış varken tamamlandı deme. Başarılı uygulama sonrası proje staging sözleşmesini uygula; release ayrı kullanıcı talebi gerektirir.

Bu revizyonun teslimi plan dosyasıdır. Mevcut uygulama işinin held kilidini veya tamamlanma durumunu kendiliğinden değiştirmez. Uygulama aşamasında plan tercihleri kullanıcıya geçmişte onaylanmış kararlar gibi sunulmaz; değişen kapsam gerekçesi görev kaydına işlenir.

## İlk keşif planı ve tarihsel kapsam önerisi

Aşağıdaki ilk plan geçmişi korumak için tutulur. Güncel karar, kapsam ve uygulama sırası için yukarıdaki Netleştirilmiş uygulama kapsamı esas alınır; buradaki açık sorular yeniden karar bekleyen iş olarak yorumlanmaz.

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
- Verginin fiyata dahil/hariç olması ve misafir checkout Store ayarından seçilir. Store ayar sekmesi yalnız Store etkinse görünür. Ayrıntılı davranış yukarıdaki güncel sözleşmededir.

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
