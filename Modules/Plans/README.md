# Modül adayları ve planlar

Bu klasör, modül kataloğundaki görünür kapsam boşluklarının özetini ve seçilen adayların uygulanabilir planlarını tutar. Planlama ve uygulama ayrı aşamalardır. Buradaki dokümanlar tek başına modül kodu, release, katalog güncellemesi veya tasarım görseli üretmez.

## Görünür adaylar

| Öncelik | Aday              | Neden gerekli                                                                                                                                                                                                                                      | Durum                                                  | Ayrıntılı plan                |
| ------- | ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ | ----------------------------- |
| 1       | Store (e-ticaret) | Katalogda `Products` vardır; modülün kendi tema kuralı fiyat, stok, varyant, sepet, sipariş ve ödemeyi açıkça kapsam dışında bırakır. Satış yapan kurulumların ürün satışını uçtan uca yönetmesi için bu işlevlerin tek modülde sunulması gerekir. | **Yerel uygulama tamamlandı** — ürün/varyant/medya/ayar/sepet/sipariş ve yönergeler doğrulandı; harici ödeme/kargo onayı ayrı. | [Store klasörünü aç](Store/Store%20-%20Tamamland%C4%B1.md) |
| 2 | Şubeler/Bayiler | Çoklu şube ve bayi adres/iletişim kayıtları; Türkiye Konum modülüne bağımlı. Tek modül, kayıt kategorileriyle ayrılır. | **Yerel uygulama, test/build ve kaynak staging tamamlandı** — Konum katalogda kayıtlı; Branches için release/push yapılmadı. | [Branches planı](Branches/Branches%20-%20Tamamland%C4%B1.md) |

Kullanıcı Kariyer/İş İlanları ve Etkinlikler önerilerini tek birleşik Şubeler/Bayiler modülüyle değiştirdi; Şube/Bayi kategorileri aynı kayıt modelinde ve Türkiye Konum bağımlılığıyla uygulandı.

Bu liste, belgelenmiş kapsam boşlukları ile CMS amacı ve kullanıcı tercihinden türetilen önerileri birlikte gösterir; güven düzeyi ve seçilme durumu ayrı belirtilir.

## İki önerilik keşif sonucu — 21 Ekim 2026

Bu öneriler mevcut modül rolleri ve CMS amacı üzerinden çıkarımdır; doğrulanmış müşteri talebi veya uygulama kararı değildir.

| Öneri | İlk kapsam | Dayanak ve güven | Durum |
| --- | --- | --- | --- |
| Randevu | Hizmet/zaman seçimi, randevu talebi, müsaitlik ve talep yönetimi | Services hizmet kataloğudur; randevu iş akışı belgelenmiyor. Hizmet işletmeleri için ihtiyaç çıkarımı; orta güven. | Kullanıcının isteğiyle yalnız plan görüşü hazırlandı; kapsam kararları açık, kodlama başlatılmadı. |
| Şubeler/Bayiler | Çoklu konum, adres, telefon, çalışma saatleri, harita bağlantısı, aktiflik/sıralama | Settings tekrarlanan kayıtları ayrı modüle yönlendiriyor; Brand marka, Sector sektör tanıtımı yapıyor. Çok konumlu işletmeler için ihtiyaç çıkarımı; orta güven. | Öneri olarak kayıtlı; planlama ve uygulama başlatılmadı. |
## Tek seferlik keşif — 22 Ekim 2026

Aşağıdaki iki öneri 16 modülün baseline rol kapsamlarından çıkarılmıştır. Müşteri talebi kanıtı değildir; kullanıcı değerlendirmesi bekleniyor, planlama başlamadı.

| Öneri | İlk olası kapsam | Mevcut rollerden farkı ve dayanak | Güven / durum |
| --- | --- | --- | --- |
| Kariyer ve İş İlanları | Pozisyon ilanı, başvuru ve değerlendirme aşamaları | Team mevcut çalışan/uzman tanıtımı yapıyor; ilan ve aday süreci belgelenmiyor. | Kullanıcı tercihiyle Şubeler/Bayiler lehine değiştirildi; aktif öneri değil. |
| Etkinlikler | Tarih/saat/yer içeren etkinlik kayıtları ve ziyaretçiye duyuru | Blog haber/duyuru içeriklerini kapsıyor; etkinlik takvimi ve tarih odaklı iş akışı belgelenmiyor. | Kullanıcı tercihiyle Şubeler/Bayiler lehine değiştirildi; aktif öneri değil. |

Kariyer için Team rule/skill; Etkinlikler için Blog ve Services rule/skill incelendi. Kod incelemesi yapılmadı. KVKK/KBKK girdisine dair yeni kanıt çıkmadı; kapsam doğrulaması bekliyor.

## Kullanıcı tercihi — 22 Ekim 2026

Kariyer/İş İlanları ve Etkinlikler önerileri yerine tek bir **Şubeler/Bayiler** modülü planlanacak. Şube ve bayi kayıtları aynı modülde yer alacak; gerekirse kayıt kategorisiyle ayrılacak. Kullanıcı talimatı: bu modül **Türkiye Konum (Konum)** modülüne bağlı çalışmalı.

Konum modülü ortak depo klasöründe bulunuyor; kural/skill adres hiyerarşisi ve aktif/tutarlı seçim servisini tarif ediyor. Mevcut `catalog.json` içinde Konum kaydı yok; dağıtılabilir bir modül bağımlılığı olarak kullanmak için katalog kaydı/yayın kararı ileride ayrıca gerekir. Bu planlama aşamasında katalog değiştirilmez ve yayın yapılmaz.

## Bekleyen değerlendirme girdileri

Sohbetlerde kullanıcı tarafından belirtilen ihtiyaçlar, henüz doğrulanmış modül adayı olmasalar bile kalıcı olarak burada tutulur. Her keşif çalışması, katalog veya rules/skills imzaları değişmemiş olsa da bu açık girdileri mevcut modüllerin kayıtlı değerlendirmeleriyle karşılaştırır. Bunlar sonuç değil, araştırılacak hipotezlerdir; inceleme sonunda ayrı modül gerektirmediği anlaşılırsa gerekçesiyle kapatılır.

| Aday girdisi | Kaynak ve kısa gerekçe | Durum | Sonraki inceleme |
| --- | --- | --- | --- |
| KVKK ayarları | Kaynak notundaki kullanıcı ifadesi “KBKK ayarları”; olası “KVKK ayarları” olarak değerlendirildi. Settings tekil/sabit ayarları yapılandırabilir, Pages politika metni yayımlayabilir; operasyonel rıza, çerez tercihi, veri sahibi başvurusu veya saklama akışı ihtiyacı henüz kanıtlanmadı. | **Ek kanıt bekliyor** — Settings alanları ve Pages gizlilik metnini karşılayabilir; bağımsız rıza/veri sahibi/saklama iş akışı kanıtı yok, yeni modül doğrulanmadı. | KVKK/KBKK yazımını ve istenen işlevleri netleştir: yalnız gizlilik metni/ayar mı, yoksa çerez rızası kaydı, veri sahibi başvurusu, saklama/anonimleştirme gibi tekrarlanan iş akışları mı? Yeni modül ancak bağımsız iş akışı ve kullanıcı ihtiyacı doğrulanırsa planlanır; yasal kapsam varsayma. |
| URL Yönlendirme Yönetimi | Kullanıcı kapsamı: aynı host içinde elle tanımlanan eski path → yeni path kalıcı 301; site-içi içerik URL'leri Pages/Blog/Products/Store'da, merkezi yönlendirme kuralı rol haritasında yok. Kırık bağlantı/SEO koruma değeri ürün çıkarımıdır. | **Plan hazır; uygulama model seçimi bekliyor.** Güven orta-yüksek (kapsam için), değer çıkarım. [Redirects planı ve konsept](Redirects/Redirects%20-%20Yeni.md). | Uygulama öncesi Pages catch-all/request-hook sırasını ve kurulu route davranışını doğrula; gpt-5.6-terra medium yönlendirmesi seçilebilir olmalı. |

### İlk tam baseline taraması

İlk discovery yalnız Store/e-ticaret kapsamıyla sınırlı değildir. 21 Ekim 2026 taramasında katalogdaki 16 modülün ilgili rules/skills dosyaları incelendi; katalog ve bu yönergelerle doğrulanan mevcut kapsamlar üzerinden bütün aday alanlar değerlendirildi. Store, Products kurallarında fiyat, stok, varyant, sepet, sipariş ve ödemenin açıkça kapsam dışı bırakılması nedeniyle güçlü aday olarak korundu. Bu taramada ayrı bir modül planını haklı çıkaran başka yeni ve yeterince doğrulanmış aday bulunmadı. Genel iletişim başvuruları çekirdek Forms akışında, sayfa/içerik/SEO alanları mevcut içerik modüllerinde ele alındığından yalnız adının bulunmaması yeni modül gerekçesi sayılmadı. Store bu baseline için başlangıç hipoteziydi; keşif kapsamını daraltmaz.

## Keşif ve uygulama akışı

Model görev devri ve sıralı adımlar için [İş Akışı](İş%20Akışı.md) belgesini izle.

Otomasyonla keşfedilen ve kullanıcı tarafından başlatılan her discovery/modül işi [Görev Günlüğü](Görev%20Günlüğü.md) dosyasına kaydedilir. Bu dosya etkin kilit, son tamamlanma zamanı, test/build, kaynak staging ve varsa release durumunun tek yetkili kaynağıdır.

Tam baseline ve sonraki katalog/rules/skills imzaları [Keşif Durumu](Ke%C5%9Fif%20Durumu.json) içinde saklanır. Günlük scheduler ilk çalışmada tüm katalog kapsamını okur; sonraki günlük çalışmalarda küçük indeksi karşılaştırır ve yalnız yeni/değişen modüllerin yönergelerini derinlemesine inceler. İncelenmiş ve değişmemiş yönergeler yeniden okunmaz. Bunun yanında bu README'deki tüm **bekleyen değerlendirme girdileri** her çalışmada, değişmeyen modüllerin önceden kaydedilmiş kapsam değerlendirmeleri kullanılarak yeniden ele alınır. Yeni bir sohbet girdisi geldiğinde hemen bu listeye eklenir; sonraki taramanın onu atlaması imza değişikliği şartına bağlı değildir.

Keşfin ana sorusu şudur: **Bu CMS'nin amacı ve kullanıcı ihtiyaçlarına göre, bulunması gereken ancak hiçbir mevcut modülün karşılamadığı bağımsız modül nedir?** Sistem her çalışmada mevcut modüllerin kayıtlı rol ve kapsam haritasını kullanarak bu soruyu yeniden değerlendirir; yalnız değişen modüllerin kurallarını derinlemesine yeniden okur. Sonuç, yeni modül ile mevcut modül genişletmesini ve onay gerektiren entegrasyonu birbirinden ayırır.

1. Günlük scheduler Türkiye saatiyle 00:15'te çalışır. Önce [Görev Günlüğü](Görev%20Günlüğü.md) kilidini ve [günlük rapordaki](G%C3%BCn%20Sonu%20Raporu.md) bugünkü modül sayısını kontrol eder. Etkin kilit varsa veya günlük iki-modül sınırına ulaşıldıysa yeni iş başlatmaz. Günlük çalışmanın ikinci işi için eski iki saatlik bekleme kuralı geçerli değildir.
2. Keşif 6 Luna low ile yürütülür. İlk tam baseline keşfi katalogdaki tüm modülleri kapsar: önce `D:\localhost\ekAgentModules\catalog.json`, ardından her modülün ilgili `.ai/rules/` ve `skills/` dosyaları; belirsizlik devam ederse yalnız ilgili kod. Store seed adayı bu sırada kanıtla yeniden değerlendirilir; e-ticaret tarama filtresi değildir.
3. Baseline sonrasında her run katalog metadata'sı ile her modülün ilgili rules/skills dosyalarının ucuz SHA-256 imzalarını [Keşif Durumu](Ke%C5%9Fif%20Durumu.json) ile karşılaştırır. Derin okuma yalnız yeni katalog kaydı veya metadata/rules/skills imzası değişen modüllerde yapılır; değişmeyen modüllerin kuralları ve kodu yeniden okunmaz.
4. Değişen yönergeler önceki analizi etkiliyorsa aday/plan güncellemesini ve gerekçeyi görev günlüğüne ekle. Değişiklik yoksa bile bekleyen değerlendirme girdilerini önceki modül kapsam kayıtlarıyla incele; her girdiyi doğrulandı, mevcut modülle karşılanıyor veya ek kanıt bekliyor olarak gerekçeli biçimde güncelle. Bekleyen girdi kalmadıysa ve kaynaklarda değişiklik yoksa yeni aday olmadığını kaydet.
5. Keşif sonucu bu özet dosyasında listelenir. Her yeni aday için `Modules/Plans/<Aday>/` klasörü oluştur; durum etiketi taşıyan ayrıntılı plan (`<Aday> - Yeni.md`, tamamlanınca `<Aday> - Tamamlandı.md`) ve ilgili tasarım/çıktı görsellerini bu klasörde tut. Özet satırı doğrudan bu klasördeki güncel plan dosyasına bağlanır.
6. Keşif 6 Luna low, ayrıntılı plan 6.1 SOL low tarafından yapılır; gerekli form konsepti ImageGen ile üretilir; kod uygulaması 5.6 TERA medium'a devredilir. Günlük çalışma 00:15 Türkiye saatinde başlar. İlk modül işi bittiğinde ikinci iş 00:45'ten önce başlamaz; 00:45'te ilk iş sürüyorsa ikinci iş ancak ilki tamamlandıktan sonra başlar. Günde en fazla iki yeni ve henüz tamamlanmamış modül sırayla ele alınır. Onay bekleyen dış entegrasyonlar uygulanmaz. İki uygun aday yoksa sayı tamamlamak için aday uydurulmaz; durum rapora yazılır.
7. Otomasyonla keşfedilen ve kullanıcı tarafından başlatılan her modül işi başlangıçta, aşama devrinde ve tamamlandığında [Görev Günlüğü](Görev%20Günlüğü.md) içinde güncellenir. Kod, frontend, hata yolları ve doğrulamalar tamamlanınca kaynak `D:\localhost\ekAgentModules\<Modul>` konumuna staged kopya olarak aktarılır. Bu kopya push değildir. Release, katalog ve yayın yalnızca kullanıcı açıkça istediğinde başlar.
8. Günlük çalışma sonunda [Gün Sonu Raporu](G%C3%BCn%20Sonu%20Raporu.md) dosyasına o tarihte tamamlanan modülleri tabloyla ekle: tarih, modül, geliştirilme nedeni ve sonuç. İş tamamlanmadıysa tamamlandı diye listeleme; o gün uygun aday yoksa bunu kısa bir not olarak ekle. Önceki günlerin raporlarını silme.

## Modül tamamlanması ve release sınırı

Uygulama görevi `.ai/rules/moduller.md` ile `.ai/workflows/modul-gelistirme.md` kurallarına uyar. Başarılı doğrulamadan sonra kaynak staging normal tamamlanmadır. ZIP, GitHub Release, ortak katalog güncellemesi, doğrulama ve push yalnızca açık release talebi üzerine yürütülür. Store'un yerel uygulaması tamamlandı ve staging yapıldı; ödeme/kargo API'leri açık onay olmadığı için ertelendi.

### Release görev devri

Uygulama/test/build başarılı olunca kaynak `D:\localhost\ekAgentModules\<Modul>` konumuna kopyalanır ve görev kaydına kaynak yolu ile doğrulama sonuçları yazılır. Bu staging normal tamamlanmanın parçasıdır; push veya release yapmaz. Kullanıcı açıkça release isterse staged kaynak doğrulanır, ZIP hazırlanır, kaynak push edilir, GitHub Release/asset yayımlanır, katalog güncellenip push edilir ve uzak içerik doğrulanır. Önceki modül işinin cooldown saati release istenmişse bu zincir bütünüyle tamamlandığında, istenmemişse uygulama/staging/doğrulama işi tamamlandığında başlar. GitHub auth, korumalı branch veya API/CLI eksikliği varsa kaldığı aşamayı açıkça kaydet; aynı iş tamamlanmış sayılmaz ve çakışan yeni keşif başlatılmaz.

GitHub entegrasyonu repo ve metin dosyası işlemlerini destekleyebilir; release ve ZIP gibi ikili asset işlemleri her bağlantıda bulunmayabilir. Özellikleri çalışma anında denetle. Doğrudan entegrasyon/API veya authenticated `gh`/Git CLI varsa onları kullan; Playwright yalnız gereken GitHub web UI adımları için yedektir.

## Kaynaklar

- Katalog: `D:\localhost\ekAgentModules\catalog.json`
- Ortak modül ve dağıtım kuralları: `.ai/rules/moduller.md`
- Modül geliştirme akışı: `.ai/workflows/modul-gelistirme.md`
- Adayın mevcut Products sınırı: `D:\localhost\ekAgentModules\Products\.ai\rules\tema-entegrasyonu.md` ve `D:\localhost\ekAgentModules\Products\skills\products-theme-integration\SKILL.md`
- Blog formu görsel referansı: `output/playwright/blog-form-desktop.png`


## Kullanıcı girdisi — 23 Ekim 2026

| Aday | Kapsam ve dayanak | Karar / durum |
| --- | --- | --- |
| Log İzleme (LogViewer) | Kullanıcının doğrudan talebi: Laravel log dosyalarını ağaçta seçme, sağ panelde son satırları okuma ve manuel yenileme. Mevcut CMS modüllerinin içerik/kimlik/ticaret rollerinden bağımsız operasyonel sorumluluk. | **Yerel uygulama, test ve kaynak staging tamamlandı.** [LogViewer - Tamamlandı.md](LogViewer/LogViewer%20-%20Tamamland%C4%B1.md). Süper-admin-only, salt okunur; release/push/katalog yapılmadı. |
## 23 Ekim 2026 — kullanıcı tetiklemesiyle modül keşfi

Katalog SHA-256 baseline ile aynı; 18 kayıtlı modülün katalog metadata ve kayıtlı rule/skill imzalarında değişiklik veya eksik dosya yok. Bu nedenle değişmeyen yönergeler yeniden okunmadı. Genel kapsam, kayıtlı rol haritası ve açık girdiler üzerinden yeniden değerlendirildi.

| Öneri | Sınıflandırma ve gerekçe | Güven / karar |
| --- | --- | --- |
| URL Yönlendirme Yönetimi | Olası bağımsız, çapraz içerik modülü. Pages/Blog/Products/Store URL ve slug yönetiyor; merkezi eski-yeni URL yönlendirme kapsamı rol haritasında görünmüyor. Slug değişimlerinde kırık link/SEO kaybı olası kullanıcı ihtiyacı, çıkarımdır. | Orta-düşük; detaylı uygulanabilir plan için otomatik 301 mı/manuel kayıt mı, içerik türleri ve route entegrasyonu netleşmeli. |
| Yönetici Değişiklik Geçmişi | Olası çekirdek yeteneği veya bağımsız denetim modülü. LogViewer uygulama hata günlüklerini okur; editör değişikliklerini kimin yaptığını izlemez. Çok modüllü CMS’de hesap verebilirlik olası ihtiyaçtır, talep kanıtı yoktur. | Düşük; kaydedilecek eylemler, hassas alan redaksiyonu, saklama süresi ve çekirdek/modül sınırı belirlenmeden planlanmadı. |

Bu iki öneri doğrulanmış/uygulama adayı olarak sayılmıyor; sayı tamamlamak için modül veya plan uydurulmadı. Önceden kaydedilmiş Randevu önerisinin kararları hâlâ açık; Branches ve LogViewer tamamlandığından tekrar açılmadı. Kariyer ve Etkinlikler kullanıcı seçimiyle superseded durumda. KVKK girdisi bu turda da Settings tekil ayarları ve Pages gizlilik metni sınırında karşılanabilir; bağımsız rıza/veri sahibi/saklama akışı için ek kullanıcı kanıtı bekliyor.

## 24 Ekim 2026 — günlük modül keşfi

Katalog SHA-256 (`af504ced4fc7282f5addd01dc34457970e0f18892724f3fe95c224087df36219`) baseline ile eşleşti; kayıtlı 18 modülde metadata ve rules/skills imza farkı yok. Bu nedenle değişmeyen yönergeler yeniden okunmadı. Kayıtlı modül kapsamları ve açık öneriler yeniden değerlendirildi.

| Öneri / girdi | Sınıflandırma ve dayanak | Güven / karar |
| --- | --- | --- |
| KVKK ayarları | Mevcut Settings tekil/sabit ayarları ve Pages politika metnini karşılayabilir. Operasyonel rıza/çerez tercihi, veri sahibi başvurusu veya saklama iş akışı ihtiyacı için yeni kanıt yok. | Bağımsız modül doğrulanmadı; kapsam ve ihtiyaç için ek kanıt bekliyor. |
| URL Yönlendirme Yönetimi | İçerik URL/slug değişikliği kaynaklı kırık bağlantı ihtiyacı makul çıkarım; otomatik/manuel davranış, içerik türleri ve route entegrasyonu belirsiz. | Orta-düşük güven; planlanabilir aday değil, kapsam doğrulaması bekliyor. |
| Yönetici Değişiklik Geçmişi | LogViewer uygulama loglarını okur; düzenleme geçmişi ayrı olabilir. Ancak kullanıcı ihtiyacı, çekirdek/modül sınırı, redaksiyon ve saklama koşulları kanıtlanmadı. | Düşük güven; ek kanıt ve kapsam kararı bekliyor. |
| Randevu | Services katalog kapsamı randevu akışını açıklamıyor; ancak talep/kapasite/iptal kararları hâlâ taslak düzeyinde. | Orta güvenli hipotez; kapsam kararı bekliyor, uygulama adayı değil. |

Bugün yeni, doğrulanmış modül adayı bulunmadı. Günde iki aday sınırını doldurmak için öneri uydurulmadı; kod, plan, görsel, test/build veya staging yapılmadı. Bu çalışma model yönlendirme seçeneğine erişemedi; GPT-6-luna low kullanıldığı iddia edilmedi.

## 28 Ekim 2026 — günlük modül keşfi ve Redirects planı

Katalog SHA-256 `933c4a9024f6a590d8806884b2346bbf706e498e64758d408ca4aad38654afc9`; keşif indeksi 23 katalog kaydının metadata ve rule/skill imzalarıyla güncellendi. Önceki indekste olmayan Appointments, Branches, Careers, Events ve LogViewer katalog kapsamı/sahipliği indekse eklendi; yeni Appointments/Careers/Events tema rule+skill dosyaları derin okundu. Store, Branches ve LogViewer tamamlandığından yeniden adaylaştırılmadı.

| Fikir | Sınıf, kullanıcı değeri ve dayanak | Güven / durum |
| --- | --- | --- |
| Redirects — aynı host path 301 | Olası opsiyonel bağımsız modül. Kullanıcı kapsamı manuel path-to-path kalıcı yönlendirme; Pages/Blog/Products/Store URL üretse de kayıtlı rol haritasında redirect kuralları yok. Kullanıcı, modül öneklerinin route isim çakışmasını önlediğini belirtti; Pages catch-all/request-hook sırası kodda doğrulanmalı. SEO/kırık link koruma yararı çıkarımdır. | Orta-yüksek (sınır), orta (ürün yararı). Plan ve form konsepti hazır. Uygulama `gpt-5.6-terra medium` seçilemediği için engelli; bu modelin kullanıldığı iddia edilmiyor. |
| Appointments uygunluk/çakışma takvimi | Mevcut modül genişletmesi. Randevu akışının zaman uygunluğunu göstermesi kullanıcı değerini artırabilir; tema yönergeleri ilk sürümün takvim/uygunluk hesabı üretmediğini açıkça söylüyor. | Orta; çalışan/şube kaynakları, kapasite, saat dilimi ve iptal/slot politikası açık değil. Ayrı plan seçilmedi. |
| KVKK operasyonları | Mevcut Settings/Pages kapsamı genel ayar/metni karşılayabilir; operasyonel rıza/veri sahibi/saklama ihtiyacı için yeni kanıt yok. | Düşük bağımsız modül ihtiyacı; ek kanıt bekliyor. |
| Yönetici değişiklik geçmişi | Olası ortak çekirdek veya modül; LogViewer hata loglarıyla farklı sorumluluk taşır. Kayıt türleri, retansiyon ve redaksiyon kararı/kanıtı yok. | Düşük; kapsam ve ihtiyaç için ek kanıt bekliyor. |
| Careers / Events | Katalogda tamamlanmış modüller mevcut; Team, Blog rollerinden ayrışıyor fakat yeni, değişmiş kanıt veya talep yok. | Mevcut katalog kapsamı; tekrar geliştirme adayı değil. |

Bugün bir iş kalemi (Redirects) seçildi; ikinci güçlü aday bulunmadı. Plan [Redirects - Yeni.md](Redirects/Redirects%20-%20Yeni.md), konsept [Redirects-Form-Konsept.png](Redirects/Redirects-Form-Konsept.png). Kod/test/build/staging yapılmadı. `gpt-6-luna low` keşif yönlendirmesi de araçtan seçilemedi; model kullanıldığı iddia edilmiyor.
