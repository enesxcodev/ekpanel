# Menü yönetimi — kullanıcı kararına sunulan mimari taslak

**Tarih:** 17 Ekim 2026  
**Lead model:** GPT-6.1 Sol  
**Araştırmacılar:** Örnek siteler ve WordPress için iki bağımsız GPT-6 Luna ajanı  
**Durum:** Kodsuz öneri. Nihai karar kullanıcıya aittir; bu rapor uygulama onayı değildir.

## Önerinin özü

Bir **menü**, sıralanmış ve çok seviyeli **menü öğeleri** içersin. Menü öğesinin görünen etiketi, bağlandığı hedef ve varsa alt öğelerinin nereden geldiği ayrı yönetilsin. Kullanıcı bir sayfayı, kategoriyi veya hizmeti tek tek seçebilsin; ayrıca URL, Laravel route adı veya anchor doğrudan ekleyebilsin. Bir kategoriyi seçmek, bütün alt içeriklerin otomatik menüye eklenmesi anlamına gelmesin. Bunun için açık bir “Alt içerikleri otomatik listele” seçeneği bulunsun.

Başlangıç önerisi manuel seçimdir. Dinamik kategori listesi gerçekten gereken dallarda açılabilir. Böylece az sayfalı işletme sitesi de geniş kategori ağacı da aynı kavramlarla yönetilebilir; her kurulumda mega menü zorunlu hale gelmez.

## Araştırmaların ve projenin karşılaştırılması

Bu taslak, aynı klasördeki `ornek-site-incelemesi.md`, `wordpress-menu-arastirmasi.md` ve `proje-icerik-kaynaklari.md` raporları tamamlandıktan sonra hazırlanmıştır.

| Girdi | Doğrulanan bulgu | Taslak için çıkarım / öneri |
|---|---|---|
| [Trendyol](https://www.trendyol.com/) | Açık kategori paneli, sütun başlıkları ve alt bağlantılar görülüyor | Hiyerarşi verisi, tema tarafından sütunlu mega menü olarak sunulabilir; bu görünüm veri modelinin zorunlu biçimi olmasın |
| [Soner Hoca](https://sonerhoca.net/) | Yatay başlıklar, dikey alt menü ve bazı dallarda üçüncü seviye var | İçerik türü ve konu bazlı farklı gruplamalar kurulabilsin; menü ağacı içerik kategorilerinin birebir kopyası olmasın |
| [Turkcell](https://www.turkcell.com.tr/) | Birbirine bağlı açık paneller ve “Tüm …” bağlantıları var | Seçilmiş bağlantılarla tümünü gör bağlantısı aynı dalda bulunabilsin |
| [WordPress klasik Menüler](https://wordpress.org/documentation/article/appearance-menus-screen/) | İçerik/özel bağlantı seçimi, bağımsız etiket, sıralama ve alt menü düzenleme var | İçerik seçicisi ve ayrı düzenlenebilir menü ağacı anlaşılır bir admin yaklaşımıdır |
| [WordPress Navigation](https://wordpress.org/documentation/article/navigation-block/) | Otomatik Page List ile tekil düzenlenebilir sayfa linkleri farklı davranır | Manuel liste ve dinamik kaynak kullanıcıya açıkça farklı seçenekler olarak sunulsun |
| Repo Pages | Hiyerarşik kategori, tekil sayfa, yayın durumu, sıra ve public sayfa hedefi var | Sayfa seçimi mevcut yapıya doğrudan uyabilir; kategori linki için arşiv hazır kabul edilmesin |
| Repo Services | Kategori–hizmet ilişkisi var; kategori ağacı/public hedef yok | Tekil/kategori kaynağı kavramı desteklensin; bağlantı hazır olana kadar kullanılamaz durumu açık gösterilsin |
| Repo ürünler | İlgili modül/model bulunamadı | Gelecek kaynak olarak kapsamda kalsın; bugün hazır seçenekmiş gibi sunulmasın |

Örnek sitelerde hover/tıklama tetikleyicileri, mobil veya klavye davranışı test edilmedi. Ekran görüntüleri açık panelleri gösterir. Bu sitelerin menülerinin otomatik mi elle mi oluşturulduğu bilinmiyor; yukarıdaki tasarım önerileri buna dair bir iddia taşımaz. WordPress'te varsayılan Pages kategorileri de yoktur; projenin `PageCategory` modeli WordPress'e birebir eşitlenmemelidir. [WordPress sayfalar](https://wordpress.org/documentation/article/create-pages/)

## Menü, öğe ve hedef kavramları

**Menü:** Yönetim adı, sabit kimlik/anahtar, etkinlik durumu ve tema konumuyla ilişkilendirilen bir navigasyon ağacı. “Ana menü” ve “Alt alan menüsü” ayrı menüler olabilir. Menü oluşturma ve temada bir konuma atama ayrı adımlardır. Tema konumlarının bir menüyü paylaşabilmesi bir kullanıcı kararıdır.

**Menü öğesi:** Üst öğe, kardeşler arasındaki sıra, görünen etiket, etkinlik durumu, hedef bilgisi ve çocuk üretim tercihini taşır. Aynı içerik farklı dallara eklenebilir. Sayfa kategorisinin yerini değiştirmek, manuel menü öğesinin ebeveynini kendiliğinden değiştirmesin.

**İçerik referansı:** Sayfa/hizmet/ürün gibi gerçek kaydın türü ve ID'si. İçeriğin başlığı veya URL'si değiştiğinde güncel kayda ulaşmayı sağlar. Bu ilişki, kullanıcıya bütün hedefleri URL olarak yazdırmaktan daha güvenilir bir seçici sunar. Hangi teknik ilişki yöntemiyle saklanacağı bu kodsuz taslağın kararı değildir.

**Hedef:** Tıklanınca nereye gidileceği. Her öğede tek etkin hedef türü bulunsun; başka türe geçildiğinde önceki türün alanları etkin kalmasın.

| Hedef türü | Kullanıcı girdisi | Önerilen davranış |
|---|---|---|
| İçerik kaydı | Kaynak türü + gerçek kayıt ID'si | Güncel yayın/etkinlik ve modül durumunu kontrol ederek public hedefi çöz |
| Dış/özel URL | Adres + etiket | `https://…` dış URL ve açıkça desteklenen site içi yol eklenebilsin; `mailto:`/`tel:` desteği ayrıca kararlaştırılsın |
| Laravel route adı | Route adı + gereken parametreler; isteğe bağlı sorgu değerleri | URL yerine adı sakla; route ve parametrelerle public adres üret; admin/işlem route'larını ziyaretçi seçicisine alma |
| Sayfa içi anchor | Bölüm kimliği; aynı sayfa veya seçilmiş sayfa/route tabanı | `#iletisim` ile başka sayfadaki `/…#iletisim` ayrımı formda açık olsun; bölümün gerçekten varlığı ayrıca değerlendirilsin |
| Bağlantısız grup başlığı — aday | Etiket | Kategori arşivi olmayan ve yalnız alt menü açan başlıklar için değerlendir; sahte `#` URL zorunlu olmasın |

Son satır ek bir öneridir ve onay gerektiren ürün seçimidir. “Alt menüyü aç” eylemi ile gerçek sayfaya gitme eylemi temada ayrı erişilebilir kontrollerle sunulabilir. URL güvenliği için izin verilen şemalar belirlensin; `javascript:` gibi çalıştırılabilir hedefler kabul edilmesin.

Route adı serbestçe elle yazılabilsin; önerilen seçicide yalnız ziyaretçiye açık GET hedefleri sunulsun. Route'un var olması tek başına içeriğin var olduğunu kanıtlamaz: örneğin `pages.show` için verilen path'in yayımlanmış sayfayı bulması gerekir. Parametre eksikliği veya auth gerektiren adres kullanımı admin içinde anlaşılır hata versin. Route değişikliği menüye yeniden girilmeden doğru adrese yansısın; route adı kaldırılırsa öğe geçersizleşsin.

## Kaynakların seçimi ve otomatik listeleme

**Manuel seçim:** Kategori panelinden bir veya birkaç sayfa seçip “Menüye ekle” denir. Eklenen öğeler bağımsız menü kayıtlarıdır. Sonradan o kategoriye eklenen sayfa menüye kendiliğinden girmez. Aynı sayfayı başka bir dalda göstermek mümkündür; aynı dalda istemeden yinelenme için uyarı verilebilir.

**Kategori öğesi:** Kategori adı başlık veya kaynağa referans olabilir. Kategoriye bağlantı verilmesi ve kategorinin çocuklarının menüde gösterilmesi iki ayrı tercihtir. Pages ve Services'te kategori arşivi bugün bulunmadığı için otomatik kategori URL'si uydurulmasın.

**Dinamik liste:** Kullanıcı bir kategori seçip kapsamı belirler: yalnız o kategorinin tekil içerikleri, yalnız alt kategoriler veya alt kategorilerle içerikler. Pages için doğrudan çocuklar ile tüm alt ağaç ayrımı ve derinlik açık olmalı. Services şu anda yalnız doğrudan hizmetleri sağlayabilir; olmayan hizmet alt kategori ağacı teklif edilmesin.

| Dinamik tercih | Öneri |
|---|---|
| Yayın/etkinlik | Yalnız `published` sayfalar ve `is_active` hizmetler; geçerli public hedef şartı |
| Sıra | Pages'te mevcut sıra alanlarını izle; Services'te seçilmiş başlık/ID gibi belirli bir sıra kullan |
| Yeni içerik | Önizlemede “Yeni yayımlanan uygun içerikler otomatik eklenir” bilgisini göster |
| Çıkarılacak kayıtlar | Seçili kayıtları hariç bırakma; gerekirse öğe sayısı sınırı |
| Boş sonuç | Öneri: boş grup ziyaretçiden gizlensin, adminde neden belirtilecek şekilde kalsın |
| Manuel ve dinamik karışım | Öneri: ilk sürümde aynı dalın çocukları tek bir yöntemle yönetilsin; “Tümünü gör” bağlantısı açık bir ek seçenek olabilir |
| Tekil düzenleme | Dinamik çocuklar önizlemede türetilmiş olarak gösterilsin; tek tek sürüklenebilir kalıcı öğe sanılmasın |
| Sabitleme | İstenirse “Mevcut listeyi manuel öğelere dönüştür” ayrı eylemiyle yeni içeriklerin otomatik eklenmesi sonlandırılsın |

Dinamik liste kalıcı menü kayıtlarına her istek için kopyalanmasın; kaynağın kurallarıyla türetilsin. Yönetici kaynak içeriği değiştirdiğinde sonuç değişeceği açık olmalıdır. İleride önbellekleme yapılırsa yayın, kategori ilişkisi, sıra, slug ve modül durumundaki değişiklikler de yenilemeye dahil edilmelidir; burada herhangi bir cache mekanizması uygulanmamıştır.

## Gerçek projeyle uyum

1. **Pages:** Kategoriler ağaç görünümünde, sayfalar ilgili kategori altında ve ayrıca kategorisiz grup içinde seçilebilsin. Sayfa ID'si üzerinden mevcut `pages.show` davranışına uygun güncel path çözülsün. Kategori arşivi ayrı bir kapsam kararıdır.
2. **Services:** Tekil hizmet ve kategori seçicisi gerçek kayıtları gösterebilir. Ancak public detay/arşiv hedefi yoktur; bunlar hazır link olarak yayımlanmasın. Geçici olarak açıkça seçilen public URL/route ile ayrı bir öğe eklenebilir; otomatik içerik hedefiyle aynı şey sayılmamalı.
3. **Ürünler:** Ürün gereksinimi korunur. Ürün modülü eklendiğinde gerçek tekil/kategori modelleri, etkinlik alanı, sıra ve public adres davranışı doğrulanır. Menü modülü bulunmayan ürün koduna zorunlu bağımlı hale gelmesin.
4. **Anchor:** Mevcut Pages içerik temizleyicisi HTML `id` niteliklerini korumuyor ve gösterim şablonu bölüm ID'si üretmiyor. Bu nedenle serbest sayfa içeriğinde anchor bugün garanti edilemez. Tema tarafından tanımlanmış bölüm ID'leri veya sonradan onaylanacak içerik bölümü desteği gereklidir.
5. **Proje sınırları:** Menü özelliği tekrarlanan kayıt ve hiyerarşi içerdiğinden bağımsız modül adayıdır. İçerik modelleri ayrı kalmalı; modüler kurulum/etkinlik koşulları ve sade controller–service yaklaşımı izlenmeli. Admin yönetim izinleri ile public menü görünürlüğü farklı amaçlar taşır; public öğe görünürlüğü yönetici izinlerine bağlanmamalıdır. Yeni izin adları uygulama aşamasında kararlaştırılır.

## Alan önerileri

Bu liste veri tabanı şeması veya migration değildir; kullanıcı ve geliştiricinin üzerinde konuşabileceği kavramsal alanlardır.

| Kavram | Gerekli/adaya göre alanlar |
|---|---|
| Menü | Kimlik, yönetim adı, sabit anahtar, etkinlik; konum ataması ayrı ilişki olabilir |
| Menü öğesi | Kimlik, menü, üst öğe, kardeş sırası, etkinlik, hedef türü, etiket |
| Etiket davranışı | İçerik adını izle veya özel etiket kullan; kaynak adı değişince beklenen sonuç açık olsun |
| İçerik hedefi | Kaynak modülü/türü, kayıt ID'si |
| Route hedefi | Route adı, parametreler; sorgu değerleri isteğe bağlı |
| URL hedefi | Adres; yeni sekme tercihi |
| Anchor hedefi | Bölüm kimliği ve isteğe bağlı taban hedef |
| Dinamik çocuklar | Kaynak kategori, kapsam, derinlik, sıra, limit, hariç bırakılanlar; açık etkinleştirme |
| Yönetim geri bildirimi | Çözümlenmiş hedef önizlemesi, geçerli/geçersiz durumu ve gerekçesi; türetilmiş durum esas veriyle karıştırılmasın |

İkon, resim, rozet, sütun sayısı ve özel CSS sınıfı ilk sürüm için zorunlu alan önerisi değildir. Mega menüye ihtiyaç kararından sonra, tema sunumu gerektirirse belirlenebilir.

## Admin akışı önerisi

1. Mevcut Tabler düzeni ve liste/ayrı ekleme–düzenleme sayfası akışı kullanılır. Menü listesinde ekleme, düzenleme ve durum işlemleri yer alır.
2. Menü oluşturulurken adı/anahtarı belirlenir; tema konumu hazırsa atama yapılır. Bir menünün konuma atanmadığı adminde belirtilir.
3. Menü düzenleme sayfasında kaynak seçicisi ve öğe ağacı bulunur. Kaynak sekmeleri: Sayfalar, Sayfa kategorileri, uygun modüllerde Hizmetler/Ürünler, Özel URL, Route, Anchor. Yalnız kurulu/etkin kaynaklar kullanılabilir.
4. İçerik araması ve kategori filtresiyle bir veya çok kayıt seçilip eklenir. Kategori eklenirken “Kategori bağlantısı/grup” ile “Alt içerikleri otomatik listele” ayrı gösterilir; eksik public hedef durumu burada açıklanır.
5. Ağaçta sürükle-bırak ve klavyeyle kullanılabilir taşı/üst öğe/sıra kontrolleri sunulur. Öğenin kendisine veya altına ebeveyn atanamaz; başka menüdeki öğe ebeveyn seçilemez.
6. Öğeyi açınca hedef türüne uygun alanlar ve etiket tercihi görünür. Dinamik kaynakta kapsam ve örnek çocuklar gösterilir. Otomatik üretilenler manuel öğelerden görsel olarak ayırt edilir.
7. Kaydetme öncesi değişiklik özeti ve çözümlenmiş ağaç önizlemesi sunulur; geçersiz yeni hedefler engellenir. Kaydetme bildirimi ve listeye dönüş mevcut admin kuralını izler. Ayrı taslak/yayımla akışı istenirse bu yerleşik akıştan ayrılan ek ürün kararı olarak onaylanmalıdır.

Çok seviyeli veri ağacı desteklenebilir; fakat temanın gösterebildiği seviye ayrıca tanımlanmalıdır. İlk kullanımda üç seviye çoğu referansı karşılayacak adaydır; bu bir zorunlu veri sınırı değildir. Tema desteğini aşan dal sessizce kaybolmasın: kaydetme/önizlemede açık uyarı ve kararlaştırılmış davranış bulunsun. Mobilde tıklama, klavye/focus ve kapatma davranışı uygulama tasarımında ayrıca ele alınmalıdır.

## Geçersiz hedefler ve değişiklikler

Öneri, mevcut öğeyi otomatik silmeden admin uyarısıyla saklamak ve public çıktıda geçersiz bağlantıyı gizlemektir. Kaynak modülün devre dışı olması, kaydın silinmesi, sayfanın taslağa çekilmesi, hizmetin pasifleşmesi, route'un kaldırılması ve parametre eksiği farklı gerekçelerle gösterilsin. Üst öğe geçersizleştiğinde geçerli çocukların yükseltilmesi mi, bağlantısız grup halinde tutulması mı, dalın gizlenmesi mi gerektiği kullanıcı kararıdır; önerilen başlangıç dalı gizlemek ve adminde açık etki önizlemesi sunmaktır.

Kategori/sayfa adının veya slug'ın değişmesi referansı koparmasın; içerik ID'sinden güncel hedef çözülsün. Menüde özel etiket seçilmişse o etiket korunsun; “kaynak adını izle” seçilmişse isim güncellensin. Dış URL'nin erişilebilirliğini her sayfa isteğinde ağdan sınamak önerilmez; biçim kontrolü erişilebilirlik garantisi değildir. Anchor için bölüm listesi yoksa doğrulamanın sınırlı olduğu gösterilsin; başarı iddiası üretilmesin.

## Kullanıcının yanıtlaması gereken kararlar

| Karar sorusu | Önerilen başlangıç |
|---|---|
| İlk sürümde hangi menü konumları gerekli: ana alan, alt alan, mobil? Aynı menü paylaşılabilir mi? | Ana alan ve alt alan; mobil aynı menüyü tema biçiminde kullansın, ihtiyaç varsa ayrı atansın |
| Arşiv sayfası olmayan kategori tıklansın mı, yalnız alt menü başlığı mı olsun? | Bağlantısız grup/dinamik kaynak; arşiv isteniyorsa ayrı kapsam olarak tanımlansın |
| Manuel seçim varsayılan mı; dinamik liste ilk sürüme dahil mi? | Manuel varsayılan, dinamik yalnız açıkça seçilen kategori dallarında |
| Dinamik Pages listesi doğrudan sayfaları mı, alt kategorileri mi, tüm ağacı mı kapsasın? | Varsayılan doğrudan yayımlanmış sayfalar; geniş kapsam açık tercihle |
| Manuel ve otomatik çocuklar aynı dalda karışabilsin mi? | İlk sürümde tek yöntem; gerekirse ayrıca “Tümünü gör” hedefi |
| Hizmet detay/kategori arşivi ve ürün modülü bu işten önce mi, sonra mı ele alınacak? | Menü raporunda gereksinim olarak koru; hazır olmayan kaynak için otomatik link açma |
| Anchor yalnız tema bölümlerinde mi, Pages metninde de mi gerekli? | Önce tema bölümleri; Pages için ID desteği ayrı gereksinim |
| Veri ve tema için kaç seviye gerekli? Sınırı aşınca ne gösterilsin? | Veri çok seviyeli; tema için başlangıçta üç seviye önerisi kullanıcı tarafından değerlendirilsin |
| Geçersiz üst hedefte geçerli çocuklara ne olsun? | Dalı publicte gizle, adminde uyarı ve etkiyi göster; alternatif açıkça seçilsin |
| Kaydetme hemen yayına mı yansısın, taslak/yayımla gerekli mi? | Mevcut CRUD akışına uygun kaydetme; ayrı yayımla yalnız ihtiyaç varsa |
| İçerik adı değişince menü etiketi güncellensin mi? | Kaynak adını izle varsayılanı ve isteğe bağlı özel etiket |

Bu sorular yanıtlanmadan kategori arşivi, hizmet/ürün public adresleri, dinamik karışım ve tema seviye sınırı kesinleşmiş kabul edilmemelidir. Sonraki uygulama kapsamı kullanıcı kararlarıyla belirlenir.
