# İş Akışı

Bu belge modül adaylarını keşiften uygulama tamamlanmasına kadar izlenecek sırayı ve model görev devrini tanımlar. Aynı akış, aday otomatik keşfedilmiş veya kullanıcı tarafından doğrudan istenmiş olsa da geçerlidir. Aday özeti [README.md](README.md) içindedir; her adayın klasörü `Modules/Plans/<Aday>/` biçimindedir. Ayrıntılı plan, dosya adında durum etiketi taşır (ör. [Store - Tamamlandı](Store/Store%20-%20Tamamland%C4%B1.md)); ilgili tasarım ve çıktı görselleri de aynı klasörde saklanır. Her iki iş türünün görev durumu ve handoff'ları [Görev Günlüğü](Görev%20Günlüğü.md) içinde tutulur.

## Sıralı aşamalar

### Her çalışmanın keşif istemi ve değerlendirmesi

Keşif modeli her çalışmada önce şu soruyu yanıtlar: **“Bu CMS'yi sen geliştiriyor olsaydın mevcut özelliklerde neyi iyileştirir veya genişletirdin; ayrıca hangi yeni bağımsız modül kullanıcıya anlamlı değer katardı ve neden?”** En fazla iki güçlü iş kalemi seç; iki öneri bulmak zorunlu değildir.

Her fikri bug/kalite düzeltmesi, mevcut modül genişletmesi, ortak çekirdek iyileştirmesi, yeni bağımsız modül veya harici entegrasyon olarak sınıflandır. Kullanıcı değerini, doğrudan kanıtı, model çıkarımını, mevcut kapsam/sahiplik sınırını, güven düzeyini ve açık soruları belirt. Makul ürün ihtiyacı için açık kapsam dışı kuralı veya önceden kanıtlanmış müşteri talebi şartı koyma; çıkarımı doğrulanmış talep gibi sunma. Belirsiz fikri sessizce eleme; karar/kanıt bekleyen girdi olarak kaydet. Fikir veya aday bulunmayan gün de değerlendirilen alanları ve eşiği geçmeme nedenini raporla; iş uydurma.

### 1. Katalog envanteri ve kural keşfi — 6 Luna, low

Herhangi bir keşif/modül işine başlamadan önce [Görev Günlüğü](Görev%20Günlüğü.md) oku. Etkin kilit varsa yeni iş başlatma. Günlük çalışma Türkiye saatiyle 00:15'te başlar; ikinci modül işi aynı günlük çalışmada en erken 00:45'te ve ilk iş tamamlandıktan sonra başlar. İki iş sırayla yürür, paralel çalışmaz. İlk tam baseline çalışması katalogdaki tüm modüllerin ilgili rules/skills dosyalarını inceler, Store seed adayını yeniden değerlendirir ve e-ticarete özel dar filtre uygulamaz. Tam sweep zamanı ve dosya imzaları [Keşif Durumu](Ke%C5%9Fif%20Durumu.json) içinde saklanır. Her iş başlamadan görev kaydı ve kilit yazılır; tamamlandığında bitiş zamanı kaydedilir ve kilit serbest bırakılır.

Her keşifte ana soru şudur: **“Bu CMS'nin amacı ve hedef kullanıcılarının ihtiyaçları açısından, CMS'de bulunması gereken ama şu anda hiçbir mevcut modülün karşılamadığı hangi bağımsız modül veya yetenek eksik? Mevcut modüllerin dışında hangi modülü önerirsin?”** Yalnız katalogda adı olmayan bir işlevi otomatik olarak yeni modül sayma; önce bunun ayrı modül sorumluluğu mu, mevcut modülün genişletmesi mi, yoksa onay gerektiren harici entegrasyon mu olduğunu belirle. Her öneride kullanıcı ihtiyacını, mevcut modüllerin neden karşılamadığını, modül sınırını ve kanıt/güven düzeyini açıkla. Kanıt yetersizse bunu kesin eksik diye sunma; ek kanıt bekleyen girdi olarak tut. Uygun aday yoksa “eksik yok” diye kesin hüküm vermek yerine değerlendirilen ihtiyaç alanlarını ve neden yeni modül eşiğini geçmediklerini özetle.

1. Önce `D:\localhost\ekAgentModules\catalog.json` dosyasını incele ve mevcut modül kayıtlarını/işlev alanlarını çıkar.
2. Katalogda görünür boşlukla ilgili modüllerin `.ai/rules/` ve `skills/` dosyalarını oku. Bu yönergeler modülün kasıtlı sınırlarını ve benzer kapsamı gösterir.
3. Belirsizlik sürerse yalnız o belirsizliği giderecek ilgili koda bak. Geniş kod taraması yapma.
4. CMS amacı ve kullanıcı işleri açısından mevcut modüllerde iyileştirme/genişletme ile yeni bağımsız modül ihtiyaçlarını birlikte ara. Store seed'ini kanıtla doğrula/ele; e-ticaret keşif filtresi değildir. E-ticaretle ilişkili işlevleri gerektiğinde tek modülde grupla. En fazla iki güçlü iş kalemini kısa gerekçeyle `README.md` içinde güncelle.

Kullanıcının sohbetlerde belirttiği ihtiyaçlar da keşif girdisidir. Yeni bir öneri geldiğinde `README.md` içindeki **Bekleyen değerlendirme girdileri** listesine kaydet; öneriyi tek başına adayın doğruluğuna dair kanıt sayma. Sonraki tarama, katalog ve yönerge imzaları değişmese bile bu girdileri modüllerin kayıtlı kapsam değerlendirmeleriyle karşılaştırır. Yalnız ilgili alanı belirsiz bırakan rules/skills dosyalarını ve gerekirse ilgili kodu incele. Her girdiyi doğrulanmış aday, mevcut modülde karşılanıyor veya ek kanıt bekliyor durumlarından birine gerekçeyle geçir; sessizce silme ya da atlama.

### 2. Ayrıntılı uygulanabilir plan — 6.1 SOL, low

Seçilen her modül veya mevcut özellik geliştirmesi için `Modules/Plans/<İş Kalemi>/` klasörü oluştur ve `<İş Kalemi> - Yeni.md` planını yaz. İş tamamlandığında `<İş Kalemi> - Tamamlandı.md` olarak yeniden adlandır. İlgili tasarım görselini yalnız UI değişikliği gerektiren işlerde üret ve aynı klasörde tut. Plan, uygulayıcının doğrudan takip edebileceği görev sırası ve tamamlanma koşulları içersin. Şunları kapsasın:

- Aday gerekçesi, iş sınırı ve mevcut modüllerle ilişkisi; varsa sınır/karar soruları.
- CMS modül kurulum sözleşmesi: paket metadata'sı, `features.json`, route/izin/menü, migrations, açık install seeder'ları, asset yayını, bağımlılık uyumluluğu, etkinlik/kapatma/kaldırma davranışı ve ortak kurulum servisleri.
- Gerekli admin ve frontend akışları; kullanıcı hataları ve modül eksik/kapalı durumları.
- Eşzamanlı istekler, transaction/izolasyon, idempotency, veri snapshot'ları ve telafi/hata davranışları için tutarlılık hedefleri. Uygun yöntemi seçme sorumluluğu uygulayıcıdadır; belirli mimari kalıp, interface, repository veya ek bağımlılık dayatılmaz.
- Yalnız ilgili davranış ve önemli hata durumlarını kapsayan en küçük anlamlı test listesi. Proje kurallarıyla çelişen test ekleme/çalıştırma talimatı yazma.
- Uygulama tamamlanınca test/build sonuçlarını ve yerel değişiklikleri aynı görev kaydına rapor etme biçimi.

Yalnız ilk tam baseline sweep'i tüm modüllerin rules/skills dosyalarını derinlemesine okur. Baseline'da katalog entry signature'ı ve her ilgili rules/skills dosyasının path/SHA-256 imzasını [Keşif Durumu](Ke%C5%9Fif%20Durumu.json) dosyasına yaz; ayrıca her modül için kısa kapsam/rol özeti tut. Sonraki run'larda küçük `catalog.json` girdilerini ve dosya imzalarını kontrol et; yalnız yeni modülü veya katalog metadata/rules/skills imzası değişen modülü derin oku. Değişmeyen yönergeleri veya kodu yeniden okuma; bunun yerine CMS'nin amacı sorusunu tüm kayıtlı modül kapsam özetleri ve açık aday girdileri üzerinden her çalışmada yeniden değerlendir. Böylece keşif yalnız imza değişikliklerine bağlı kalmaz. Her modülün önceki değerlendirmesini indekste koru; yeni kanıt eski sonucu değiştiriyorsa README/planı güncelle ve nedeni görev günlüğüne kaydet. Bekleyen sohbet girdilerini her çalışmada değerlendir; imza değişikliği yok diye atlama. Bekleyen girdi de yoksa bile genel CMS kapsam boşluklarını kayıtlı kapsam haritasına göre kısaca yeniden düşün; yeni kanıt veya anlamlı açık yoksa aday üretme. İlgili kod yalnız değişen yönergelerin veya açık aday girdisinin bıraktığı belirsizliği çözmek için okunur.

Planlama tamamlanması uygulama veya yayın yetkisi değildir. Öncelik, kapsam ve mimari belirsizlikleri planın içinde işaretle; kod değişikliğine geçme.

#### Harici API ve bulunmayan altyapılar için kullanıcı onayı

Onay kayıtlarının güncel listesi ve durum geçmişi [Onay Gerektiren İşler](Onay%20Gerektiren%20İşler.md) dosyasında tutulur.

Her modül planında dış servis/API veya projede henüz bulunmayan bir altyapı (ör. ödeme sağlayıcısı, kargo/taşıyıcı servisi, SMS/e-posta sağlayıcısı) gerektiren işleri ayrı bir **Onay bekliyor** bölümünde listele. Her madde için belirt: hangi servis/hesap/anahtar veya ürün kararı gerekiyor, onay gelene kadar hangi yerel kapsam uygulanabilir ve onay gelmezse hangi iş ertelenir.

6.1 SOL entegrasyonun kendisini onaylanmış varsaymaz ve TERA'ya uygulanabilir çekirdek kapsamı ayrı gösterir. 5.6 TERA, kullanıcı bu belirli entegrasyon/kapsam için açık onay vermedikçe sağlayıcı API'sine bağlanmaz, gerçek callback/webhook veya canlı dış işlem akışı geliştirmez; gizli anahtar/hesap bilgisi uydurmaz ve entegrasyonu tamamlanmış gibi gösteren sahte bir bağlantı kurmaz. Yerel veri modeli, sağlayıcıdan bağımsız sipariş durumu ve test fake'leri gibi entegrasyondan bağımsız işler ancak planda tanımlıysa yapılabilir; gerçek ödeme/kargo başlatma ya da gönderi oluşturma yapılmaz. Onay gelmezse bu entegrasyon maddeleri `waiting_approval` olarak kalır; bağımsız ve onaylı yerel kapsam ayrıca tamamlanabilir.

Onay verilirse yalnızca onaylanan servis ve işlevler açılır; servis seçimi, kimlik bilgisi saklama, webhook güvenliği, hata/tekrar deneme ve maliyet/operasyon etkileri planlanır. Onay, release veya GitHub yayını talebi anlamına gelmez; yayın kapısı ayrıca geçerlidir.

### 3. Form tasarım konsepti — ImageGen

Aday seçilip form gereksinimleri yazılı plana alındıktan sonra ImageGen, modülün ekle/düzenle formu için tasarım konsepti üretir. Store örneğinde:

- Görsel referans: `output/playwright/blog-form-desktop.png` (mevcut Blog admin form ekranı).
- İçerik kaynağı: [Store planındaki sekme ve alan gereksinimleri](Store/Store%20-%20Tamamland%C4%B1.md#admin-ürün-oluşturmadüzenleme-formu).
- Formun sekme/alan grupları görselde görünür olmalı; mevcut admin panelinin Tabler yerleşimi, sidebar ve renk/boşluk dili referans alınmalı.
- ImageGen çıktısı konsepttir. Kaynak görseldeki alanlar yeni modül için otomatik gereksinim sayılmaz; uygulayıcı plan ve mevcut bileşenleri esas alır.
- Tasarım çıktısı adayın `Modules/Plans/<Aday>/` klasörüne kaydedilir ve ayrıntılı plan içinden bağlanır. Yalnız tasarım görselinin üretimi kod uygulamasının tamamlandığı anlamına gelmez.

### 4. Modül uygulaması ve dar doğrulama — 5.6 TERA, medium

5.6 TERA medium, ayrıntılı planı ve ImageGen konseptini devralır. Uygulama sırası:

1. Uygulamaya başlamadan `.ai/rules/index.md` ve kapsamdaki kuralları, `.ai/rules/moduller.md` ile `.ai/workflows/modul-gelistirme.md` dahil, oku; `grep -rin '<konuyla ilgili anahtar sözcük>' .ai/rules` ile ek eşleşmeleri bul.
2. Paket/Laravel API'si gerekiyorsa sürümleri doğrula ve proje talimatındaki resmi dokümantasyon aramasını kullan.
3. Kodlamadan önce [modül geliştirme workflow'undaki mevcut davranışları keşfetme ve doğrulama adımlarını](../../.ai/workflows/modul-gelistirme.md#mevcut-davranışları-keşfetme-ve-doğrulama) uygula: önce en yakın bir örnek modülü incele; yeterli değilse ikinci, gerekirse üçüncü modülü yalnız eksik konu için karşılaştır. Ortak JS/form bağlantılarını, veri sahipliğini ve kurulum/etkinleştirme yollarını koddan doğrula. Ardından modülü ilgili mevcut örüntülerle oluştur; planın CMS kurulum/izin/asset ve form gereksinimlerini tamamla. Seçilen mimari yöntemi gereksinim ve kurulum sınırlarıyla gerekçelendir; kalıp dayatma.
4. Admin ekle/düzenle formunu konsept ve planla uyumlu uygula. Sekmeler klavye/mobil kullanımında erişilebilir, validasyon hataları doğru sekmede görünür ve alan değerleri güvenli biçimde korunur.
5. Yalnız ilgili özellik ve önemli hata yollarını kapsayan en dar mevcut testleri çalıştır. PHP değiştiyse Pint; modül asset'i değiştiyse gereken derleme ve frontend görünüm kontrolünü proje kurallarına göre tamamla. Başarısız kontrolü gizleme; tamamlanana kadar yerel uygulama işini bitmiş sayma.
6. Başarılı kod/test/build sonrası modül kaynağını farkları koruyarak `D:\localhost\ekAgentModules\<Modul>` konumuna kopyala. Bu kaynak staging adımı otomasyonla keşfedilen ve kullanıcı tarafından başlatılan modüller için aynıdır; push veya yayın değildir.
7. [Görev Günlüğü](Görev%20Günlüğü.md) kaydını test/build sonuçları, staging hedefi ve tamamlanma zamanı ile güncelle; kilidi serbest bırak. Aynı görev kaydında değişen dosyaları, mimari kararı, bilinen sınırları ve tamamlanma durumunu raporla.

Uygulama öncesi plandaki **Onay bekliyor** maddelerini kontrol et. Açık kullanıcı onayı bulunmayan entegrasyonları kapsam dışı tut ve görev günlüğünde `waiting_approval` olarak kaydet; bağımsız yerel kapsam varsa onu ayrıca bitirebilirsin. Tamamlanma raporu, ertelenen entegrasyonları açıkça saysın.

## Release ve dağıtım kapısı

Uygulama başarısı sonrası staged kaynak kopyalama normal tamamlanmadır; bu adım push etmez. GitHub'a push, Release/asset, ortak `catalog.json` güncellemesi ve yayın yalnızca kullanıcı açıkça talep ettiğinde yürütülür; modülün otomasyonla keşfedilmiş olması veya uygulamanın tamamlanması bu talebin yerine geçmez. Açık talep gelirse staged kaynağı doğrula, sonra `.ai/rules/moduller.md` içindeki mevcut release/dağıtım adımlarını ve `.ai/workflows/modul-yayinlama.md` rehberini uygula. Kaynak commit'i, release ZIP'i, tag/asset, gerçek URL/hash ile katalog kaydı ve push sırasını koru; doğrulanmamış yayın adresini başarılı diye raporlama.

GitHub araçlarını/API'yi o görev sırasında denetle. Bağlı entegrasyon metin/dosya/commit yeteneklerini sağlarken Release oluşturma veya ZIP asset yükleme sunmuyorsa authenticated `gh` CLI ya da GitHub REST API'yi tercih et. API ve CLI kullanılamazsa Playwright yalnız gerekli GitHub web arayüzü adımlarında yedektir. Auth, korumalı branch veya başka dış engel varsa son başarılı adıma kadar olan durumu raporla; sırları kaydetme.

## Günlük zamanlama ve çakışma önleme

- Otomasyon her gün Türkiye saatiyle 00:15'te bir kez başlar. O gün için en fazla iki uygun yeni modül işi yürütür.
- İlk iş 00:15'te başlar. İkinci işin başlangıcı 00:45'ten önce olamaz; ilk iş 00:45'te sürüyorsa ikinci iş, ilk iş tamamlandıktan sonra başlar. İki modül işi hiçbir zaman paralel yürütülmez.
- Etkin görev kilidi veya devam eden uygulama/yayın varsa yeni modül işi başlatılmaz. Aynı günlük çalışmanın ikinci işi için ayrıca iki saatlik bekleme uygulanmaz.
- Günlük ikinci iş tamamlanmadan bir sonraki günün otomasyonu çalışırsa etkin işi kontrol eder; çakışan iş başlatmaz. Tamamlanmamış işleri görev günlüğünde açık tutar.
- Discovery yalnız yeni ve ilgili kurallarla doğrulanmış adayları ekler; aynı aday veya ayrıntılı plan tekrar yazılmaz.
