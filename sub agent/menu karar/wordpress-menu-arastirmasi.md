# WordPress menü araştırması

**Kapsam:** WordPress.org kullanıcı belgeleri ve developer.wordpress.org resmi referansları. Bu rapor WordPress davranışlarını özetler; Laravel uygulaması için nihai mimari kararı içermez.

## Kaynaklarda doğrulanan bulgular

### Klasik menü düzenleme

- Klasik Görünüm → Menüler ekranında ayrı menüler oluşturulabilir. Sayfa, yazı/kategori ve özel bağlantı gibi öğeler sol panellerden seçilip menüye eklenir. Sayfa öğeleri yayımlanmış sayfalar listesinden tek tek seçilebilir. Ekran seçenekleri hangi içerik panellerinin görüneceğini belirleyebilir; bazı paneller varsayılan olarak gizlidir. [Appearance Menus Screen](https://wordpress.org/documentation/article/appearance-menus-screen/)
- Özel bağlantı öğesi, kullanıcının girdiği URL'yi hedefler. Menü etiketi hedef sayfanın başlığından ayrı düzenlenebilir. Yeni sekmede açma ve açıklama gibi seçenekler de bulunur. [Appearance Menus Screen](https://wordpress.org/documentation/article/appearance-menus-screen/)
- Sıralama ve hiyerarşi sürükle-bırak ile düzenlenir: öğeyi sağa kaydırmak onu üstündeki öğenin alt öğesi yapar. Belge birden fazla alt seviye desteklediğini belirtir. Tema, oluşturulan menünün gösterileceği konumu desteklemelidir. [Appearance Menus Screen](https://wordpress.org/documentation/article/appearance-menus-screen/)
- Bu klasik ekran WordPress 5.9'a kadar tüm sitelerde varsayılan olarak sunuluyordu. WordPress 6.0 ve sonrasında blok temalarda navigasyon Navigation bloğunda yönetilir; klasik veya hibrit temalarda ya da ilgili eklentilerle Menüler ekranı hâlâ bulunabilir. [Appearance Menus Screen](https://wordpress.org/documentation/article/appearance-menus-screen/)

### Modern Site Editor ve Navigation bloğu

- Site Editor üzerinden menüleri listeleme ve düzenleme olanağı WordPress 6.3 ile tanıtılmıştır ve yalnızca etkin bir blok temada kullanılabilir. Menü oluşturmak tek başına onu sitede göstermez; tema şablon parçasına (ör. Header) Navigation bloğu eklenip ilgili menü atanmalıdır. [Site Editor navigation](https://wordpress.org/documentation/article/site-editor-navigation/)
- Navigation bloğunda başlangıçtaki Page List bloğu yayımlanmış sayfaları otomatik listeler ve yeni yayımlanan sayfaları ekler. Kullanıcı bu listeyi düzenlenebilir tekil Page Link bloklarına dönüştürürse yeni sayfalar otomatik eklenmez. Bu, otomatik sayfa listesi ile sabit/elle düzenlenen menü arasında belgelenmiş bir davranış farkıdır. [Navigation block](https://wordpress.org/documentation/article/navigation-block/)
- Tekil sayfalar arama/başlıkla seçilebilir; özel bağlantı için URL girilebilir. Menü öğesinin etiketi, hedefi ve yeni sekme seçeneği ayarlanabilir. Alt menü öğesine sayfa, URL veya başka desteklenen blok eklenebilir; List View/drag-drop ve araç çubuğu ile sıra düzenlenebilir. [Navigation block](https://wordpress.org/documentation/article/navigation-block/), [Custom Link block](https://wordpress.org/documentation/article/custom-link-block/), [Submenu block](https://wordpress.org/documentation/article/submenu-block/)
- WordPress belgeleri blok temalardaki Navigation bloğunun modern yönetim yolu olduğunu söylüyor; klasik Menüler ekranının her tema/kurulumda aynı şekilde mevcut olduğu varsayılamaz. [Site Editor navigation](https://wordpress.org/documentation/article/site-editor-navigation/), [Appearance Menus Screen](https://wordpress.org/documentation/article/appearance-menus-screen/)

### Sayfa, kategori ve özel içerik türleri

- Sayfalar WordPress'te hiyerarşik düzenlenebilir; bir sayfa başka bir sayfanın ebeveyni/alt sayfası olabilir. Ancak varsayılan Pages içerik türü kategorileri ve etiketleri desteklemez. [Create pages](https://wordpress.org/documentation/article/create-pages/)
- Yerleşik yazı kategorileri hiyerarşiktir ve kategori bağlantısı kategori arşiv sayfasına gider; kategori altında alt kategoriler ve o kategorilerdeki yazılar arşivde gösterilir. Bu belge, yazı kategorilerini anlatır; ürün veya hizmet içerik türlerinin kategori yapısını garanti etmez. [Posts Categories screen](https://wordpress.org/documentation/article/posts-categories-screen/)
- Özel içerik türünün menüde seçilebilir olup olmadığı kayıt ayarına bağlıdır: `show_in_nav_menus`, o içerik türünün navigasyon menülerinde seçime açılıp açılmayacağını kontrol eder; varsayılanı `public` değerinden türetilir. [register_post_type()](https://developer.wordpress.org/reference/functions/register_post_type/)
- Özel taxonomy için de `show_in_nav_menus` ayarı, taxonomy öğelerini navigasyon menülerinde seçilebilir kılar; varsayılan `public` değerinden gelir. Bu nedenle ürün/hizmet kategorilerinin menü seçicisinde görünmesi, yalnızca adlarının kategori olmasına bağlı değildir; ilgili içerik/taxonomy yapısının WordPress kayıt ayarları önem taşır. [register_taxonomy()](https://developer.wordpress.org/reference/functions/register_taxonomy/)
- Klasik menü ekranı, menüye eklenebilen post type ve taxonomy nesneleri için ayrı meta kutuları üretir; resmi çekirdek referansı post type kutularının `show_in_nav_menus=true` olan türlerden oluşturulduğunu gösterir. [wp_nav_menu_post_type_meta_boxes()](https://developer.wordpress.org/reference/functions/wp_nav_menu_post_type_meta_boxes/)
- Menünün çıktısı ve derinliği tema tarafından da etkilenir: `wp_nav_menu()` menü/konum seçer; `depth` parametresi çıktıdaki hiyerarşi seviyesini sınırlar, `0` tüm seviyeler anlamındadır. [wp_nav_menu()](https://developer.wordpress.org/reference/functions/wp_nav_menu/)

## Kaynaklardan çıkarımlar

- WordPress'te içerik kaynağı ile menü öğesi birbirinden ayrıdır: sayfa veya kategori seçimi, özel URL girişi ve hiyerarşik sıralama aynı menüde birlikte kullanılabilir. Bu, tek bir menüye farklı hedef türlerini karıştırmanın WordPress'in yerleşik kullanım biçimi olduğunu gösterir. Bu ifade kaynakların belgelenmiş yeteneklerinden yapılan çıkarımdır.
- Ürün/hizmet gibi özel türlerin tekil içeriklerini veya kategorilerini seçilebilir sunma olanağı vardır; bunun görünürlüğü CPT ve taxonomy kayıt seçeneklerine bağlıdır. WordPress kullanıcı belgesi, her özel eklentinin her özel içerik türünü otomatik olarak menüye eklediğini garanti etmez. [register_post_type()](https://developer.wordpress.org/reference/functions/register_post_type/), [register_taxonomy()](https://developer.wordpress.org/reference/functions/register_taxonomy/)
- Standart menü belgeleri bir kategori arşivini menü bağlantısı olarak eklemeyi ve alt menüler kurmayı açıklar. Seçilen kategori altındaki bütün öğeleri her kurulumda otomatik olarak menü öğelerine genişletme davranışını ise bu kaynaklar genel bir özellik olarak garanti etmiyor. Böyle bir davranış, ilgili içerik türü/taxonomy ve tema/eklenti deneyimine göre ayrıca değerlendirilmelidir.
- İncelenen resmi belgelerde Laravel route adına benzer, uygulama içi isimli route hedefi ayrı bir WordPress menü hedef türü olarak tanımlanmıyor. WordPress'in kullanıcıya dönük belgeleri sayfa/öğe seçimi ve URL temelli özel bağlantı akışını anlatıyor. Bu yalnızca bu kaynak setinde görülen sınırdır; WordPress eklenti API'lerinin tüm olasılıkları hakkında kapsamlı bir yokluk iddiası değildir.
- Sayfa hiyerarşisi ile menü hiyerarşisi farklı yapılardır: Page parent-child ilişkisi içerik yapısıdır; menüde öğeyi içeri girintilemek navigasyon yapısıdır. Belgeler ikisini ayrı yönetim davranışları olarak tanımlar. [Create pages](https://wordpress.org/documentation/article/create-pages/), [Appearance Menus Screen](https://wordpress.org/documentation/article/appearance-menus-screen/)

## Resmi kaynaklar

- [Appearance Menus Screen – WordPress.org](https://wordpress.org/documentation/article/appearance-menus-screen/)
- [Navigation block – WordPress.org](https://wordpress.org/documentation/article/navigation-block/)
- [Site Editor navigation – WordPress.org](https://wordpress.org/documentation/article/site-editor-navigation/)
- [Custom Link block – WordPress.org](https://wordpress.org/documentation/article/custom-link-block/)
- [Submenu block – WordPress.org](https://wordpress.org/documentation/article/submenu-block/)
- [Create pages – WordPress.org](https://wordpress.org/documentation/article/create-pages/)
- [Posts Categories screen – WordPress.org](https://wordpress.org/documentation/article/posts-categories-screen/)
- [register_post_type() – Developer.WordPress.org](https://developer.wordpress.org/reference/functions/register_post_type/)
- [register_taxonomy() – Developer.WordPress.org](https://developer.wordpress.org/reference/functions/register_taxonomy/)
- [wp_nav_menu_post_type_meta_boxes() – Developer.WordPress.org](https://developer.wordpress.org/reference/functions/wp_nav_menu_post_type_meta_boxes/)
- [wp_nav_menu() – Developer.WordPress.org](https://developer.wordpress.org/reference/functions/wp_nav_menu/)
