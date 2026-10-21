# İş Akışı

Bu belge modül adaylarını keşiften uygulama tamamlanmasına kadar izlenecek sırayı ve model görev devrini tanımlar. Aynı akış, aday otomatik keşfedilmiş veya kullanıcı tarafından doğrudan istenmiş olsa da geçerlidir. Aday özeti [README.md](README.md) içindedir; her adayın klasörü `Modules/Plans/<Aday>/` biçimindedir. Ayrıntılı plan, dosya adında durum etiketi taşır (ör. [Store - Yeni](Store/Store%20-%20Yeni.md)); ilgili tasarım ve çıktı görselleri de aynı klasörde saklanır. Her iki iş türünün görev durumu ve handoff'ları [Görev Günlüğü](Görev%20Günlüğü.md) içinde tutulur.

## Sıralı aşamalar

### 1. Katalog envanteri ve kural keşfi — 6.1 SOL, low

Herhangi bir keşif/modül işine başlamadan önce [Görev Günlüğü](Görev%20Günlüğü.md) oku. Etkin kilit varsa veya son modül tamamlanmasından iki saat geçmediyse yeni keşif başlatma. İlk eligible run tam baseline sweep'tir: katalogdaki tüm modüllerin ilgili rules/skills dosyalarını incele, Store seed adayını da yeniden değerlendir ve e-ticarete özel dar filtre uygulama. Bu tam sweep tamamlanma zamanı ve dosya imzaları [Keşif Durumu](Ke%C5%9Fif%20Durumu.json) içinde saklanır. Her discovery başlamadan kendi kayıt/kilidini yaz; bittiğinde tamamlanma zamanını kaydet ve kilidi serbest bırak.

1. Önce `D:\localhost\ekAgentModules\catalog.json` dosyasını incele ve mevcut modül kayıtlarını/işlev alanlarını çıkar.
2. Katalogda görünür boşlukla ilgili modüllerin `.ai/rules/` ve `skills/` dosyalarını oku. Bu yönergeler modülün kasıtlı sınırlarını ve benzer kapsamı gösterir.
3. Belirsizlik sürerse yalnız o belirsizliği giderecek ilgili koda bak. Geniş kod taraması yapma.
4. Katalog ve ilgili yönergelerle doğrulanabilen, en önemli tüm yeni adayları seç. Store seed'ini kanıtla doğrula/ele; e-ticaret keşif filtresi değildir. E-ticaretle ilişkili birden çok işlev aday çıkarsa tek modülde gruplandır. Her adaya kısa, somut gerekçe yaz; kısa listeyi `README.md` içinde güncelle.

### 2. Ayrıntılı uygulanabilir plan — 6.1 SOL, low

Seçilen her yeni aday için `Modules/Plans/<Aday>/` klasörü oluştur ve içinde `<Aday> - Yeni.md` planı aç. Aday tamamlandığında planı `<Aday> - Tamamlandı.md` olarak yeniden adlandır. İlgili tasarım/çıktı görsellerini de aynı klasörde tut ve plan içinden bağla. Plan, 5.6 TERA medium uygulayıcının doğrudan takip edebileceği görev sırası ve tamamlanma koşulları içersin. Şunları kapsasın:

- Aday gerekçesi, iş sınırı ve mevcut modüllerle ilişkisi; varsa sınır/karar soruları.
- CMS modül kurulum sözleşmesi: paket metadata'sı, `features.json`, route/izin/menü, migrations, açık install seeder'ları, asset yayını, bağımlılık uyumluluğu, etkinlik/kapatma/kaldırma davranışı ve ortak kurulum servisleri.
- Gerekli admin ve frontend akışları; kullanıcı hataları ve modül eksik/kapalı durumları.
- Eşzamanlı istekler, transaction/izolasyon, idempotency, veri snapshot'ları ve telafi/hata davranışları için tutarlılık hedefleri. Uygun yöntemi seçme sorumluluğu uygulayıcıdadır; belirli mimari kalıp, interface, repository veya ek bağımlılık dayatılmaz.
- Yalnız ilgili davranış ve önemli hata durumlarını kapsayan en küçük anlamlı test listesi. Proje kurallarıyla çelişen test ekleme/çalıştırma talimatı yazma.
- Uygulama tamamlanınca test/build sonuçlarını ve yerel değişiklikleri aynı görev kaydına rapor etme biçimi.

Yalnız ilk tam baseline sweep'i tüm modüllerin rules/skills dosyalarını derinlemesine okur. Baseline'da katalog entry signature'ı ve her ilgili rules/skills dosyasının path/SHA-256 imzasını [Keşif Durumu](Ke%C5%9Fif%20Durumu.json) dosyasına yaz. Sonraki run'larda küçük `catalog.json` girdilerini ve dosya imzalarını kontrol et; yalnız yeni modülü veya katalog metadata/rules/skills imzası değişen modülü derin oku. Değişmeyen yönergeleri veya kodu yeniden okuma. Her modülün önceki değerlendirmesini indekste koru; yeni kanıt eski sonucu değiştiriyorsa README/planı güncelle ve nedeni görev günlüğüne kaydet. Katalog/imza değişikliği yoksa yalnız görev günlüğü ve son tarama zamanı güncellenir; aday özeti ve planlara dokunulmaz. İlgili kod yalnız değişen yönergelerin bıraktığı belirsizliği çözmek için okunur.

Planlama tamamlanması uygulama veya yayın yetkisi değildir. Öncelik, kapsam ve mimari belirsizlikleri planın içinde işaretle; kod değişikliğine geçme.

#### Harici API ve bulunmayan altyapılar için kullanıcı onayı

Onay kayıtlarının güncel listesi ve durum geçmişi [Onay Gerektiren İşler](Onay%20Gerektiren%20İşler.md) dosyasında tutulur.

Her modül planında dış servis/API veya projede henüz bulunmayan bir altyapı (ör. ödeme sağlayıcısı, kargo/taşıyıcı servisi, SMS/e-posta sağlayıcısı) gerektiren işleri ayrı bir **Onay bekliyor** bölümünde listele. Her madde için belirt: hangi servis/hesap/anahtar veya ürün kararı gerekiyor, onay gelene kadar hangi yerel kapsam uygulanabilir ve onay gelmezse hangi iş ertelenir.

6.1 SOL entegrasyonun kendisini onaylanmış varsaymaz ve TERA'ya uygulanabilir çekirdek kapsamı ayrı gösterir. 5.6 TERA, kullanıcı bu belirli entegrasyon/kapsam için açık onay vermedikçe sağlayıcı API'sine bağlanmaz, gerçek callback/webhook veya canlı dış işlem akışı geliştirmez; gizli anahtar/hesap bilgisi uydurmaz ve entegrasyonu tamamlanmış gibi gösteren sahte bir bağlantı kurmaz. Yerel veri modeli, sağlayıcıdan bağımsız sipariş durumu ve test fake'leri gibi entegrasyondan bağımsız işler ancak planda tanımlıysa yapılabilir; gerçek ödeme/kargo başlatma ya da gönderi oluşturma yapılmaz. Onay gelmezse bu entegrasyon maddeleri `waiting_approval` olarak kalır; bağımsız ve onaylı yerel kapsam ayrıca tamamlanabilir.

Onay verilirse yalnızca onaylanan servis ve işlevler açılır; servis seçimi, kimlik bilgisi saklama, webhook güvenliği, hata/tekrar deneme ve maliyet/operasyon etkileri planlanır. Onay, release veya GitHub yayını talebi anlamına gelmez; yayın kapısı ayrıca geçerlidir.

### 3. Form tasarım konsepti — ImageGen

Aday seçilip form gereksinimleri yazılı plana alındıktan sonra ImageGen, modülün ekle/düzenle formu için tasarım konsepti üretir. Store örneğinde:

- Görsel referans: `output/playwright/blog-form-desktop.png` (mevcut Blog admin form ekranı).
- İçerik kaynağı: [Store planındaki sekme ve alan gereksinimleri](Store/Store%20-%20Yeni.md#admin-ürün-oluşturmadüzenleme-formu).
- Formun sekme/alan grupları görselde görünür olmalı; mevcut admin panelinin Tabler yerleşimi, sidebar ve renk/boşluk dili referans alınmalı.
- ImageGen çıktısı konsepttir. Kaynak görseldeki alanlar yeni modül için otomatik gereksinim sayılmaz; uygulayıcı plan ve mevcut bileşenleri esas alır.
- Tasarım çıktısı adayın `Modules/Plans/<Aday>/` klasörüne kaydedilir ve ayrıntılı plan içinden bağlanır. Yalnız tasarım görselinin üretimi kod uygulamasının tamamlandığı anlamına gelmez.

### 4. Modül uygulaması ve dar doğrulama — 5.6 TERA, medium

5.6 TERA medium, ayrıntılı planı ve ImageGen konseptini devralır. Uygulama sırası:

1. Uygulamaya başlamadan `.ai/rules/index.md` ve kapsamdaki kuralları, `.ai/rules/moduller.md` ile `.ai/workflows/modul-gelistirme.md` dahil, oku; `grep -rin '<konuyla ilgili anahtar sözcük>' .ai/rules` ile ek eşleşmeleri bul.
2. Paket/Laravel API'si gerekiyorsa sürümleri doğrula ve proje talimatındaki resmi dokümantasyon aramasını kullan.
3. Modülü ilgili mevcut örüntülerle oluştur; planın CMS kurulum/izin/asset ve form gereksinimlerini tamamla. Seçilen mimari yöntemi gereksinim ve kurulum sınırlarıyla gerekçelendir; kalıp dayatma.
4. Admin ekle/düzenle formunu konsept ve planla uyumlu uygula. Sekmeler klavye/mobil kullanımında erişilebilir, validasyon hataları doğru sekmede görünür ve alan değerleri güvenli biçimde korunur.
5. Yalnız ilgili özellik ve önemli hata yollarını kapsayan en dar mevcut testleri çalıştır. PHP değiştiyse Pint; modül asset'i değiştiyse gereken derleme ve frontend görünüm kontrolünü proje kurallarına göre tamamla. Başarısız kontrolü gizleme; tamamlanana kadar yerel uygulama işini bitmiş sayma.
6. Başarılı kod/test/build sonrası modül kaynağını farkları koruyarak `D:\localhost\ekAgentModules\<Modul>` konumuna kopyala. Bu kaynak staging adımı otomasyonla keşfedilen ve kullanıcı tarafından başlatılan modüller için aynıdır; push veya yayın değildir.
7. [Görev Günlüğü](Görev%20Günlüğü.md) kaydını test/build sonuçları, staging hedefi ve tamamlanma zamanı ile güncelle; kilidi serbest bırak. Aynı görev kaydında değişen dosyaları, mimari kararı, bilinen sınırları ve tamamlanma durumunu raporla.

Uygulama öncesi plandaki **Onay bekliyor** maddelerini kontrol et. Açık kullanıcı onayı bulunmayan entegrasyonları kapsam dışı tut ve görev günlüğünde `waiting_approval` olarak kaydet; bağımsız yerel kapsam varsa onu ayrıca bitirebilirsin. Tamamlanma raporu, ertelenen entegrasyonları açıkça saysın.

## Release ve dağıtım kapısı

Uygulama başarısı sonrası staged kaynak kopyalama normal tamamlanmadır; bu adım push etmez. GitHub'a push, Release/asset, ortak `catalog.json` güncellemesi ve yayın yalnızca kullanıcı açıkça talep ettiğinde yürütülür; modülün otomasyonla keşfedilmiş olması veya uygulamanın tamamlanması bu talebin yerine geçmez. Açık talep gelirse staged kaynağı doğrula, sonra `.ai/rules/moduller.md` içindeki mevcut release/dağıtım adımlarını ve `.ai/workflows/modul-yayinlama.md` rehberini uygula. Kaynak commit'i, release ZIP'i, tag/asset, gerçek URL/hash ile katalog kaydı ve push sırasını koru; doğrulanmamış yayın adresini başarılı diye raporlama.

GitHub araçlarını/API'yi o görev sırasında denetle. Bağlı entegrasyon metin/dosya/commit yeteneklerini sağlarken Release oluşturma veya ZIP asset yükleme sunmuyorsa authenticated `gh` CLI ya da GitHub REST API'yi tercih et. API ve CLI kullanılamazsa Playwright yalnız gerekli GitHub web arayüzü adımlarında yedektir. Auth, korumalı branch veya başka dış engel varsa son başarılı adıma kadar olan durumu raporla; sırları kaydetme.

## Discovery cooldown ve çakışma önleme

- Aynı anda yalnız bir modül görevi veya onun release aşaması yürür; discovery aktif uygulama/yayınla çakışmaz.
- Son modül görevi bitince sonraki discovery için en az iki saat beklenir. Kullanıcı release istemişse süre ancak release, asset, katalog doğrulaması ve push tamamlanınca başlar; istememişse uygulama ve ilgili doğrulama tamamlanınca başlar.
- Bu süre sabit saat dilimi/duvar saati tekrarı değildir; ilgili görevin bitiş zamanına göre hesaplanır.
- Discovery yalnız yeni ve ilgili kurallarla doğrulanmış adayları ekler; aynı aday veya ayrıntılı plan tekrar yazılmaz.
