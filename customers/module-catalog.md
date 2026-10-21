# EkAgent modül kataloğu

20 Ekim 2026 tarihinde yerel kaynaklardan hazırlanan keşif özeti. `Modules/` altında 16 modül var. Bu sayı kurulu/etkin müşteri modülü veya kullanılabilir müşteri kaydı sayısı değildir. Veritabanı ve etkinlik bu çalışma sırasında kontrol edilmedi.

Bu katalog modüllerin amacını ve kaynaklarını buldurur. Bir modül seçildiğinde bağlantılı rule/skill, model, `features.json` ve frontend servisini yeniden oku. Teknik kaynakları çoğaltma. Yeni modül eklenirse `Modules/` keşfiyle kataloğu güncelle; eski listeyi kapalı evren sayma.

## Ortak kaynaklar

- [Kural indeksi](../../.ai/rules/index.md)
- [Tema entegrasyonu](../../.claude/skills/theme-integration/SKILL.md)
- [Modül kuralları ve alan genişletme](../../.ai/rules/moduller.md)
- [Frontend](../../.ai/rules/frontend.md) ve [Blade](../../.ai/rules/frontend-blade.md)
- [Settings](../../.ai/rules/settings.md)
- [Mimari kararlar ve çekirdek formlar](../../.ai/Mimari-Kararlar.md)
- [Modül geliştirme workflow'u](../../.ai/workflows/modul-gelistirme.md): yalnız gerçek modül geliştirme kapsamı varsa uygulanır; katalog hazırlama release/kurulum başlatmaz.

## Hızlı seçim

| Modül | İş amacı / onepage kullanım adayı | Başlıca sınır |
|---|---|---|
| Services | Sunulan işler, çözümler, hizmet kategorileri | Ürün, iş portföyü veya sektör değildir |
| Products | Ürün kataloğu, kategori ve ürün galerisi | E-ticaret, fiyat, stok, sepet, ödeme içermez |
| Team | Gerçek çalışan/ekip tanıtımı | Kullanıcı hesabı veya her uzman kartı otomatik Team değildir |
| Brand | Doğrulanmış çalışılan markalar ve logo şeridi | Kendi logo, müşteri yorumu ve resmî ortaklık kanıtı değildir |
| Testimonials | Gerçek müşteri deneyimleri ve görüşler | Logo duvarı veya otomatik Google yorum aktarımı değildir |
| Projects | Tamamlanmış/devam eden işlerin portföyü | Her fotoğraf veya yorum proje değildir |
| Galeriler | Mekân, çalışma ve etkinlik fotoğraf/video gösterimi | Ortak medya kütüphanesi, Slider veya proje kaydı değildir |
| Slider | Ana tanıtım, hero/banner; gerekirse carousel | Veri çokluğu carousel zorunluluğu değildir |
| Sector | Hizmet verilen sektörler/iş alanları | Firmanın yaptığı hizmetle karıştırılmaz |
| Faqs | Gerçek soru-cevaplar, konu grupları | Her akordeon SSS değildir; hizmet ilişkisi yoktur |
| Documents | Sertifika, broşür, katalog dosyası ve belgeler | Ürün kataloğu dosyası Products kaydı değildir |
| Pages | Bağımsız, uzun biçimli kurumsal/bilgi sayfaları | Her tekil anasayfa metni otomatik Pages değildir |
| Blog | Blog, haber ve duyuruların kategori ağacı | Yeni içerik tipi veya satış için sahte yazı üretme |
| Menus | Ana/mobil/footer ziyaretçi navigasyonu | Restoran yemek menüsü ve admin sidebar değildir |
| Popup | Zamanlanmış kampanya/bilgilendirme kayıtları | Hazır modal arayüzü veya ziyaretçi takibi sunmaz |
| Users | Web ziyaretçisi hesap/giriş/doğrulama işlemleri | Ekip listesi değildir; tanıtım onepage'inde çoğunlukla gereksiz |

“Referanslar” adlı ayrı bir modül mevcut listede yok. Bu başlık deneyim alıntısıysa Testimonials; doğrulanmış marka ilişkisi ise Brand; iş portföyüyse Projects olabilir. Müşteri şirketleri listesi gibi belirsiz bir ihtiyacı bu üçünden birine zorla sokma; kullanıcıya sor.

## Services — Hizmetler

Firmanın ne yaptığını gösterir. Gerçek hizmet başlıkları, açıklama, görsel veya ikon ve isteğe bağlı kategori kullanılır. `Service`: `title`, `short_description`, `content`, `slug`, `image`/`image_url`, `icon`, `is_active`, `sort_order`, `extra_fields`; kategori bağı `service_category_id`. Kategoride kısa açıklama, rozet ve `homepage_display` ek alanları mevcut; kart/kompakt sunum kararı tema akışında ele alınır.

`ServicesFrontendService`: `getAll(?int $categoryId = null)`, `getFirst(?int $categoryId = null)`, `find(int $id)`. Aktif hizmetler, aktif kategori veya kategorisiz kayıtlar; `sort_order`, `id` sırası. Slug bulunması public detay route'u kanıtı değildir.

Kaynaklar: [rule](../../Modules/Services/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Services/skills/services-theme-integration/SKILL.md), [model](../../Modules/Services/app/Models/Service.php), [features](../../Modules/Services/features.json), [servis](../../Modules/Services/app/Services/ServicesFrontendService.php).

## Products — Ürünler

Kurumsal ürün kataloğu. `Product`: `title`, `slug`, `short_description`, `content`, kategori, bağımsız kapak `image_url`, sıralı `images`, `site_form_id`, `is_active`, `sort_order`, `extra_fields`. Ürünün kapağının galeriye dahil olması gerekmez.

`ProductsFrontendService`: `products(?int $categoryId = null, ?int $limit = null)`, `categories()`, `findBySlug(string $slug)`, `formFor(Product $product)`. Aktif ürün ve aktif kategori/kategorisiz görünürlük; `sort_order`, `id` sırası. Bağlı mevcut form yoksa form gösterilmez. Ödeme, rezervasyon, stok, fiyat veya restoran siparişi özelliği varsayma. Böyle bir istek kapsam açığıdır.

Kaynaklar: [rule](../../Modules/Products/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Products/skills/products-theme-integration/SKILL.md), [model](../../Modules/Products/app/Models/Product.php), [features](../../Modules/Products/features.json), [servis](../../Modules/Products/app/Services/ProductsFrontendService.php).

## Team — Ekip

Gerçek çalışanların ad/görev/fotoğraf tanıtımı. `TeamMember`: `name`, `title`, `image`/`image_url`, `is_active`, `extra_fields`. Ünvan ve görsel boş olabilir. Mevcut modelde sosyal hesap, biyografi, departman veya kullanıcı hesabı ilişkisi yok; fotoğraftaki kişiye kimlik/görev uydurma.

`TeamFrontendService`: `getAll()`, `getFirst()`, `find(int $id)`; yalnız aktif üyeler. İş anlamı taşıyan sıra/öne çıkan üye politikası yoktur. Sosyal ikon çizmek desteklenen sosyal hesap alanı yaratmaz.

Kaynaklar: [rule](../../Modules/Team/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Team/skills/team-theme-integration/SKILL.md), [model](../../Modules/Team/app/Models/TeamMember.php), [features](../../Modules/Team/features.json), [servis](../../Modules/Team/app/Services/TeamFrontendService.php).

## Brand — Markalar

Firmanın çalıştığı doğrulanmış markaları tanıtır. `Brand`: `name`, `image`/`image_url`, `short_description`, `link`, `sort_order`, `is_active`, `extra_fields`. Ürün/kategori/proje ilişkisi yok. Bir logonun fotoğrafta görünmesi yetkili servis, distribütör veya ortaklık kanıtı sayılmaz.

`BrandFrontendService`: `getAll()`, `getFirst()`, `find(int $id)`; aktif kayıtlar, `sort_order`, `id` sırası. Olmayan linke sahte hedef verme; tek bir işletmenin kendi logosunu bu modüle bağlama.

Kaynaklar: [rule](../../Modules/Brand/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Brand/skills/brand-theme-integration/SKILL.md), [model](../../Modules/Brand/app/Models/Brand.php), [features](../../Modules/Brand/features.json), [servis](../../Modules/Brand/app/Services/BrandFrontendService.php).

## Testimonials — Müşteri yorumları

Kaynaklı gerçek deneyim alıntıları. `Testimonial`: `customer_name`, `company`, `job_title`, `content`, isteğe bağlı `image`/`image_url`, `is_active`, `sort_order`, `extra_fields`. Firma/unvan/portre eksikse uydurma. Google puanı, kaynak URL'si veya yıldız alanı mevcutmuş gibi davranma; güncel features ve kapsamı doğrula.

`TestimonialsFrontendService`: `getAll()`, `getFirst()`, `find(int $id)`; yayın onaylı aktif kayıtlar, `sort_order`, `id` sırası. Yeni yorumlar onay bekler. Google yorumları analize girdi olabilir; otomatik import veya yayınlanmış kayıt değildir. Şirket/hizmet/proje ilişkisi yok.

Kaynaklar: [rule](../../Modules/Testimonials/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Testimonials/skills/testimonials-theme-integration/SKILL.md), [model](../../Modules/Testimonials/app/Models/Testimonial.php), [features](../../Modules/Testimonials/features.json), [servis](../../Modules/Testimonials/app/Services/TestimonialsFrontendService.php), [workflow](../../Modules/Testimonials/.ai/workflows/modul-gelistirme.md).

## Projects — Projeler / iş örnekleri

Kimliği ve anlatısı olan iş portföyü. `Project`: `title`, `slug`, `project_category_id`, `project_date`, `content`, `is_active`, `sort_order`, `images`, `coverMedia`/`cover_url`, `extra_fields`. Kapak proje görsellerinden seçilir. İş aşaması kategorilerle yönetilir; ayrı sabit aşama alanı yok.

`ProjectsFrontendService`: `getAll(?int $categoryId = null)`, `getFirst(?int $categoryId = null)`, `find(int $id)`; aktif/yayındaki kayıtlar, `sort_order`, `id` sırası. Sıradan mekân fotoğrafını veya kanıtsız öncesi-sonrası çiftini proje yapma. Öncesi/sonrası görsellerin aynı işe ait olduğu doğrulanmalı.

Kaynaklar: [rule](../../Modules/Projects/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Projects/skills/projects-theme-integration/SKILL.md), [model](../../Modules/Projects/app/Models/Project.php), [features](../../Modules/Projects/features.json), [servis](../../Modules/Projects/app/Services/ProjectsFrontendService.php), [workflow](../../Modules/Projects/.ai/workflows/modul-gelistirme.md).

## Galeriler — Fotoğraf ve video

Mekân, tesis, etkinlik veya çalışmalardan görsel kayıtlar. `GalerilerItem`: `galeriler_category_id`, `type`, `title`, `description`, `image`/`imageMedia`, `video_url`, `sort_order`, `extra_fields`. Kategori ağacı vardır; her fotoğraf ayrı kayıttır. Video doğrulaması HTTPS YouTube/Vimeo sınırındadır. Ortak Media Library bu içerik modülünden ayrıdır.

`GalerilerFrontendService`: `getAll(?int $categoryId = null)`, `getFirst(?int $categoryId = null)`, `find(int $id)`; kategori filtresi alt kategorileri kapsar; `sort_order`, `id` sırası. Modelde yayın/aktiflik alanı yok; hayalî yayın filtresi üretme. Albüm, hero slider veya proje ilişkisi varsayma.

Kaynaklar: [rule](../../Modules/Galeriler/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Galeriler/skills/galeriler-theme-integration/SKILL.md), [model](../../Modules/Galeriler/app/Models/GalerilerItem.php), [features](../../Modules/Galeriler/features.json), [servis](../../Modules/Galeriler/app/Services/GalerilerFrontendService.php), [workflow](../../Modules/Galeriler/.ai/workflows/modul-gelistirme.md).

## Slider — Ana tanıtım alanı

Hero/banner veya açıkça gereken carousel. `Slider`: `title`, `subtitle`, `image`/`image_url`, `is_active`, `extra_fields`. Güncel features içinde `eyebrow`, `highlighted_title`, `background_image`, `button_text`, `directions_button_text`, `directions_url`, `short_description` bulunuyor. Bunlar destekleniyor diye tasarım hepsini kullanmak zorunda değildir.

**Belge-kod farkı:** mevcut rule/skill ek alanları daha dar anlatıyor. `directions_url` özel yol tarifi alanıdır; genel bir CTA URL alanı olarak varsayılmaz. Alanın tanımlı olması tema tarafından kullanıldığı veya dolu olduğu kanıtı değildir. Features ve tüketen tema kodunu yeniden doğrula.

`SliderFrontendService`: `getFirst()`, `getAll()`; aktif kayıtlar. Sıralama alanı/politikası yoktur. Birden çok kayıt kendiliğinden carousel gerektirmez. Belirli hero seçimi veya sıra belirsizse kullanıcıya sor.

Kaynaklar: [rule](../../Modules/Slider/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Slider/skills/slider-theme-integration/SKILL.md), [model](../../Modules/Slider/app/Models/Slider.php), [features](../../Modules/Slider/features.json), [servis](../../Modules/Slider/app/Services/SliderFrontendService.php).

## Sector — Hizmet verilen sektörler

Firmanın hangi müşteri iş alanlarına hizmet verdiğini anlatır. `Sector`: `title`, `slug`, `short_description`, `content`, `image`/`image_url`, `sort_order`, `is_active`, `extra_fields`; modelde meta alanları da var. Hizmet “ne yapılıyor”, sektör “hangi alana hizmet ediliyor” ayrımını koru.

`SectorFrontendService`: `getAll()`, `getFirst()`, `find(int $id)`; aktif kayıtlar, `sort_order`, `id` sırası. Başka içeriklerle ilişki veya public detay route'u varsayılmaz.

Kaynaklar: [rule](../../Modules/Sector/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Sector/skills/sector-theme-integration/SKILL.md), [model](../../Modules/Sector/app/Models/Sector.php), [features](../../Modules/Sector/features.json), [servis](../../Modules/Sector/app/Services/SectorFrontendService.php).

## Faqs — Sık sorulan sorular

Doğrulanmış soru-cevap içerikleri ve isteğe bağlı konu grubu. `Faq`: soru `name`, yanıt `content`, `category_id`, `extra_fields`. Gerçek yorumlardan soru ihtiyacı çıkarılabilir; işletmenin cevabı bilinmiyorsa cevabı gerçekmiş gibi yazma.

`FaqsFrontendService`: `getAll(?int $categoryId = null)`, `getFirst(?int $categoryId = null)`, `find(int $id)`. Aktiflik/yayın ve iş sırası alanı yok; hizmet ilişkisi de yok. Kategorisiz kayıtlar geçerlidir. Tasarım gerektiriyor diye hizmete özel filtre uydurma.

Kaynaklar: [rule](../../Modules/Faqs/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Faqs/skills/faqs-theme-integration/SKILL.md), [model](../../Modules/Faqs/app/Models/Faq.php), [features](../../Modules/Faqs/features.json), [servis](../../Modules/Faqs/app/Services/FaqsFrontendService.php).

## Documents — Belgeler

Sertifika, broşür, katalog dosyası, indirme içeriği. `Document`: `name`, `content`, `slug`, `file`/`file_url`, `status`, isteğe bağlı `category_id`, `extra_fields`. Dosya bulunmadan indirme bağlantısı veya sertifika kanıtı üretme.

`DocumentsFrontendService`: `getAll(?int $categoryId = null)`, `getFirst(?int $categoryId = null)`, `find(int $id)`; `published` kayıtlar. Tanımlı iş sırası ve public detay/indirme route'u varsayılmaz. Kategori adı/ID'sini sabitleme.

Kaynaklar: [rule](../../Modules/Documents/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Documents/skills/documents-theme-integration/SKILL.md), [model](../../Modules/Documents/app/Models/Document.php), [features](../../Modules/Documents/features.json), [servis](../../Modules/Documents/app/Services/DocumentsFrontendService.php).

## Pages — Kurumsal / bilgi sayfaları

Bağımsız uzun biçimli kurumsal içerik ve kategori URL hiyerarşisi. `Page`: `title`, `short_description`, `content`, `image`/`image_url`, `slug`, `status`, `sort_order`, `page_category_id`, meta alanlar, `extra_fields`. Hakkımızda metni Pages adayı olabilir; tekil sabit kısa anasayfa alanında Settings'i de değerlendir.

`PagesFrontendService`: `getAll(?int $categoryId = null)`, `getFirst(?int $categoryId = null)`, `find(int $id)`; `published` kayıtlar; `sort_order`, `title`, `id` sırası. Kategori filtresi alt kategorileri kapsar. Mevcut `url_path` ve public route sözleşmesini koru; kategori slug'larını yeniden icat etme.

Kaynaklar: [rule](../../Modules/Pages/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Pages/skills/pages-theme-integration/SKILL.md), [model](../../Modules/Pages/app/Models/Page.php), [features](../../Modules/Pages/features.json), [servis](../../Modules/Pages/app/Services/PagesFrontendService.php).

## Blog — Blog / haber / duyuru

`BlogContent` ortak kayıt; ayrım kategori ağacıyla yapılır. Başlık, kısa açıklama, içerik, öne çıkan görsel, slug, durum ve yayın tarihi bulunur. Sosyal medyadaki her gönderiyi otomatik blog kaydına dönüştürme; onepage'de güncel içerik yoksa bu bölümü zorlamadan atla.

`BlogFrontendService`: `getAll(?int $categoryId = null)`, `getFirst(?int $categoryId = null)`, `find(int $id)`; yalnız `status=published`; kategori ve alt kategorileri. `published_at` alanı bulunması servisin en yeni sırayla döndürdüğü anlamına gelmez; servis iş sırası garanti etmiyor.

Kaynaklar: [rule](../../Modules/Blog/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Blog/skills/blog-theme-integration/SKILL.md), [model](../../Modules/Blog/app/Models/BlogContent.php), [features](../../Modules/Blog/features.json), [servis](../../Modules/Blog/app/Services/BlogFrontendService.php).

## Menus — Ziyaretçi navigasyonu

Ana/mobil/footer gezinmesi. `Menu`: `name`, `icon`, `key`, `is_active`; `MenuItem`: etiket, ikon, hedef türü, URL/route/anchor, kaynak ve ağaç/sıra alanları. **Yemek menüsü değildir.** Restoran menüsünü içerik anlamına göre Products, Documents veya başka doğrulanmış kapsamla değerlendir; fiyat ihtiyacını mevcut özellik sayma.

`MenusFrontendService::getTree(string $menuKey)` → `label/icon/url/new_tab/children` ağacı. Menü anahtarı tema konumundan gelir; etkinlik ve hedef çözümünü NavigationService yapar. Anchor için gerçekten var olan bölüm kimliği gerekir. Pages kategori arşivi veya Services public URL'si uydurma.

Kaynaklar: [rule](../../Modules/Menus/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Menus/skills/menus-theme-integration/SKILL.md), [model](../../Modules/Menus/app/Models/Menu.php), [features](../../Modules/Menus/features.json), [servis](../../Modules/Menus/app/Services/MenusFrontendService.php), [geliştirme skill'i](../../Modules/Menus/skills/menus-development/SKILL.md).

## Popup — Zamanlanmış bildirimler

Başlangıç/bitiş aralığı ve tekrar politikası bulunan bağımsız duyuru/kampanya kaydı. `Popup`: `title`, `content`, `image_media_id`, `is_active`, `starts_at`, `ends_at`, `repeat_policy`, `repeat_interval`, `repeat_unit`, `extra_fields`.

`PopupFrontendService`: `published`, `eligible`, `shouldShow`; gerçek parametre ve tarih sözleşmesi skill'de. Sıra `starts_at DESC`, `id DESC`. Servis uygunluğu hesaplar; hazır modal, otomatik açılış, kapatma takibi veya ziyaretçi kalıcılığı sağlamaz. Onepage teklifini göstermek için otomatik popup ekleme.

Kaynaklar: [rule](../../Modules/Popup/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Popup/skills/popup-theme-integration/SKILL.md), [model](../../Modules/Popup/app/Models/Popup.php), [features](../../Modules/Popup/features.json), [servis](../../Modules/Popup/app/Services/PopupFrontendService.php).

## Users — Web kullanıcıları

Ziyaretçi hesabı, giriş, çıkış, e-posta doğrulama ve parola sıfırlama. Ekip tanıtımı veya yönetici hesabı değildir. Genel kullanıcı listesini herkese açık içerik yapma. Modelin kişisel hesap ve oturum alanları tasarım için kopyalanmaz.

`UsersFrontendService::current()` mevcut aktif web hesabını döndürür. Diğer metotlar kimlik doğrulama işlemleridir; genel `getAll` veya profil güncelleme API'si yok. Backend akışları mevcut olsa da hazır tema ekranı veya kayıt endpoint'i varsayılmaz. Basit tanıtım onepage'inde gerçek hesap ihtiyacı yoksa kullanma.

Kaynaklar: [rule](../../Modules/Users/.ai/rules/tema-entegrasyonu.md), [skill](../../Modules/Users/skills/users-theme-integration/SKILL.md), [model](../../Modules/Users/app/Models/User.php), [features](../../Modules/Users/features.json), [servis](../../Modules/Users/app/Services/UsersFrontendService.php).

## Modül dışı sayfa alanları

- Header/footer ve bölüm düzeni tema bileşenleridir. Hero da bir sunumdur; veri sahibi anlamına göre Slider veya başka doğrulanmış kaynak olabilir.
- Tekil, sabit alanlı yönetilebilir site bilgileri için Settings'i değerlendir: logo, iletişim, sosyal hesap, kısa bölüm metni gibi. Var olan ayar anahtarlarını koddan doğrula; burada yeni anahtar varmış gibi davranma. Tekrar eden listeleri Settings içine zorlama.
- İletişim/teklif formu çekirdek form yönetimidir; mevcut `x-site-form` sözleşmesi ve gerçek form anahtarı kullanılmalıdır. Bir formun çizilmesi başvuru altyapısı veya rezervasyon sistemi kurulmuş anlamına gelmez.
- Puan/yorum sayısı, harita, WhatsApp ve telefon bağlantıları için doğrulanmış kaynak ve gerçek hedef gerekir. Yeni entegrasyon, API aboneliği, import veya veri yazımı bu katalogla yetkilendirilmez.

## Güncellik ve uygulama sınırı

Mevcut frontend servisleri çoğunlukla koleksiyon/tek kayıt okur; HTML veya public route üretmez. `getAll` sayfalama veya limit değildir. `getFirst` işletme açısından en iyi/öne çıkan kaydı seçtiğini garanti etmez. Kod tamamen kurulu değilken sınıf çözümleme ile kapalı modülde boş sonuç farklı durumlardır; ayrıntılar ortak skill'dedir.

Alan ihtiyacı eksikse bunu tasarımın koşulu olarak belirt. Customers dışına rule/skill, model veya migration yazma. Ortak alan genişletme akışı ancak kullanıcı ileride uygulama kapsamını açarsa izlenir.
