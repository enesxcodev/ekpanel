---
name: menus-development
description: EkAgent Menus modülünün öğe yönetimi, hedef çözümü ve isteğe bağlı içerik kaynaklarını geliştir veya düzelt. Statik admin sidebar tanımları için kullanılmaz.
---

# Menus geliştirme

Önce [proje kural indeksini](../../../../.ai/rules/index.md), [modül kurallarını](../../../../.ai/rules/moduller.md) ve mevcut Menus kodunu oku. Bu skill mevcut ürün davranışlarını açıklar; kapsam dışı mimari değişiklik izni vermez.

- `Menu` bir anahtarla seçilen menüdür. `MenuItem` bağımsız navigasyon ağacıdır: `parent_id`, `sort_order`, kullanıcı etiketi, hedef türü ve Var/Yok durumunu taşır. Admin sidebar `config/menus.php` ayrı sistemdir.
- Hedef türleri `group`, `url`, `route`, `anchor`, `content`tir. Hedef değişince eski türe ait alanları temizle. URL güvenliği ve route uygunluğu `NavigationService` üzerinden uygulanır; yönetici veya auth gerektiren route'ları public seçenek sayma.
- Öğe yazımları `MenuService` içinde menü kaydını kilitleyen transaction ile yapılır. Aynı menüye ait ebeveyn, döngü engeli ve manuel/dinamik çocuk çakışması korunur. Üst öğe başka menüden olamaz. Çocuğu olan öğenin silinmesi reddedilir; menünün silinmesi tüm ağacı kaldırır.
- Yok öğeler ve altları admin verisinde kalır. `NavigationService` public çıktıda tüm dalı gizler; geçersiz kaynak/route da görünmez. Modül kaldırılınca veri silinmez.
- Yeni içerik kaynağı için ilgili içerik modülünde `MenuSource` adaptörü sağla ve modülün `config/config.php` dosyasında `navigation_sources` anahtarına kaynak anahtarı → adaptör sınıfı olarak bir kez kaydet. `MenuSources` yalnız etkin modülleri keşfeder. İçerik modülü provider'ında Menus'u zorunlu resolve etme, Composer bağımlılığı ekleme; adaptör yalnız iki modül birlikte mevcutken yüklenir.
- `MenuSource::options()` seçilebilir kayıtları, `find($id)` etiket/URL/hata durumunu, `supportsChildren()` dinamik yeteneği, `children($id)` türetilen public çocukları verir. Teknik imzaları güncel interface'den oku. Silinmiş/yayınlanmamış kayıtta geçerli URL üretme; kategori arşivi yoksa null URL ile grup davranışı açıkça kullanılabilir. Public hedef yokluğunu hata olarak bildir.
- Bir kategori seçmek çocuklarını eklemez. `dynamic_children` açık ve kaynak destekliyorsa kaynak tarafından tanımlanan doğrudan içerikler türetilir. Pages kategorileri şu an doğrudan yayımlanmış sayfaları sunar. Menü ağacına kalıcı kopya ekleme.
- `menus.key`, ikon ve etkinlik ortak; menü adı `menu_translations` içinde dil başınadır. Her `menu_items` kaydı `lang_id` taşır ve her dilin öğe ağacı/sırası bağımsızdır. CRUD, ebeveyn, silme ve reorder işlemlerini aynı menü+dil içinde sınırla. Eksik dil çevirisini başka dile fallback etme.
- `MenusLanguageService` aktif dili seçer; TR varsayılandır. Admin workspace `?lang=xx` ile seçimi taşır. `LocalizedMenuSource` sağlayan içerik adaptörlerine `optionsForLanguage`, `findForLanguage` ve `childrenForLanguage` çağrıları yapılır; eski `MenuSource` davranışı geriye uyumludur.
- Yeni CRUD akışı, işlem izinleri, ortak DataTable/bildirim altyapısı için mevcut kardeş modülleri izle. Tema tüketiminde [Menus tema skill'ini](../menus-theme-integration/SKILL.md) kullan.
