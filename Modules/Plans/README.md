# Modül adayları ve planlar

Bu klasör, modül kataloğundaki görünür kapsam boşluklarının özetini ve seçilen adayların uygulanabilir planlarını tutar. Planlama ve uygulama ayrı aşamalardır. Buradaki dokümanlar tek başına modül kodu, release, katalog güncellemesi veya tasarım görseli üretmez.

## Görünür adaylar

| Öncelik | Aday              | Neden gerekli                                                                                                                                                                                                                                      | Durum                                                  | Ayrıntılı plan                |
| ------- | ----------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------ | ----------------------------- |
| 1       | Store (e-ticaret) | Katalogda `Products` vardır; modülün kendi tema kuralı fiyat, stok, varyant, sepet, sipariş ve ödemeyi açıkça kapsam dışında bırakır. Satış yapan kurulumların ürün satışını uçtan uca yönetmesi için bu işlevlerin tek modülde sunulması gerekir. | **Yeni** — plan ve ürün formu konsepti hazır; kod uygulaması sırada. | [Store klasörünü aç](Store/Store%20-%20Yeni.md) |

Bu liste, yalnız katalog ve incelenen ilgili modül yönergelerinden doğrulanabilen boşluğu içerir. Başka modüller için ihtiyaç uydurulmaz; yeni aday keşfedilirse ayrı satır ve ayrı plan dosyası eklenir.

### İlk tam baseline taraması

İlk discovery yalnız Store/e-ticaret kapsamıyla sınırlı değildir. 21 Ekim 2026 taramasında katalogdaki 16 modülün ilgili rules/skills dosyaları incelendi; katalog ve bu yönergelerle doğrulanan mevcut kapsamlar üzerinden bütün aday alanlar değerlendirildi. Store, Products kurallarında fiyat, stok, varyant, sepet, sipariş ve ödemenin açıkça kapsam dışı bırakılması nedeniyle güçlü aday olarak korundu. Bu taramada ayrı bir modül planını haklı çıkaran başka yeni ve yeterince doğrulanmış aday bulunmadı. Genel iletişim başvuruları çekirdek Forms akışında, sayfa/içerik/SEO alanları mevcut içerik modüllerinde ele alındığından yalnız adının bulunmaması yeni modül gerekçesi sayılmadı. Store bu baseline için başlangıç hipoteziydi; keşif kapsamını daraltmaz.

## Keşif ve uygulama akışı

Model görev devri ve sıralı adımlar için [İş Akışı](İş%20Akışı.md) belgesini izle.

Otomasyonla keşfedilen ve kullanıcı tarafından başlatılan her discovery/modül işi [Görev Günlüğü](Görev%20Günlüğü.md) dosyasına kaydedilir. Bu dosya etkin kilit, son tamamlanma zamanı, test/build, kaynak staging ve varsa release durumunun tek yetkili kaynağıdır.

Tam baseline ve sonraki katalog/rules/skills imzaları [Keşif Durumu](Ke%C5%9Fif%20Durumu.json) içinde saklanır. İlk tarama bir kez tüm katalog kapsamını okur; sonraki iki saatlik taramalar bu küçük indeksi karşılaştırır ve yalnız yeni/değişen modüllerin yönergelerini derinlemesine inceler. İncelenmiş ve değişmemiş yönergeler yeniden okunmaz.

1. Yeni keşif, önce [Görev Günlüğü](Görev%20Günlüğü.md) içindeki kilidi ve son modül completion zamanını kontrol eder. Etkin kilit varsa başlamaz; aksi halde önceki modül görevi tamamlandıktan en az iki saat sonra yapılır. Göreve release de dahilse bu süre uygulama, doğrulama, release, GitHub asset, katalog ve push tamamlanınca başlar. İki saat, sabit duvar saati tekrarı değil, tamamlanma anından başlayan bekleme süresidir.
2. İlk tam baseline keşfi katalogdaki tüm modülleri kapsar: önce `D:\localhost\ekAgentModules\catalog.json`, ardından her modülün ilgili `.ai/rules/` ve `skills/` dosyaları; belirsizlik devam ederse yalnız ilgili kod. Store seed adayı bu sırada kanıtla yeniden değerlendirilir; e-ticaret tarama filtresi değildir.
3. Baseline sonrasında her run katalog metadata'sı ile her modülün ilgili rules/skills dosyalarının ucuz SHA-256 imzalarını [Keşif Durumu](Ke%C5%9Fif%20Durumu.json) ile karşılaştırır. Derin okuma yalnız yeni katalog kaydı veya metadata/rules/skills imzası değişen modüllerde yapılır; değişmeyen modüllerin kuralları ve kodu yeniden okunmaz.
4. Değişen yönergeler önceki analizi etkiliyorsa aday/plan güncellemesini ve gerekçeyi görev günlüğüne ekle. Değişiklik yoksa yeni aday olmadığını kaydet; README ve ayrıntılı aday planlarına dokunma.
5. Keşif sonucu bu özet dosyasında listelenir. Her yeni aday için `Modules/Plans/<Aday>/` klasörü oluştur; durum etiketi taşıyan ayrıntılı plan (`<Aday> - Yeni.md`, tamamlanınca `<Aday> - Tamamlandı.md`) ve ilgili tasarım/çıktı görsellerini bu klasörde tut. Özet satırı doğrudan bu klasördeki güncel plan dosyasına bağlanır.
6. Plan ve uygulama ayrı işlerdir. Bir planın hazır olması uygulamaya başlandığı anlamına gelmez. Uygulama görevi, planı 5.6 TERA medium uygulayıcıya devredecek şekilde görev sırası ve tamamlanma ölçütleri içerir.
7. Otomasyonla keşfedilen ve kullanıcı tarafından başlatılan her modül işi başlangıçta, aşama devrinde ve tamamlandığında [Görev Günlüğü](Görev%20Günlüğü.md) içinde güncellenir. Kod, frontend, hata yolları ve doğrulamalar tamamlanınca kaynak `D:\localhost\ekAgentModules\<Modul>` konumuna staged kopya olarak aktarılır. Bu kopya push değildir. Release, katalog ve yayın yalnızca kullanıcı açıkça istediğinde başlar.

## Modül tamamlanması ve release sınırı

Uygulama görevi `.ai/rules/moduller.md` ile `.ai/workflows/modul-gelistirme.md` kurallarına uyar. Başarılı doğrulamadan sonra kaynak staging normal tamamlanmadır. ZIP, GitHub Release, ortak katalog güncellemesi, doğrulama ve push yalnızca açık release talebi üzerine yürütülür. Bu tur yalnız plan ve kural dokümantasyonudur; gerçek modül staging'i veya yayın yapılmaz.

### Release görev devri

Uygulama/test/build başarılı olunca kaynak `D:\localhost\ekAgentModules\<Modul>` konumuna kopyalanır ve görev kaydına kaynak yolu ile doğrulama sonuçları yazılır. Bu staging normal tamamlanmanın parçasıdır; push veya release yapmaz. Kullanıcı açıkça release isterse staged kaynak doğrulanır, ZIP hazırlanır, kaynak push edilir, GitHub Release/asset yayımlanır, katalog güncellenip push edilir ve uzak içerik doğrulanır. Önceki modül işinin cooldown saati release istenmişse bu zincir bütünüyle tamamlandığında, istenmemişse uygulama/staging/doğrulama işi tamamlandığında başlar. GitHub auth, korumalı branch veya API/CLI eksikliği varsa kaldığı aşamayı açıkça kaydet; aynı iş tamamlanmış sayılmaz ve çakışan yeni keşif başlatılmaz.

GitHub entegrasyonu repo ve metin dosyası işlemlerini destekleyebilir; release ve ZIP gibi ikili asset işlemleri her bağlantıda bulunmayabilir. Özellikleri çalışma anında denetle. Doğrudan entegrasyon/API veya authenticated `gh`/Git CLI varsa onları kullan; Playwright yalnız gereken GitHub web UI adımları için yedektir.

## Kaynaklar

- Katalog: `D:\localhost\ekAgentModules\catalog.json`
- Ortak modül ve dağıtım kuralları: `.ai/rules/moduller.md`
- Modül geliştirme akışı: `.ai/workflows/modul-gelistirme.md`
- Adayın mevcut Products sınırı: `D:\localhost\ekAgentModules\Products\.ai\rules\tema-entegrasyonu.md` ve `D:\localhost\ekAgentModules\Products\skills\products-theme-integration\SKILL.md`
- Blog formu görsel referansı: `output/playwright/blog-form-desktop.png`
