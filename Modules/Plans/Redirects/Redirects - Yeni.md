# Redirects — Uygulama Planı

**Durum:** plan hazır; uygulama engelli (`gpt-6.1-terra, medium` bu oturumda seçilemiyor)
**Sınıf:** mevcut özellik iyileştirmesi / olası bağımsız, opsiyonel URL yönlendirme modülü  
**Güven:** orta-yüksek — kullanıcı kapsamı doğrudan açıkladı; kırık bağlantı riski CMS kullanımından çıkarımdır.  
**Kaynak konsept:** [Redirects-Form-Konsept.png](Redirects-Form-Konsept.png)

## Gerekçe ve kapsam sınırı

Kullanıcı eski URL yollarını yeni URL yollarına elle yöneten, aynı site içinde çalışan kalıcı 301 kuralları istedi. Kullanıcı açıklamasına göre modül önekli route'lar aktif modül route adlarıyla çakışmaz. Pages catch-all davranışının yönlendirmeyi yutmaması için gerçek request-hook/route sırası uygulama öncesi koddan doğrulanmalı.

Katalogdaki Pages, Blog, Products ve Store kendi içerik URL/slug'larını yönetiyor; ortak eski-yeni yol eşleştirme kapsamı katalog rolleri ve kayıtlı rules/skills içinde belgelenmiyor. Redirects yalnız yönlendirme kayıtlarının sahipliğini ve istek başına uygulamasını üstlenir. Mevcut içeriklerin slug değişimini otomatik izlemek, içerik modüllerine yazmak, wildcard/regex kuralları, alan adı değişimi, proxy/CDN ayarı veya arama motoru denetim araçları bu ilk sürümde yoktur.

İlk sürüm aynı uygulama host'unda iki normalize edilmiş site-içi path arasında elle tanımlı 301 yönlendirme sunar. Dış URL kabul edilmez. Hedefin başka bir Redirects kaynağına dönmesi döngü oluşturacağından kaydetme doğrulaması ve runtime üst sınırı gerekir. Bir kaynak path için yalnız bir kayıt bulunur; güncelleme mevcut kaydı düzenler.

## Kurallar ve kurulum sözleşmesi

- `.ai/rules/index.md`, `.ai/rules/moduller.md`, `.ai/rules/mimari-kod-yapisi.md`, `.ai/rules/admin-arayuz.md`, `.ai/rules/yetkilendirme.md` ve `.ai/workflows/modul-gelistirme.md` uygulama öncesi okunmalı; eşleşen ek kural anahtarları `.ai/rules` içinde taranmalı.
- Composer/Laravel veya frontend API'sine bağlı karar öncesi `composer show --direct`, `package.json` ve Laravel Boost `search-docs` ile kurulu sürüm doğrulanmalı.
- Uygulayıcı önce bir yakın opsiyonel admin modülünü ve çekirdek request/route girişini inceleyerek paket metadata, `features.json`, admin route/izin/menü, migration, kurulum/etkinleştirme/devre dışı bırakma/kaldırma, panel asset'leri ve servis provider kayıtlarını projenin mevcut sözleşmesine göre kurmalı. Yeni bağımlılık eklenmemeli.
- İzin tüm kayıt işlemlerinde sunucu tarafında doğrulanmalı; menü gizleme yetkilendirmenin yerine geçmez. Modül kapalıysa admin route ve frontend redirect resolver çalışmamalı.
- Kurulum idempotent olmalı. Kuralların silinmesi veya modülün devre dışı kalması varsayılan olarak kuralları silmemeli; uninstall davranışı mevcut modül sözleşmesi ve veri koruma kuralına uymalı.

## Veri sahipliği ve ilişkili modüller

Redirects kendi yönlendirme kayıtlarının sahibidir. Başlangıç alanları: normalize edilmiş `source_path`, normalize edilmiş `destination_path`, `status_code` (ilk sürümde sabit 301), `is_active`, isteğe bağlı `description`, oluşturma/güncelleme zamanları ve mevcut kurulumda kullanıcı ilişkisi standardı varsa `created_by`/`updated_by`. İçerik kaydı veya URL sahibi yabancı anahtarı eklenmez.

Kaynak path için veritabanı unique constraint'i eşzamanlı yinelenen kayıtları engeller. Kaydetme ve edit işleminde aynı kanonikleştirme yordamı kullanılmalı. Leading slash zorunlu kılınmalı; query/fragment ve host girdisi reddedilmeli; nokta segmentleri, çift slash ve kodlanmış ayraçların davranışı açıkça normalize edilip test edilmeli. Destination path aynı kurallardan geçer. Self-redirect ve kayıtlı zincir döngüsü reddedilir.

## İstek ve yönetim akışı

1. Admin listesi arama, aktif/pasif filtresi, pagination ve create/edit/toggle/delete işlemleri sunar. Silme onaylı ve yetkili işlemdir.
2. Form konseptindeki alanlar kullanılır: eski yol, yeni yol, sabit `301 Kalıcı`, aktiflik ve opsiyonel açıklama. Kaydetme düğmesi esnasında doğrulama hataları ilgili alan yanında gösterilir.
3. Ziyaretçi isteğinde, Pages catch-all'den önce bağlandığı doğrulanmış request hook yalnız GET/HEAD istekleri için aynı host'taki path'i normalize eder ve etkin tam eşleşmeyi arar. Redirect yanıtı `Location` ile 301 döner; query string korunacaksa güvenli politika tek yerde uygulanır ve test edilir. POST/diğer method'lar değişmeden devam eder.
4. Redirects devre dışı veya modül kapalıysa resolver hiçbir sorgu/yanıt değişikliği yapmaz. İstek başına yinelenen DB sorgusunu önlemek için mevcut cache yaklaşımına uygun kısa süreli cache kullanılabilir; kayıt değişimi, toggle, silme ve modül yaşam döngüsünde ilgili cache invalidate edilir. Cache kullanımı doğruluk garantisini bozmamalı.

## Tutarlılık, yarışlar ve hata davranışı

- Unique kaynak constraint'i son doğruluk kapısıdır; eşzamanlı çakışmada kontrollü validation mesajı dönülür.
- Bir kaynak/hedef değişikliğinde döngü kontrolü ve yazma transaction içinde, eşzamanlı değişiklikleri yakalayacak şekilde yapılır. Kullanılan DB kilitleme/izolasyon yöntemi kurulu motor ve uygulama örüntüsüyle uyumlu seçilir.
- Çalışma anında yönlendirme zincirleri takip edilmez: hedef başka bir kaynağa eşleşirse tek hop davranışı/loop riskini güvenli biçimde engelle. Resolver hiçbir durumda sınırsız tekrar yapmamalı.
- Hatalı veya şüpheli path 4xx validation sonucu almalı; harici hedefe redirect, open redirect veya Pages fallback'i maskeleme olmamalı. Beklenmeyen resolver hatası içeriği 500'e çevirmemeli, loglanmalı ve normal route akışı sürmeli.
- Query string aktarımı açık bir sabit politikaya bağlanmalı; fragment HTTP request'e gelmediğinden saklanmamalı. Host header hedef üretiminde kullanılmamalı.

## Dar doğrulama planı

- Modül feature testleri: yetkisiz CRUD reddi; yetkili ekle/düzenle; kaynak yinelenmesi; normalize edilen eşdeğer path çakışması; dış URL, self-loop, redirect cycle ve kötü path reddi.
- HTTP testleri: etkin GET/HEAD için tek hop 301 ve doğru Location; query string kararı; POST etkilenmemesi; pasif kayıt ve modül kapalıyken redirect olmaması; Pages catch-all ile çakışma sırası.
- Yarış/tutarlılık için mümkün olan en dar DB testi: unique constraint ihlali kontrollü sonuç verir; toggle/update sonrası resolver eski cache'i kullanmaz.
- PHP değişince ilgili test ve `vendor/bin/pint --dirty --format agent`; asset değişince gerekli modül asset derlemesi ve `npm run build`. İlgili dar test/build başarılı olmadan tamamlandı denmez.
- Başarıdan sonra yalnız Redirects kaynak klasörü `D:\localhost\ekAgentModules\Redirects` konumuna kopyalanıp dosya hash'leri karşılaştırılır. Push, release veya katalog güncellemesi yapılmaz.

## Tamamlanma koşulları

- Modül mevcut kurulum sözleşmesine uygun kurulur, opsiyonel ve yetki korumalı çalışır.
- Kullanıcı kapsamındaki aynı-host path 301 davranışı, duplicate/döngü/race/hatalı-girdi durumları ve Pages catch-all etkileşimi dar testlerle doğrulanır.
- Pint ve gerekiyorsa frontend build başarılı olur; yeni modül kaynağı staging'e hash doğrulamasıyla kopyalanır.
- Plan `<Redirects - Tamamlandı.md>` olarak yeniden adlandırılır; görev günlüğü test, build, değişen dosya ve staging sonuçlarıyla kapatılır.

## Onay bekliyor

Harici servis, API veya yeni altyapı gerekmiyor. İlk sürüm aynı uygulama host'undaki manuel 301 kayıtlarıyla sınırlıdır.

## Uygulama engeli

İş akışı uygulama aşamasında kullanıcının seçtiği `gpt-6.1-terra, medium` modelini gerektiriyor. Bu oturum model yönlendirmesini seçemedi; bu nedenle uygulamaya farklı modelle geçilmedi ve belirtilen model kullanılmış gibi gösterilmiyor. Otomasyonun sonraki çalışmaları için uygulayıcı modeli talimatı güncellendi. Model yönlendirmesi kullanılabilir olduğunda bu plan ve konsept hazırdır. Görsel konsept tüm ekranlar için zorunlu tasarım spesifikasyonu değildir.
