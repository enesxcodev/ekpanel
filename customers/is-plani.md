# Günlük müşteri adayı ve teklif taslağı planı

Amaç, her 30 dakikada bir heartbeat ile kaldığı aşamadan devam ederek tek seride toplam 3 TAMAMLANMIŞ teklif taslağı hazırlamaktır. Üç uygun aday bulmak seriyi bitirmez. İlk pilotta beş sektör havuzundan koordinatörün seçeceği 3 sektörden birer tamamlanmış teklif hedeflenir; koordinatör ilk turda sektör seçimini ve gerekçesini kaydeder. Bu belge yürütme sözleşmesidir; otomasyon burada kurulmadı. Başlangıç şehri İstanbul; ilçe/yarıçap stratejisi henüz kullanıcı incelemesi bekleyen öneridir. Araştırma ve ücretli üretim duraklatılmıştır.

## Roller ve model yönlendirmesi

İlk 3 farklı işletme çalışması **teknik pilot**tur: workflow, kalite, süre ve token/maliyet ölçer; sektör satış performansı hakkında çıkarım sağlamaz. Sonrasında kullanıcı seçerse tek sektörde 25 işletmelik **ticari pilot** önerilir; başlatılmadı. Ticari pilotta yanıt, ciddi görüşme, kabul ve gelir, tanımlı dönem/payda ve gerçek kayıtlarla ölçülür. Müşteriye gönderim ve ticari pilotun başlaması ayrı kullanıcı talimatı gerektirir; otomatik çalıştırılmaz. Otomasyon duraklatılmış kalır; bu belge güncellemesi etkinleştirmez.

| Rol | Sorumluluk | Model / araç | Devir ölçütü |
| --- | --- | --- | --- |
| Koordinatör | İlk tur sektör seçimi/gerekçesi, seri durumu, konum, kota, bütçe, mükerrer kontrolü, tur kapanışı | Sektör seçimi Luna low; basit kayıt/hesap araçla | Veri çelişkisinde gerekçeli Luna medium; 3 tamamlanmış teklifte dur |
| Araştırmacı Luna | Maps taraması, aktiflik/sektör/iletişim/site kontrolü ve kaynaklı veri toplama | GPT-6 Luna low + erişilebilir arama/Maps aracı | Kanıt yoksa belirsizliği kaydet; veri çelişkisi için gerekçeli Luna medium |
| Marka analisti | Firma/sektör raporu, yorum temaları, görsel alaka ve marka kanıtı, önerilen içerik alanları, standart brif | Luna low; yetersiz görsel/marka yorumu veya brif kalitesinde Sol low | Yetersizlik gerekçesini kaydet; gerçekten gerekirse medium, kanıt uydurma |
| Tasarım kararı yazarı | Seçilen aday için ana sayfa tasarım kararı | GPT-6 Luna low; karmaşık marka kararında GPT-6.1 Sol low | `tasarim-karari.md` hazırlanır; otomatik ImageGen çağrısı yapılmaz |
| Teklif yazarı | Doğrulanmış veriden kısa kişiselleştirilmiş Türkçe taslak | Luna low | Çıktı ve kimlik kontrolü tamamlanmadan hazır işaretleme |
| Kayıt sorumlusu | takip.md, maliyet.md, durum kayıtları ve hesaplar | Basit araç/hesap; metin gerekirse Luna low | Kullanıcı takip hücrelerine dokunma; gerçek kullanım verisini koru |
| Denetçi | İddia–kaynak, renk, Türkçe yazı, firma kimliği, çıktı ve maliyet kontrolü | Luna low; görsel yorum yetersizse gerekçeli Sol low | Medium yalnız gerekirse; en çok bir düzeltme, sonra insan incelemesi |

Model bir karar/metin/görsel yorumlama seçeneğidir; görsel üreten Image Gen aracı ayrı bir yetenektir. Bir modeli seçmek Maps erişimi, zamanlama veya imagegen çağrısı sağlamaz.

Her görev işi yapabilen ekonomik model/araçla başlar; tüm işler Sol'a verilmez. Reasoning varsayılanı **low**, desteklenen ve işe yeterli daha düşük seviye kullanılabilir; **medium yalnız gerçekten gerekli olduğunda üst sınırdır**, genel varsayılan değildir. `high`, `xhigh`, `max`, `ultra` veya daha yüksek seviye kullanılmaz. Model veya reasoning yükseltmesinin somut nedeni karar ve maliyet kayıtlarına yazılır; Sol'a geçiş de low ile başlar. Image Gen ayrı araçtır; desteklemediği reasoning parametresi gönderilmez. İlk pilot 3 hazır teklifte kapanır; 5'e otomatik yükseltilmez. Sonraki seri ve hedef kullanıcının yeni kararını bekler.

21.10.2026 resmi model belgelerine göre [Luna](https://developers.openai.com/api/docs/models/gpt-6-luna) none/low/medium, [6.1 Sol](https://developers.openai.com/api/docs/models/gpt-6.1-sol) low/medium seçeneklerini destekler; Sol'da none/minimal yoktur. Sağlayıcının medium varsayılanına güvenmek yerine low açıkça seçilir. Buradaki göreve dayalı tercihler mevcut modellerle öneridir; hesap erişimi, araç ve görsel girdi desteği gerçek çalışmada doğrulanır. İstenen yetenek/araç yoksa engel kaydedilir; model değiştirerek varmış gibi gösterilmez.

21.10.2026 tarihinde [resmi model kataloğu](https://developers.openai.com/api/docs/models) Luna'yı maliyet duyarlı yoğun işler, 6.1 Sol'u kalite–maliyet dengesi için konumlandırıyor; güncel modeller metin ve görsel girdi alabiliyor. Bu rol dağılımı iş akışı önerisidir, kalite garantisi değildir. Hesabın erişimi ve kullanım sınırları gerçek çalışmadan önce kontrol edilir. Fiyat sabitlenmez; güncel tarife ve gerçek kullanım kaydedilir.

[Resmi görsel üretim belgesi](https://developers.openai.com/api/docs/guides/tools-image-generation), metin ve isteğe bağlı görsel girdilerle ayrı görsel üretim aracını tarif eder. Bu oturumdaki `image_gen.imagegen` aracı referans dosya yolları veya yakın konuşma görselleri kabul eder; model seçme alanı sunmaz. API örnekleri bu aracın parametreleriymiş gibi kullanılmaz. Gerçek üretim sırasında imagegen becerisi okunur; bu teslim yalnız brif ve yer tutucu içerir.

## Aşamalar ve kabul kapıları

| Aşama | Girdi | İşlem | Çıktı / geçiş şartı |
| --- | --- | --- | --- |
| 0. Hazırlık | Yapılandırma, geçmiş adaylar | Konum/kota/bütçe ve araç erişimi kontrolü | Gün klasörü ve çalışma kimliği; eksikse bekleme |
| 1. Keşif | Sektör + konum sorguları | Maps'te adayları tara; aktiflik, sektör uyumu, iletişim ve profil durumunu kontrol et; görev başına en çok 10 farklı profil | Uygun ön aday veya kaynaklı eleme gerekçesi; fotoğraf/yorum sayısı eleme değildir |
| 2. Doğrulama | Ön aday | Resmi siteyi ara; kimliği eşleştir; mükerrerleri ele; site yokluğunu veya belirsizliği dikkatle kaydet | Araştırma kaydı, tarih, güven ve site kararı; site bulunamayanlar önceliklendirilir |
| 3. Lead Scoring ve içerik | Kimliği/site durumu kontrol edilmiş aday, mevcut fotoğraf ve yorum kanıtları | Luna low her ağırlıklı ölçüte kanıta dayalı puan önerir; koordinatör toplamı ve kanıt ayrımını doğrular; içerik seviyesini belirler | Kanıt/çıkarım/bilinmeyenleri ayıran 100 puanlık kayıt ve içerik stratejisi |
| 4. Referans incelemesi | Sektörel referanslar yetersiz veya güncel değilse | İsteğe bağlı en çok 1 ilgili web sitesinin yalnız ana sayfasını incele; URL/tarih ve soyut gözlemleri yaz | İç referans; rakip iddiaları hedef firmaya atfedilmez |
| 5. Tasarım kararı | 75+ nihai puan, doğrulanmış brif ve içerik stratejisi | `tasarim-karari-sablonu.md` üzerinden `tasarim-karari.md` hazırla; ImageGen promptunu yaz, aracı çağırma | Marka analizi, palet, ana sayfa bölümleri, CMS modülleri, doğrulanmış varlıklar ve ayrıntılı prompt |
| 6. Teklif | 75+ puanlı doğrulanmış firma ve kontrol edilmiş tasarım | Luna low ile kısa kişisel metin | Gönderilmemiş teklif taslağı; insan incelemesi bekler |
| 6. Kapanış | Tüm aşama kayıtları | Listeyi güncelle, sayımları ve maliyeti uzlaştır | Tur kapanışında günlük Markdown raporu; eksikler ve sonraki adım |

Seri kotası, tüm zorunlu teslimleri doğrulanmış 3 benzersiz firmanın tamamlanmış teklif taslağıdır. Araştırılmış/uygun aday sayısı ve tasarım sayısı yalnız ara göstergelerdir; kotaya tek başlarına katkı vermez. Eksik gün veya tur başarılı gibi gösterilmez.

## Heartbeat yürütme sözleşmesi

1. Her 30 dakikada koordinatör kalıcı seri ve firma durumlarını okur. İlk tur sektörleri ve seçim gerekçesini kaydeder; sonraki turlar bu karardan devam eder. Konum girilmemişse araştırma başlatmaz, eksik alanı kaydeder.
2. Önceki run sürüyorsa çakışan ikinci run başlatılmaz. Seri kaydında run kimliği, kilit sahibi, başlangıç ve son canlılık zamanı tutulur. Kilit atomik edinilir; alınamazsa tur atlanır. Yalnız canlılık kaybı çalışan run'ın bittiği kanıtı sayılmaz; kilit belirsizse duraklatılır, sahiplik doğrulanmadan devralınmaz.
3. Aynı seri + firma kimliği + aşama + girdi sürümü tekrar geldiğinde doğrulanmış çıktı yeniden üretilmez veya ikinci kez sayılmaz (idempotency). Önce mevcut dosya ve devam eden görsel çağrısı kontrol edilir. Sonucu belirsiz çağrı otomatik yeniden gönderilmez; durumu kaydedilir.
4. Zincir Luna low araştırma → Luna low marka analizi/içerik önerisi/standart brif (yetersiz görsel yorum veya brifte gerekçeli Sol low; medium yalnız gerekirse) → tasarım kararı dosyası (otomatik ImageGen yok) → Luna low kısa teklif taslağıdır. Tüm zincirin bir turda bitmesi gerekmez. Her aşama sonunda dosyalar ve sonraki adım kalıcı kaydedilir; sonraki heartbeat ilk eksik/doğrulanmamış aşamadan sürer.
5. Firma kayıtları kısa JSON/JSONL yapısında tutulur; kanıt, çıkarım ve bilinmeyen ayrıdır. Tamamlanmış aşamalar tekrar çalıştırılmaz. Firma klasörü ve kaynak dosyalar korunur; günlük Markdown raporu yalnız tur kapanışında üretilir. 75+ nihai puanlı aday klasörüne `tasarim-karari.md` eklenir.
6. Hazır teklif taslağı sayılmak için aktiflik, hedef sektör uyumu, iletişim bilgisi, kimlik/site kontrolü, 75+ lead puanı, kanıt/çıkarım/bilinmeyenleri ayıran puan kaydı, fotoğraf/yorum miktarına göre belirlenmiş içerik stratejisi, doğrulanmış brif, tamamlanmış ve doğrulanmış `tasarim-karari.md`, Luna low teklif metni, maliyet.md ve karar/durum kayıtları birlikte doğrulanır. Fotoğraf veya yorum için asgari sayı şartı yoktur; eksik içerik stratejide açıkça ele alınır. Yer tutucu, boş dosya, yalnız dosya adı veya `üretildi` beyanı yeterli değildir. Doğrulama zamanı ve teslim yolları kaydedildikten sonra firma `teklif_taslagi_hazir` sayılır; yalnız gönderim için insan incelemesi bekler.
7. Her tur başında ve yeni firma/ücretli çağrı öncesinde seri toplamı kontrol edilir. 3 doğrulanmış teklif hazır olduğunda seri `tamamlandi` olur ve heartbeat bu seri için durdurulur. Zamanlayıcı henüz durdurulamamışsa sonraki tetikler iş yapmadan çıkar. Ertesi gün otomatik yeni seri açılmaz; yeni seri için yeni talimat gerekir.
8. Geçici hata ve görsel düzeltme sınırları firma/aşama boyunca kalıcı tutulur; her heartbeat tekrar hakkını sıfırlamaz. Engel, eksik veri, deneme sayısı ve sonraki adım kaydedilir. Kota için kriterler gevşetilmez. Hiçbir müşteriye mesaj gönderilmez; teklif yalnız dosyaya yazılır.

## Araştırma ve site kontrolü

### Görev başına Maps tarama sınırı

Bir görevde en fazla **10 farklı Google Maps işletme profili** açılıp incelenir. Sayaç yalnız farklı işletme profilleri açılarak kontrol edildiğinde artar; aynı profili tekrar açmak/yenilemek yeni işletme sayılmaz. Onuncu farklı profil incelendikten sonra yeni aday araması durur ve uygun aday bulunmasa da bu sınır günlük raporda belirtilir. Yeni bir görevde sayaç ancak o görev kullanıcı tarafından başlatıldığında baştan başlar. Bu sınır Maps profili sayısıdır; genel web arama sorgusu sayısı değildir.

### Aday uygunluğu ve Lead Scoring

Fotoğraf ve yorum adedi zorunlu eleme kapısı değildir. Adayın ilerlemesi için faal işletme durumu, hedef sektör uyumu, erişilebilir iletişim bilgisi ve web sitesi durumu kontrol edilip kaydedilir. Web sitesi bulunamadığı kapsamlı ama tanımlı aramalarda destekleniyorsa adayı önceliklendir; durum belirsizse bunu açıkça yaz ve kesin yokluk iddiası kurma.

Uygunluğu GPT-6 Luna low, araştırmacının kaynaklı bulgularını kullanarak taslak puanlar. Koordinatör beş kalemin toplamını basit hesapla kontrol eder; model veya ajan kanıt yerine geçmez. Her kalemde puan, kanıt, çıkarım ve bilinmeyen veri ayrı gösterilir; ödeme kapasitesi ve Google’dan müşteri kazanma potansiyeli tahmindir ve dayanağı yazılır. Veri yoksa yüksek puan uydurulmaz; bilinmeyen alan puanlanmış gerçek gibi sunulmaz ve güven düzeyi düşürülür.

| Ölçüt                                        | Azami puan | Kanıt değerlendirme örnekleri                                                                                 |
| -------------------------------------------- | ---------: | ------------------------------------------------------------------------------------------------------------- |
| Tahmini ödeme kapasitesi                     |         25 | Yalnız açıkça gözlenen işletme ölçeği/fiyat sinyalleri; yoksa bilinmiyor, yüksek varsayım yok                 |
| Web sitesi ihtiyacı                          |         25 | Doğrulanmış resmi site yokluğu, güncellik/kullanılabilirlik sorunları; belirsiz alanlar kesin yokluk sayılmaz |
| Google üzerinden müşteri kazanma potansiyeli |         20 | Arama niyetiyle sektör/hizmet uyumu ve erişilebilir profil verileri; sonuç garantisi değildir                 |
| Dijital görünürlük eksikliği                 |         15 | Kaynaklı site, profil ve diğer dijital varlık gözlemleri                                                      |
| İletişime geçilebilirlik                     |         15 | Görünür ve firma kimliğiyle eşleşen telefon/iletişim kanalları                                                |
| **Toplam**                                   |    **100** |                                                                                                               |

Ön eleme puanı (`on_puan`) nihai puandan ayrı tutulur. Ön elemede 60+ alan veya eksik/belirsiz verisi nedeniyle potansiyeli açıklığa kavuşmamış aday detaylı incelemeye alınır. Detaylı incelemede aynı beş ağırlıkla nihai 100 puan (`nihai_puan`) hesaplanır. **75–100:** yüksek öncelik; tasarım kararı ve kişiselleştirilmiş teklif taslağı. **50–74:** orta öncelik; aday havuzunda beklet. **0–49:** düşük öncelik; şimdilik tasarım/teklif hazırlama. Bu eşik müşteriyle iletişim/gönderim yetkisi vermez.

Önce sektörel referans dosyaları kullanılır. Bunlar yetersiz veya güncel değilse, aday başına zorunlu olmaksızın en fazla **1 rakip/benzer işletme web sitesi** incelenebilir; yalnız ana sayfa açılır, alt sayfalara gidilmez. URL/tarih ve hizmet, kapasite, içerik sunumuna dair soyut gözlemler iç kayda alınır. Rakip iddiaları hedef işletmeye atfedilmez; görsel, logo, metin veya ayırt edici yerleşim kopyalanmaz.

Aktiflik değerlendirmesinde Maps profilinin açık/kapalı durumu ve erişilebilen yakın dönem güncellik sinyalleri kaydedilir; yorum adedi tek başına aktiflik veya ticari potansiyel puanı değildir. Yorumların yakın tarihleri ve işletme yanıtları varsa destekleyici kanıt olarak not edilebilir.

### İçerik yeterliliği ve tasarım stratejisi

Fotoğraf/yorum sayıları işletmeyi elemek veya lead skorunu yükseltmek için değil, tasarım stratejisini seçmek için kullanılır. Her varlık sayısı ve kaynağı kaydedilir; erişilemeyen içerik varmış gibi gösterilmez.

Doğrulanmış sosyal medya hesabı, özellikle Instagram, işletmenin kendi hizmetlerini ve tasarım örneklerini araştırmak için kullanılabilir. Önce Maps bağlantısı, aynı telefon/adres veya tutarlı profil adıyla hesabın işletmeye ait olduğu doğrulanır. Profil biyografisi, öne çıkanlar ve erişilebilen gönderi açıklamalarından hizmet/kapsam bilgisi alınabilir; kaynak URL'si ve kontrol tarihiyle tasarım kararında kanıt olarak gösterilir. Görsel örnekleri için belirli gönderi bağlantısı ve gözlenen içerik yazılır; erişilemeyen ya da yalnız alt metin/açıklamayla bilinen görüntü görsel olarak incelenmiş sayılmaz. İndirilen/yerel varlık ile yalnız bağlantı üzerinden referans gösterilen gönderi ayrı tutulur. Tasarımda gerçek işletme görseli kullanımı için görselin işletmeyle bağı, kalite ve kullanım izni ayrıca doğrulanır; izin belirsizse promptta görseli kullanılmış gibi sunma, yalnızca içerik/renk fikri olarak belirt. Müşteri/yüz görselleri izinsiz kopyalanmaz. Sosyal medya hesabı zorunlu eleme koşulu veya yüksek ticari puan gerekçesi değildir; puanlama içindeki sınırlı sinyal olarak kalır.

| Mevcut fotoğraf | Tasarım stratejisi |
| --- | --- |
| 6 veya daha fazla | İzin/kaynak koşullarına uyarak gerçek fotoğraflarla kişiselleştirilmiş tasarım |
| 2–5 | Mevcut doğrulanmış görsellerle kişiselleştirilmiş tasarım |
| 0–1 | Sektörel tasarım yaklaşımı; işletmeye ait görsel varlık eksikliği açıkça belirtilir, gerçek işletme fotoğrafı izlenimi verilmez |

| Mevcut yorum | Analiz stratejisi |
| --- | --- |
| 20 veya daha fazla | Kaynaklı örneklerde tekrarlanan olumlu temaları analiz et; örneklem sınırını ve olumsuz/karışık bulguları gizleme |
| 1–19 | Yalnız mevcut kaynaklı yorumlardan sınırlı çıkarım yap; genelleme yapma |
| 0 | Yorum/deneyim uydurma; sahte referans veya değerlendirme üretme |

İş akışı: **Araştırma → Kimlik ve web sitesi kontrolü → ön puan → detaylı inceleme (60+ veya belirsiz/eksik veri) → nihai puan → içerik yeterliliği → gerekiyorsa tek rakip ana sayfası → tasarım kararı dosyası → teklif taslağı.** Otomatik ImageGen yoktur. Fotoğraf ve yorum azlığı hiçbir aşamada tek başına eleme nedeni değildir.

Araştırmadan önce ana `customers` içindeki [firma geçmişi](firma-gecmisi.md), [sektör özeti](teklif-goturulen-sektorler.md) ve bağlı tüm gün kayıtları kontrol edilir; aday kimliği üretimden önce eşleştirilir. Ortak firma geçmişi kimlik/tekrar seçmeme kaynağıdır, teklif listesi türetilmiş çalışma görünümüdür. Aynı firma/şube yeni günlük aday veya tamamlanmış teklif diye yeniden sayılmaz. Değişmiş eski kaydı yeniden ele almak kullanıcı kararı ve değişiklik kanıtı gerektirir; kimliği korunur, yeni firma gibi kotaya girmez. Sektör özeti aynı sektördeki başka firmaları topluca eleme nedeni değildir.

1. Maps profil URL'si, varsa Place ID, ad, adres, telefon, kategori, yorum sayısı ve erişim zamanı kaydedilir. Telefonun listelenmesi çalıştığını kanıtlamaz.
2. Fotoğraf ve yorum sayısı zorunlu eleme eşiği değildir. Sayıları ve erişilebilir kaynakları kaydet; görsel/yorum stratejisini içerik yeterliliği tablosuna göre seç. Fotoğrafların işletmeyle ilişkisi veya kullanım hakkı doğrulanmıyorsa gerçek işletme görseli gibi sunma; eksikliği ve strateji sınırını kaydet.
3. Yorum kullanılırsa kaynağı, erişim tarihi, görünen yorum tarihi ve kısa özeti kaydet. Minimum yorum örneklemi yoktur. Yalnız olumlu örnekleri seçip çelişkileri gizleme; yorum metninin bağımsız doğrulandığını iddia etme. 0 yorumda herhangi bir müşteri deneyimi uydurma.
4. Maps web sitesi alanı kontrol edilir; boş olması yeterli değildir. Web'de en az firma adı + konum, firma adı + tam adres ve telefon numarası sorguları yapılır. Varsa profilin resmi sosyal bağlantıları ve yönlendirdiği alan adları incelenir.
5. Bulunan sitenin ad/adres/telefonu karşılaştırılır. Aynı ad tek başına yeterli değildir; telefon veya tam adres gibi güçlü eşleşme ve tutarlı işletme kimliği aranır. Rehber, sosyal profil ve pazar yeri bağımsız resmi site diye işaretlenmez; her biri ayrıca kaydedilir.
6. Her sorgu için tam sorgu, zaman, kaynak URL/kanıt yolu, gözlem ve eşleşen/çelişen alanlar yazılır. Maps profilindeki site alanı ile web aramasında çıkan olası domain ayrı bulgulardır. Benzer firma adı tek başına domaini o işletmeye bağlamaz; telefon, tam adres, Maps bağlantısı, resmi sosyal profil yönlendirmesi ve sayfa kimliği karşılaştırılır. Başaran Usta / Başaran Oto gibi ad varyantlarında bu bağ özellikle araştırılır; kanıtları ve çelişkileri kaydet, doğrulanmayan bağı varsayma. Web sitesi durumu kontrol edilmesi gereken bir alan ve puanlama girdisidir; site sahibi olmak adayı otomatik elemez, doğrulanmış sitesi bulunmayan adaylar önceliklendirilir. Bağ doğrulanamazsa `olası eşleşme, sahiplik doğrulanmadı` diye kaydet; kesin yokluk iddiası kurma. Site erişilemez veya yönlendirme çelişkiliyse yalnız ilgili alanı duraklat.
7. Sonuç yalnızca “Yapılan kontrollerde resmi web sitesi bulunamadı” olabilir. Arama sonuçları yokluk kanıtı değildir. Güven `yüksek/orta/düşük` ve gerekçesi kaydedilir; yalnız tam kontrol ve tutarlı kimlikle yüksek güven aday kapısını geçer. Bu da kesin yokluk iddiası değildir.

Mükerrerlik önce Place ID, sonra normalize telefon ve adres + firma adı ile tüm günlerde aranır. Aynı telefon kullanan şubeler otomatik birleştirilmez; adres ve şube kimliği incelenir. Eski kayda bağlantı verilir, aynı işletme yeniden taranmaz. Eski kriterlerle elenmiş aday, yeni kriterlere göre yeniden değerlendirilebilir; aynı kalıcı kimlik korunur ve yeni aday kotası/mükerrer kayıt oluşturulmaz.

## Analiz ve tasarım kararına devir

Analist her hizmet iddiasını kaynakla eşler. Yorumlardan “incelenen yorumlarda şu tema görüldü” sonucu çıkarılabilir; “en iyi”, garantili sonuç, yetki belgesi, ödül veya hizmet kapsamı çıkarılamaz. Yorumlar varsa örneklenen kayıtlar kaynak kimliklerine bağlanır; tema sıklığı yalnız incelenen örneklem için raporlanır, tüm müşterilere genellenmez.

Marka rengi yalnız logo, tabela veya cephedeki marka öğesinden, fotoğraf kimliği ve ilgili bölge belirtilerek çıkarılır. Araç, tesisat, cilt bakımı veya ekipman fotoğrafının baskın rengi marka rengi sayılmaz. Işık etkisi nedeniyle tahmini renk kesin kurumsal HEX gibi sunulmaz. Kanıt yoksa `bilinmiyor`; kullanılacak nötr palet yaratıcı tercih olarak etiketlenir.

### Önerilen içerik alanları

Marka analisti [modül kataloğunun Hızlı seçim tablosunu](module-catalog.md#hızlı-seçim) yalnız modül adı, iş amacı ve başlıca sınır düzeyinde kısa bir rehber olarak kullanır. Bu analiz/brif aşamasında kataloğun teknik bölümleri, bağlantılı rule/skill dosyaları, modeller, `features.json` veya servis ayrıntıları açılmaz. Katalogdaki “modül seçilince ayrıntıyı oku” talimatı teknik uygulama aşamasına ertelenir. Tasarım karar dosyasında katalogdan yalnız kısa seçim önerileri kullanılır.

| Alan adı | Tek cümle firma gerekçesi | Veri var / eksik |
| --- | --- | --- |
| Hizmet/servis bölümü (Services rehberi) | Firmanın doğrulanmış hizmetlerini anlaşılır biçimde tanıtmak için temel beklentidir. | Var: [hizmet kanıt kimlikleri] / eksik |
| Yorum/deneyim alanı (Testimonials rehberi; isteğe bağlı) | Mevcut, kaynaklı yorumlardan sınırlı ve temsil gücü belirtilmiş deneyim temaları sunmak için düşünülebilir. | Var: [kaynaklı yorumlar] / yok veya yetersiz |
| [İsteğe bağlı alan; ör. galeri, ekip veya ürün] | [Firmaya ait kanıta dayalı tek cümle gerekçe]. | Var: [kanıt kimliği] / eksik |

Diğer alanlar yalnız firma kanıtı ve ihtiyacı varsa önerilir; kanıtı olmayan ekip, ürün veya galeri içeriği uydurulmaz. Google yorumları varsa analiz girdisi ve isteğe bağlı içerik önerisidir; otomatik Google yorum importu veya entegrasyonu vaat edilmez. Kamusal kullanım değerlendirmesi tamamlanmamış yorumlar yayıma hazır alıntı sayılmaz.

Katalog kapalı bir bölüm listesi veya zorunlu sayfa şablonu değildir; her modülü kullanmak gerekmez. Modül eşleşmeleri mevcut kolay uygulanabilen içerik alanlarını hatırlatan yumuşak rehberdir. Font, kompozisyon, düzen ve bölümlerin sunumu yaratıcı tasarım promptunda tariflenir; öneriler yaratıcılığı sınırlamaz.

Otomatik ImageGen çağrısı yapılmaz. 75+ nihai puanlı aday için `tasarim-karari.md` hazırlanır: marka kanıtı/çıkarımı/bilinmeyeni, kanıta dayalı renk paleti, ana sayfa bölümleri, katalog Hızlı seçiminden uygun CMS modülleri, doğrulanmış ve kullanılabilir varlıklar, yorum/içerik sınırları ve ayrıntılı ImageGen promptu. Prompt doğrulanmamış iddia, sahte referans veya rakipten kopya öğe içermez.

Font, kompozisyon, düzen, hiyerarşi ve yaratıcı kararlar promptta tasarım aracına bırakılabilir; bu iş akışında görsel üretim çağrısı yapılmaz. Yeni hizmet, sahte müşteri yorumu, ödül, referans, fiyat veya sonuç garantisi eklenmez. Gerçek yorumların kamusal tasarımda kullanımı ayrıca değerlendirilir; varsayılan olarak yalnız iç analizde özetlenir.

## Teklif yazarı mesaj rehberi

Ekonomik yönlendirme: kısa kişiselleştirilmiş teklif Luna low ile yazılır. Ticari fiyat/şartlar metin modeline ürettirilmez: yalnız [kullanıcı onaylı ticari koşullar](ticari-kosullar.md) içindeki sürümü/onay tarihi belli değerler firma `ticari-teklif.md` dosyasına kopyalanır ([örnek](ornekler/2026-10-21/temsili-oto-atolyesi/ticari-teklif.md)). Eksik alan “kullanıcı belirleyecek” kalır; geçerli ticari teklif hazır sayılmaz. Fiyat, indirim, teslim, revizyon, dahil modüller veya ödeme şartları tahmin edilmez. Tasarım brifinin yumuşak modül önerileri dahil modül taahhüdü değildir; dahil liste yalnız onaylı paketten gelir. Kaynak sürüm/onay kaydı ve kopyalama zamanı saklanır; global değişiklik eski teklifi sessizce değiştirmez. AI üretim maliyeti satış fiyatından ayrı kalır. “Ne kadar” sorusunda yalnız doldurulmuş onaylı paket kullanıcıya gösterilir; yanıtı kullanıcı gönderir. Fiyatsız görselli pilot paketinin hazır sayımı, ticari teklifin onaylı/hazır olduğu anlamına gelmez.

Luna, tasarım karar dosyası tamamlanıp kontrol edildikten sonra kısa Türkçe mesajı üç bileşenle kişiselleştirir:

1. İşletmenin neden seçildiği: doğrulanmış hizmeti ve kaynaklı yorumlarda görülen ilgili özellik; araştırma kaydında kanıt kimlikleri tutulur, kanıtsız övgü eklenmez.
2. Site kontrolü: tarihli kontrollerle sınırlı “Araştırmamızda resmi web sitenize ulaşamadık” ifadesi; kesin “siteniz yok” iddiası kullanılmaz.
3. Somut işlevsel fayda: hizmetleri inceleme, işletmeyle iletişim kurma ve teklif talebine yönelme amacı; çalışan sistem veya satış artışı garantisi verilmez.

Onaylanan çekirdek mesaj örneği: “Sunduğunuz hizmetleri internette arayan potansiyel müşterilere ulaşmak ve reklamlarınızdan gelen ziyaretçileri teklif talebine yönlendirmek için işletmenize özel bir web tasarımı hazırladık.” Hizmet ifadesi gerçek işletmenin doğrulanmış hizmetine uyarlanır. Mevcut reklam kullanımı doğrulanmadıysa “reklamlarınızdan gelen” yerine “arama ve reklam kanallarından gelebilecek ziyaretçiler” veya “ileride verebileceğiniz reklamlardan gelecek ziyaretçiler” kullanılır; aktif reklam kampanyası varmış gibi yazılmaz.

Rakiplerin web/reklamdan müşteri veya teklif aldığı, rakiplerin araştırıldığı ya da satışın artacağı kanıtsız söylenmez. Görselli teklif yöntemi korunur: gerçek tasarım mesajla birlikte kullanıcı tarafından paylaşılır; bu pilotta önce izin isteyip görseli sonradan paylaşma A/B testi eklenmez. “Tasarım hazırladık” yalnız gerçek çıktı mevcut ve kontrol edilmişse kullanılır; [temsili teklif](ornekler/2026-10-21/temsili-oto-atolyesi/teklif-taslagi.md) koşullu örnektir. Kullanıcı kendisi gönderir; otomatik mesaj/gönderim/takip yoktur, otomasyon duraklatılmış kalır.

## Eksik veri, deneme ve maliyet

- Aktif işletme, hedef sektör uyumu, iletişim bilgisi ve web sitesi durumu kontrol edilip kaydedilir. Fotoğraf/yorum miktarı tek başına eleme nedeni değildir. Eksik puan kanıtla yükseltilmez; 75 puan eşiği varsayımla aşılmaz.
- Geçici araç hatasına bir tekrar yapılır; zaman ve hata kaydedilir. CAPTCHA/erişim kısıtında kontroller aşılmaya çalışılmaz; duraklatılır. Eksik kanıtı model yükselterek doldurma.
- Brif çelişkisi veya belirsiz marka okumasında kaynak yeniden incelenir; veri çelişkisinde gerekirse Luna medium, görsel/marka yorumu veya brif Luna ile yetersizse gerekçeli Sol low kullanılır. Medium yalnız gerekirse seçilir; sınırlı inceleme sonrası belirsizlik sürerse `bilinmiyor` veya duraklama kararı verilir.
- Tasarım kararında firma adı, Türkçe içerik, iletişim, hizmetler ve kullanılabilir gerçek varlıklar kanıtlarla karşılaştırılır. Bir gerekçeli düzeltmeden sonra hata sürüyorsa `insan_incelemesi` olur.
- Önce düşük maliyetli kimlik, sektör, iletişim ve web sitesi durumu kontrolü; sonra puanlama ve gerekirse ayrıntılı inceleme yapılır. Web sitesi olan aday otomatik elenmez. Aynı kanıt tekrar indirilmez/işlenmez; değişen bölüm yeniden değerlendirilir; otomatik görsel üretimi yoktur.
- Her aşama/model çağrısı için gerçek kullanım kaynağı/çağrı kimliği, reasoning ayarı, input/output/cached/reasoning tokenları ayrı kaydedilir. Erişilemeyen alan `ölçülemiyor` olur; tahmin gerçek ölçüm gibi sunulmaz. Cached ve reasoning alt kırılımları sağlayıcının toplamına tekrar eklenmez. Görsel çağrı ve çıktı adedi, tekrarlar ve görsel maliyeti metin kullanımından ayrı tutulur; bilinmeyen fiyat/ücret uydurulmaz. Süre, tarife tarihi ve mevcut gerçek ücret kaydedilir; günlük/firma başı tavan dolunca yeni ücretli çağrı durur.
- Pilot kapanış raporu aşama/model bazında ölçülen token toplamlarını ve kapsanan çağrı sayısını, eksik ölçümleri, ayrı görsel çağrı/adet/maliyet toplamını, darboğazları ve somut düzeltme önerilerini içerir. Eksik ölçüm varken toplam yalnız ölçülebilen kısmı ifade eder. Hedef veya reasoning tavanı rapor sonucuyla kendiliğinden artırılmaz.

## Kayıt yapısı ve durumlar

Her günlük seri klasöründe `takip.md` bulunur; [manuel takip şablonu](takip-sablonu.md) kullanılır. Satış durumları hazır paket üretiminden ayrı tutulur. Kullanıcı teklifleri kendisi manuel gönderir, yanıt/sonuçları elle doldurur. Otomasyon yalnız yeni doğrulanmış hazır pakete firma/teklif bağlantısı ve `teklif_taslagi_hazir` durumuyla satır açar; kullanıcı hücrelerini ve manuel durumunu asla ezmez. Gönderim/yanıt/tutar/sonuç uydurulmaz. Sonraki takip tarihi iletişim yetkisi değildir; WhatsApp, gönderim ve müşteri takip otomasyonu kapalıdır.

Satış durum sözlüğü: `gonderime_hazir` (kullanıcı onayı), `gonderildi` (gerçek gönderim kanıtı), `yanit_bekleniyor`, `ilgileniyor`, `fiyat_gorusmesi`, `kazanildi`, `kaybedildi`, `takip_gerekli`. Bunlar zorunlu doğrusal sıra değildir; `teklif_taslagi_hazir` hiçbirine otomatik geçmez. Son iletişim/takip tarihleri, müşteri notu, teklif tutarı/para birimi, kayıp nedeni ve durum kanıtı kullanıcı tarafından tutulur; bilinmeyen tutar bilinmiyor kalır. Gerçek gönderim başlayacaksa ilgili iletişim kuralları o aşamada doğrulanır. Sohbete maddeleri “gönderdim” demek müşteri gönderimi kanıtı değildir.

Her işletme için minimal kayıt `business-index.jsonl` içinde işletme adı, varsa Maps ID/normalize telefon, adres anahtarı, eski durum ve kaynak dosyasıyla tutulur. Yeni aday karşılaştırması model çağrısından önce deterministik araçla yapılır; telefon tek başına şube birleştirmez. Lead score kaydı firma `durum-ve-kararlar.md` dosyasında ayrı `on_puan` ve `nihai_puan` alanlarıyla, beş ölçüt, kanıt/çıkarım/bilinmeyen, güven ve gerekçeyle tutulur. `teklif-goturulecekler.md` çalışma görünümünde lead score ve bant gösterilir. Günlük raporda görev başına kaç farklı Maps profili açıldığı ayrıca sayılır; sınır 10'dur. Bu plan değişikliği duraklatılmış otomasyonu etkinleştirmez.

Her gerçek firma için günlük/firma klasöründe **`maliyet.md` zorunludur**; [temsili maliyet şablonu](ornekler/2026-10-21/temsili-oto-atolyesi/maliyet.md) kullanılır. Koordinatör dahil her ajanın gerçek modeli, aşaması, kullanım ve çağrı/tekrar kayıtları, başarısız denemeler ve kalite kontrol giderleri kapsanır. Birim fiyat/para birimi/kaynak/kontrol tarihi, hesaplanan tutar ve firma toplamı yazılır. Maliyet kaydının tamamlanması tüm ücretlerin bilindiği anlamına gelmez; bilinmeyenler ve kısmi toplam açıkça etiketlenirse teslim sayılabilir. Paylaşılan koordinasyon/araştırma günlük raporda ayrı havuzdur; dağıtım yöntemi açıklanmadan firmaya yüklenmez ve iki kez toplanmaz. Bu sohbetin geçmiş/mevcut gerçek kullanım maliyeti erişilebilir değil; uydurulmaz.

Her gün `gunluk-rapor.md`; her firmada `arastirma-kanitlar.md`, `analiz-ve-brif.md`, `gorseller-manifesti.md`, `tasarim-ciktisi.md`, `teklif-taslagi.md`, `durum-ve-kararlar.md`, `maliyet.md` bulunur. Gerçek kanıt ve çıktı dosyaları firma klasörüne eklenir; manifestte kaynak URL, yerel yol, edinme zamanı, kullanım durumu ve gerekirse checksum tutulur. Eksik dosyaya mevcutmuş gibi bağlantı verilmez.

Durum akışı: `yapilandirma_bekliyor → arastiriliyor → uygun_aday → brif_hazir → tasarim_hazir → teklif_taslagi_hazir`. Son durum tüm teslimlerin doğrulandığını belirtir; gönderim incelemesi ayrıca bekler. Her aşamadan `duraklatildi`, `elendi` veya `mukerrer` durumuna geçilebilir. Kayda önceki/yeni durum, aktör, zaman, kanıt ve gerekçe yazılır. `gonderildi` bu planın bir çıktısı değildir; mesaj gönderme ayrı açık talimat gerektirir.

Kalıcı seri kaydı `D:\localhost\ekagent\customers\seri-durumu.md` içinde gerçek çalışma başlarken oluşturulur: seri kimliği, durum, hedef 3, doğrulanmış hazır firma kimlikleri/yolları, sektör seçimi/gerekçesi, konum, run/kilit/canlılık bilgisi, son tur ve sonraki adım tutulur. Firma `durum-ve-kararlar.md` kaydı ayrıca seri kimliği, firma kimliği, mevcut aşama, girdi/çıktı sürümleri, teslim yolları, doğrulama zamanı, aşama bazlı deneme sayıları ve engeli içerir. Bu teslim gerçek seri başlatmaz veya hazır teklif oluşturmaz.

Gün kapanışında liste ve rapor firma kimlikleriyle uzlaştırılır: taranan = uygun + elenen + mükerrer + duraklatılan; halen araştırılanlar ayrıca açık kayıt olarak gösterilir. Temsili örnekler bu hesaplara katılmaz.

Kapanışta ortak firma geçmişi kaynaklı son işlemle, sektör özeti ise tüm günlerdeki gerçek üretim/maliyet ve kullanıcı manuel takip kayıtlarından yenilenir. Satış durumlarının esas kaynağı günlük `takip.md` dosyasıdır; ortak özet kullanıcı hücrelerini değiştirmez. Oranlar açık pay/payda ve dönemle; kabul tutarı/tahsilat ayrı, AI maliyeti para birimi ve gerçek/tahmini/eksik ayrımıyla tutulur. Gönderim sıfırsa oran hesaplanamaz. Üç firmalık teknik pilottan satış puanı/sıralama çıkarılmaz; yalnız yetersiz veri/gözlem/ticari pilot sonucu ve kaynaklı not kullanılır. Bu dokümanlar çalışan dedup/kilit/senkronizasyon kodu değildir. Basit kayıt/hesap araçla, gerekirse Luna low; en fazla gerekçeli medium. Otomasyon duraklatılmış kalır.


