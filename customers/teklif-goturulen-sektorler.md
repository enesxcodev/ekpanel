# Ortak sektör özeti — BOŞ ŞABLON

Henüz gerçek faaliyet/satış kaydı yoktur; tablodaki boşluklar sıfır değildir. Tüm sektörlerin gerçek kayıtlarından türetilir. Kaynaklar: [firma geçmişi](firma-gecmisi.md), firma üretim/maliyet dosyaları, kullanıcının doldurduğu günlük `takip.md` ve varsa gerçek kabul/tahsilat kanıtları. Temsili örnekler dahil edilmez. Son derleme zamanı / gözlem dönemi / gönderim kohortu: DOLDURULACAK.

| Sektör | Hazır paket | Gönderilen | Yanıt | Ciddi görüşme | Kazanılan | Kaybedilen | Bekleyen | Kaynaklar / bilinmeyen kayıt sayısı | Değerlendirme / kanıta dayalı kısa not |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| DOLDURULACAK | Bilinmiyor | Bilinmiyor | Bilinmiyor | Bilinmiyor | Bilinmiyor | Bilinmiyor | Bilinmiyor | Gerçek kayıt yok | Yetersiz veri |

| Sektör / aynı gözlem kohortu | Yanıt oranı: yanıt / gönderilen | Görüşme oranı: ciddi görüşme / gönderilen | Kazanım oranı: kazanılan / gönderilen | Pay / payda / dönem / kapsam |
| --- | --- | --- | --- | --- |
| DOLDURULACAK | Hesaplanamaz | Hesaplanamaz | Hesaplanamaz | Gerçek veri yok |

Hazır paket tüm teslimleri doğrulanmış benzersiz firmadır; gönderim ancak kullanıcının gerçek gönderim kaydıyla sayılır. Yanıt, aynı gönderim kohortundan en az bir müşteri yanıtı kanıtı olan benzersiz firmadır. Ciddi görüşme, kullanıcı notunda somut ihtiyaç/kapsam/fiyat değerlendirmesi kanıtlanan firmadır; sadece taslak veya yanıtsız mesaj sayılmaz. Kazanılan/kaybedilen kullanıcı sonuç kaydına dayanır. Bekleyen, gönderilmiş olup kapanış sonucu bulunmayan ve son durumu bilinen firmadır; eksik durumlar ayrıca bilinmiyor olarak raporlanır. Tarihsel aşamalar birbirleriyle örtüşebilir, sütunlar gelişigüzel toplanmaz.

Oranlar aynı dönem/kohort için benzersiz firma bazında hesaplanır; pay/payda ve gözlem bitişi yazılır. Gönderilen 0 ise oran hesaplanamaz; veri eksikse oran eksik/kısmi olarak etiketlenir, sıfır yazılmaz. Alternatif payda kullanılırsa adı açıkça değiştirilir. Gözlem süresi farklı kohortlar doğrudan karşılaştırılmaz.

| Sektör | Kabul edilen teklif tutarı / para birimi / kanıt | Gerçek tahsilat / para birimi / kanıt | Doğrudan AI gerçek kısmı | API liste tahmini kısmı | Ortak AI payı / yöntem | Ölçüm kapsamı / eksik tutar / maliyet kaynakları |
| --- | --- | --- | --- | --- | --- | --- |
| DOLDURULACAK | Bilinmiyor | Bilinmiyor | Ölçülemiyor | Ölçülemiyor | Dağıtılmadı | Gerçek veri yok |

Kabul tutarı tahsil edilmiş gelir değildir; yalnız ödeme kanıtı gerçek tahsilata girer. Para birimleri ayrı tutulur. AI maliyeti gerçek/tahmini/ölçülemeyen ve kısmi/tam toplam ayrımıyla [maliyet şablonuna](ornekler/2026-10-21/temsili-oto-atolyesi/maliyet.md) uyar; ortak havuz ya bir kez ayrıca tutulur ya açıklanan yöntemle dağıtılır, iki kez toplanmaz. Bilinmeyen maliyet sıfır veya satış fiyatı sayılmaz.

Değerlendirme yalnız `yetersiz veri`, `gözlem` veya `ticari pilot sonucu` ve kısa kaynaklı nottur. Üç farklı işletmelik TEKNİK pilot workflow/kalite/süre/token-maliyet içindir; sektör satış puanı, keyfi ağırlıklı skor veya başarı sıralaması üretmez. Sonraki tek sektörde 25 işletmelik ticari pilot yalnız öneridir, başlamadı; kullanıcı kararı gerekir. Sektör özeti sonraki araştırmada bağlamdır, aynı sektörün diğer firmalarını eleme kuralı değildir.

Gönderim ve müşteri yanıtı kullanıcı tarafından manuel kaydedilir; kaynak hücreleri otomasyonca ezilmez. Hesap/derleme önce basit araç, gerekirse Luna low; en fazla gerekçeli medium. Bu dosya canlı hesaplama veya iletişim otomasyonu değildir; otomasyon duraklatılmış kalır.
