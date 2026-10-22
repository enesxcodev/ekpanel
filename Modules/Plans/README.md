# Modül adayları ve planlar

Bu klasör, modül kataloğundaki görünür kapsam boşluklarının özetini ve seçilen adayların uygulanabilir planlarını tutar. Planlama ve uygulama ayrı aşamalardır. Buradaki dokümanlar tek başına modül kodu, release, katalog güncellemesi veya tasarım görseli üretmez.

## Görünür adaylar

| Öncelik | Aday              | Neden gerekli                                                                                                                                                                                                                                      | Durum                                                  | Ayrıntılı plan                |
| ------- | ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ | ----------------------------- |
| 1       | Store (e-ticaret) | Katalogda `Products` vardır; modülün kendi tema kuralı fiyat, stok, varyant, sepet, sipariş ve ödemeyi açıkça kapsam dışında bırakır. Satış yapan kurulumların ürün satışını uçtan uca yönetmesi için bu işlevlerin tek modülde sunulması gerekir. | **Yerel uygulama tamamlandı** — ürün/varyant/medya/ayar/sepet/sipariş ve yönergeler doğrulandı; harici ödeme/kargo onayı ayrı. | [Store klasörünü aç](Store/Store%20-%20Tamamland%C4%B1.md) |
| 2 | Şubeler/Bayiler | Çoklu şube ve bayi adres/iletişim kayıtları; Türkiye Konum modülüne bağımlı. Tek modül, gerekirse kayıt türüyle ayrılır. | **Plan hazır, kullanıcı incelemesi bekliyor** — Konum ortak depoda var ancak katalogda yok; dağıtım önkoşulu ayrıca ele alınmalı. | [Branches planı](Branches/Branches%20-%20Yeni.md) |

Kullanıcı Kariyer/İş İlanları ve Etkinlikler önerilerini tek birleşik Şubeler/Bayiler modülüyle değiştirdi; planında Şube/Bayi türü aynı kayıt modelinde ve Türkiye Konum bağımlılığı olarak ele alındı.

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
| KVKK ayarları | Kaynak notundaki kullanıcı ifadesi “KBKK ayarları”; olası “KVKK ayarları” olarak değerlendirildi. Settings tekil/sabit ayarları yapılandırabilir, Pages politika metni yayımlayabilir; operasyonel rıza, çerez tercihi, veri sahibi başvurusu veya saklama akışı ihtiyacı henüz kanıtlanmadı. | **Kapsam doğrulaması bekliyor** — tek başına yeni modül adayı doğrulanmadı; privacy/compliance iş akışları için kullanıcı ihtiyacı ve kapsam kanıtı gerekli. | KVKK/KBKK yazımını ve istenen işlevleri netleştir: yalnız gizlilik metni/ayar mı, yoksa çerez rızası kaydı, veri sahibi başvurusu, saklama/anonimleştirme gibi tekrarlanan iş akışları mı? Yeni modül ancak bağımsız iş akışı ve kullanıcı ihtiyacı doğrulanırsa planlanır; yasal kapsam varsayma. |

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
