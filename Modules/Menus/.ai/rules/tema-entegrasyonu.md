# Menus tema entegrasyonu

- Menus, ziyaretçi navigasyonunun yönetilen kayıtlarını sağlar. Ana menü, alt alan bağlantıları, mobil menü ve kategori gezinmesi aday kullanım alanlarıdır. Konum, bağlantı amacı, görsel hiyerarşi ve içerik tekrarını birlikte değerlendir; yalnız başlık kelimesiyle eşleştirme yapma. Eşleşme belirsizse kullanıcıya sor.
- Statik admin sidebar tanımları modüllerin `config/menus.php` dosyalarındadır. Bunlar kullanıcı tarafından düzenlenen `Menu` / `MenuItem` verisiyle karıştırılmaz.
- Menü anahtarı tema tarafından seçilir. `MenusFrontendService::getTree($key)`, mevcut `NavigationService` üzerinden yalnız etkin menüyü, Var öğeleri ve geçerli hedefleri döndürür. Yok veya geçersiz üst öğenin bütün dalı public çıktıda gizlenir; kayıtlar admin ağacında korunur.
- Mevcut `welcome` ve `pages::pages.show` görünümleri `menus.main_menu` config anahtarını kullanır; varsayılan menü anahtarı `main` olur. Kayıt yoksa gezinme görünmez. Diğer tema konumlarında aynı servise ilgili menü anahtarını ver; modül devre dışıyken erişimi koşullu yap.
- `menus::public.navigation` hazır, iç içe listeli renderer'dır. `label`, `icon`, `url`, `new_tab`, `children` ağacını kullanır; tema görünümü bunları yeniden sunabilir. Etiketleri escape et; yeni sekmede `noopener noreferrer` ilişkisini koru. Çok seviyeli verinin tüm seviyelerini erişilebilir biçimde göster; sessizce kırpma.
- Grup başlıkları bağlantısız olabilir. Kategori seçimi kendiliğinden çocuk üretmez; yalnız `dynamic_children` açık ve kaynak destekliyorsa doğrudan yayımlanmış çocuk sayfalar türetilir. Tema veri sorgularını tekrarlayarak bu davranışı aşma.
- Anchor hedefi bölüm kimliği ve isteğe bağlı sayfa adresini içerir; renderer bölümün DOM içinde var olduğunu doğrulamaz. Pages temizleyicisindeki `id` sınırlamasını mevcut koddan kontrol et; eksik bölüm kimliği üretimini varsayma.
- Kaynak modülün kurulu/etkin ve public hedefinin kullanılabilir olmasını doğrula. Pages kategorisinin arşiv URL'sini veya Services public adreslerini uydurma. Kaynak davranışları kendi adaptöründe tutulur.
- Alan genişletme ve ortak form altyapısı için [modül kurallarını](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme), geliştirme sınırları için [Menus geliştirme skill'ini](../../skills/menus-development/SKILL.md) oku.

- `Menu` ve `MenuItem` nullable `icon` alanı taşır; ikon ham HTML değildir. Öğenin ikonu public ağaçta bulunur, dinamik çocuklarda tanımlanmamışsa null olur.
- Menü anahtarı/icon/etkinlik ortaktır; görünen menü adı çevirisi `menu_translations` tablosunda, her dilin menü öğeleri ve sıralaması bağımsız `menu_items.lang_id` ağacındadır. `MenusFrontendService::getTree($key, $languageCode)` ile açık dili ver; dil verilmezse route/context ve TR varsayılanı kullanılır. Eksik çeviride başka dile fallback yapılmaz.

- Tema okumasında `MenusFrontendService` kullan. Güncel metotlar, parametreler, dönüş türleri ve yayın/sıralama davranışı [modülün tema skill'inde](../../skills/menus-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce bu sözleşmeyi oku.
