# Çalışma yapılandırması — doldurulacak şablon

Bu dosya çalışan otomasyon veya uygulama ayarı değildir. Alanlar gerçek çalışmadan önce doldurulur.

| Alan | Varsayılan / doldurulacak değer |
| --- | --- |
| Tarih ve saat dilimi | YYYY-MM-DD; Europe/Istanbul; zamanlar ISO 8601, +03:00 |
| Konum | İstanbul; ilçe/mahalle/yarıçap stratejisi öneri, kullanıcı incelemesi olmadan çalıştırma |
| Zamanlama sözleşmesi | Her 30 dakikada heartbeat; otomasyon kurulumu bu belge görevinin dışında |
| Seri hedefi | Tasarım kararı dosyası ve teklif metni dahil tüm teslimleri doğrulanmış toplam 3 TAMAMLANMIŞ paket; uygun aday sayısı değildir |
| Seri bitişi | 3 hazır teklifte dur; sonraki gün kendiliğinden yeni seri açma |
| Başlangıç dağılımı | Beş sektör havuzundan seçilen 3 sektör × 1 tamamlanmış teklif; ilk tur koordinatör sektör seçimi ve gerekçesini kalıcı kaydeder |
| Devam / çakışma | Bir turda tüm zincir zorunlu değil; kalıcı aşamadan sür; önceki run sürüyorsa yeni run başlatma |
| Kalıcı durum | Ana customers/seri-durumu.md ve günlük/firma klasöründeki durum-ve-kararlar.md; firma/aşama/girdi sürümüyle idempotency |
| Sektörler | Özel oto servisleri; vinç/forklift/iş makinesi kiralama; su kaçağı/tesisat; güzellik/bakım; endüstriyel soğutma/ticari mutfak servisi |
| Sektör değişimi | Mevcut sektör notlarından seçilmiş başlangıç varsayımı; kullanıcı değiştirebilir |
| Kota devri | Varsayılan kapalı; bir sektörde eksik varsa diğerinden doldurma, açığı raporla |
| Telefon | Maps profilinde işletme telefonu mevcut; kaynak kaydedilir, arama yapılmaz |
| Fotoğraf / yorum eleme kriteri | Fotoğraf veya yorum sayısı zorunlu eleme eşiği değildir; yalnız içerik ve tasarım stratejisini belirler |
| Fotoğraf stratejisi | 6+: gerçek fotoğraflarla kişiselleştir; 2–5: mevcut görselleri kullan; 0–1: eksik varlıkları belirten sektörel konsept |
| Yorum stratejisi | 20+: kaynaklı örneklerde tekrarlanan temaları analiz et; 1–19: sınırlı mevcut kanıttan çıkarım yap; 0: referans/değerlendirme üretme |
| Lead score | 100 puan: ödeme kapasitesi 25, web ihtiyacı 25, Google müşteri kazanma potansiyeli 20, dijital görünürlük eksikliği 15, iletişime geçilebilirlik 15; Ön eleme `on_puan` ayrı; 60+ veya belirsiz/eksik veri detaylı incelemeye geçer; nihai `nihai_puan` Luna low önerir, koordinatör toplamı doğrular |
| Lead score karar bantları | 75–100: tasarım kararı dosyası ve teklif taslağı; 50–74: aday havuzu; 0–49: şimdilik tasarım/teklif yok; müşteri gönderim onayı ayrı kalır |
| Rakip/benzer site incelemesi | Önce sektörel referanslar; yetersiz/eskiyse isteğe bağlı en çok 1 rakip sitesinin yalnız ana sayfası; URL/tarih ve soyut öneri; kopyalama yok |
| Site kontrolü geçerliliği | Öneri: 7 gün; tasarım/teklif incelemesinden önce süre dolmuşsa yenile |
| Görev başına Maps tarama sınırı | En fazla 10 farklı Maps işletme profili; aynı profili tekrar açmak yeni profil sayılmaz; 10'da durup sonucu raporla |
| Yeniden deneme | Geçici erişim hatasına en çok 1 tekrar; kalıcı belirsizlikte duraklat |
| Ekonomik metin modeli | GPT-6 Luna (`gpt-6-luna`) low; araştırma, site doğrulama, veri toplama, sektör seçimi, standart analiz/brif ve kısa teklif |
| Model yönlendirme doğruluğu | Bu alanlar model önerisidir; ortam belirli modeli seçmeyi desteklemiyorsa seçim yapıldığını iddia etme, fiilen kullanılan modeli ve önerilen modeli ayrı raporla |
| Kayıt / hesap | Basit araç ve hesap; metin ihtiyacında Luna low; takipte kullanıcı hücreleri korunur |
| Sol kullanımı | GPT-6.1 Sol (`gpt-6.1-sol`) low yalnız Luna'nın görsel/marka yorumu veya brif kalitesi yetersizse; nedeni karar ve maliyet kaydına yaz |
| Görsel üretim | Otomatik üretim kapalı; 75+ aday için `tasarim-karari.md` ve ayrıntılı ImageGen promptu hazırlanır, araç çağrılmaz |
| Görsel deneme sınırı | Firma başına ilk tasarım + en çok 1 gerekçeli düzeltme |
| Bütçe | Günlük tutar, para birimi, firma başı tavan: DOLDURULACAK; belirlenmeden ücretli akış başlatma |
| İletişim | Yalnız taslak; otomatik gönderim kapalı |
| Pilot sonrası | İlk pilot 3 hazır teklifte durur; otomatik 5'e yükseltme ve ertesi gün yeni seri yok; kullanıcı karar verir |
| Reasoning | Varsayılan low veya desteklenen daha düşük seviye; veri çelişkisinde Luna medium, Sol için de medium yalnız gerçekten gerekirse; high/xhigh/max/ultra ve üstü yok |
| Model yükseltme | Gerekçeli Sol low kullanımı ve yalnız ihtiyaçta medium; tavan aşılamaz; Image Gen'e desteklemediği reasoning parametresi verilmez |
| Kullanım ölçümü | Aşama/model/çağrı bazında mevcut gerçek input/output/cached/reasoning tokenları; erişilemeyen ölçülemiyor; görsel çağrı/adet/maliyet ayrı |
| Pilot son raporu | Ölçülen toplamlar ve kapsamı, eksik ölçümler, darboğazlar ve düzeltme önerileri; tahmin/fiyat uydurma yok |

Konum, sektör veya bütçe boşsa bu doküman tamamlanabilir; gerçek çalışma `yapilandirma_bekliyor` durumunda kalır.

Ticari paket kaynağı: [ticari-kosullar.md](ticari-kosullar.md); henüz kullanıcı onayı/sürüm/değer yoktur. Ajan koşul üretmez; firma ticari teklifine yalnız onaylı sürümü kopyalar. Eksik fiyat/şartlar teknik pilot belgelerinin hazırlanmasını engellemez, ticari teklifin hazır sayılmasını engeller.

Her firma için günlük/firma klasöründe `maliyet.md` zorunlu; [maliyet şablonu](ornekler/2026-10-21/temsili-oto-atolyesi/maliyet.md) kullanılır. API liste hesabı tahmini, billing/fatura tutarı gerçek olarak ayrılır; ortak koordinasyon günlük raporda ayrı tutulur. Bu sohbetin çağrı bazlı gerçek maliyeti ölçülemiyor.

Pilot türü: 3 farklı işletmeyle teknik süreç/kalite/zaman/token-maliyet ölçümü; sektör satış çıkarımı yapılmaz. Tek sektörde 25 işletmelik ticari pilot yalnız sonraki öneridir; yanıt/ciddi görüşme/kabul/gelir ölçümü ve mesaj gönderimi için ayrı kullanıcı talimatı gerekir. Otomasyon duraklatılmıştır; bu belge çalıştırmaz.

