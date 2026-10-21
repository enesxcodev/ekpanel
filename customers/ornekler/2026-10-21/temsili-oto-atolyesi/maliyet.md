# TEMSİLİ firma maliyet kaydı / yeniden kullanılabilir şablon

Firma: ORNEK-OTO-001; seri: örnek, başlatılmadı. Bu dosya gerçek harcama veya satış verisi içermez. Araştırma/üretim akışı çalışmadı. Bu sohbetin ve koordinatörün mevcut/geçmiş çağrı bazlı kullanımına veya faturasına erişim yok; maliyeti **ölçülemiyor**, sıfır kabul edilmez. Aşağıdaki satırlar beklenen rollerin boş şablonudur, yapılmış çağrı değildir.

## Doğrudan firma çağrıları

Göreve yeterli ekonomik model kullanılır: araştırma/site kontrolü, sektör seçimi, standart analiz/brif ve teklif Luna low; veri çelişkisinde gerekirse Luna medium. Görsel/marka yorumu veya brif Luna ile yetersizse gerekçeli Sol low; medium yalnız gerçekten gerekirse. Kayıt ve hesaplar basit araçla, metin gerekiyorsa Luna low ile yapılır. High ve üstü yoktur. Gerçek model/ayar ile yükseltme nedeni bu maliyet kaydına ve durum/karar kaydına yazılır; mevcut yetenek/erişim yoksa engel kaydedilir.

Her gerçek çağrı için bir satır ekle; özetlemede aynı çağrı kimliğini ikinci kez sayma. Model alanına tercih edilen değil gerçekten kullanılan model/snapshot yazılır. Başarısız/iptal çağrı, yeniden üretim, düzeltme ve kalite kontrolü de kullanım/fatura varsa kapsanır; yoksa ücret belirsizliği korunur.

| Kayıt / çağrı kimliği | Ajan | Aşama | Gerçek model / reasoning low; gerekçeli ≤medium | Sonuç / çağrı–tekrar adedi | Input | Output | Cached read | Cache write 30m / 12h | Reasoning token | Kullanım kaynağı |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| Yok | Araştırmacı Luna | Araştırma | Çağrı yok | Çalışmadı / — | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Yok |
| Yok | Marka analisti | Analiz / içerik / brif | Çağrı yok; plan Luna low, yetersizlikte gerekçeli Sol low | Çalışmadı / — | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Yok |
| Yok | Teklif yazarı | Teklif | Çağrı yok; plan Luna low | Çalışmadı / — | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Yok |
| Yok | Denetçi | Kalite kontrolü / düzeltme | Çağrı yok | Çalışmadı / — | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Ölçülemiyor | Yok |

| Aynı kayıt kimliği | Birim fiyatlar ve birim / para birimi | Fiyat kaynağı / kontrol tarihi / tier–bağlam–bölge | Hesaplanabilen tutar | Tür / kapsam |
| --- | --- | --- | --- | --- |
| DOLDURULACAK | Bilinmiyor | Bilinmiyor | Ölçülemiyor | API liste tahmini / faturadan gerçek / ölçülemiyor; seçilecek |

Çağrı adedi toplam denemeleri içerir; tekrar adedi bunun alt kümesidir, çağrı toplamına yeniden eklenmez. Bir çağrının tahmini ve faturalanmış tutarı aynı harcamanın iki görünümüdür; birlikte toplanmaz.

## Fiyat referansı ve hesaplama sınırları

21.10.2026 kontrolünde [resmi API fiyat sayfasında](https://developers.openai.com/api/docs/pricing) Standard/kısa bağlam için USD / 1 milyon token:

| Model | Normal input | Cached read | Cache write 30m | Cache write 12h | Output |
| --- | --- | --- | --- | --- | --- |
| gpt-6.1-sol | 2.00 | 0.10 | 2.50 | 5.00 | 10.00 |
| gpt-6-luna | 0.10 | 0.01 | 0.125 | 0.25 | 0.50 |

Bu referans ücret ödendiğini veya bu sohbetin API ile faturalandığını göstermez. Gerçek çağrının modeli, işlem katmanı, bağlam uzunluğu, bölgesi ve geçerli fiyatı yeniden doğrulanır. Eşleşme/fiyat doğrulanamıyorsa tutar bilinmiyor kalır. ChatGPT/Codex abonelik kullanımından çağrı başına gerçek parasal fiyat çıkarılmaz; kullanım yüzdesi token fiyatı değildir. Doğrulanmış kaynağın para birimi kullanılır; TL dönüşümü yapılmadı. Gerekirse kur, tarih ve kaynakla ayrı dönüşüm tahmini tutulur; farklı para birimleri doğrudan toplanmaz.

[Cache muhasebesine](https://developers.openai.com/api/docs/guides/prompt-caching) göre input tokenları normal, cache okuma veya cache yazma fiyatından birine girer; cache write ek ücret gibi tekrar eklenmez. [Reasoning muhasebesinde](https://developers.openai.com/api/docs/guides/reasoning) reasoning tokenları output toplamının içindedir; output üzerine yeniden eklenmez. Sağlayıcının ham usage verisi ve alan tanımı saklanır.

Yalnız ayrık kırılımlar doğrulanmışsa: `tahmini token tutarı = (normal_input × fiyat + cached_read × fiyat + write_30m × fiyat + write_12h × fiyat + output_toplam × fiyat) / 1.000.000`. Normal input, sağlayıcının toplamından dahil olan cache kırılımları çıkarılarak bulunur; alanlar eksikse eksik kısmı varsayarak tam hesap yapma. Araç ücretleri ayrıca ve yalnız faturada token tutarına dahil değilse eklenir. API liste fiyatıyla hesaplanan daima **tahmini**; fatura/gerçek billing kaydıyla eşleşen **gerçek** etiketlidir.

## Image Gen — ayrı maliyet kalemi

| Çağrı kimliği / sonuç | Gerçek araç / varsa bildirilen üretici model | Çağrı / tekrar / çıktı adedi | Faturalama ölçüsü ve birim fiyat / para birimi | Kaynak / kontrol tarihi | Tutar / tür |
| --- | --- | --- | --- | --- | --- |
| Yok / çalışmadı | Image Gen; üretici model bildirilmedi | — / — / — | Bilinmiyor | Gerçek araç/billing verisi yok | Ölçülemiyor |

Görsel adedi tek başına fiyat değildir. Görsel yorumlayan Sol/Luna token maliyeti, görsel üreten aracın maliyeti değildir. Araç toplamı alt model ücretini içeriyorsa alt model ikinci kez eklenmez. Abonelik kotası API görsel ücreti sayılmaz. Başarısız çağrı/yeniden üretim maliyeti de mevcut billing verisiyle kaydedilir; Image Gen'e desteklemediği reasoning parametresi verilmez.

## Paylaşılan koordinasyon ve toplamlar

Birden fazla firmayı kapsayan koordinatör/ortak araştırma çağrıları günlük `gunluk-rapor.md` içindeki ortak maliyet bölümüne aynı çağrı/fiyat alanlarıyla kaydedilir. Yalnız bu firmaya ait koordinasyon doğrudan maliyettir. Bu sohbetin belge hazırlama maliyeti: ölçülemiyor; firma maliyetine keyfî dağıtılmadı.

| Özet (para birimi bazında) | Gerçek fatura kısmı | API liste tahmini kısmı | Kapsam / eksik |
| --- | --- | --- | --- |
| Ajan + aşama + gerçek model alt toplamları | Ölçülemiyor | Ölçülemiyor | Gerçek çağrı kaydı yok |
| Doğrudan metin / kalite / başarısız denemeler | Ölçülemiyor | Ölçülemiyor | Muhasebe verisi yok |
| Doğrudan Image Gen | Ölçülemiyor | Ölçülemiyor | Üretim yapılmadı |
| Ölçülen doğrudan kısmi toplam | Oluşmadı | Oluşmadı | Sıfır harcama iddiası değildir |
| Ortak maliyet dağıtım payı | Dağıtılmadı | Dağıtılmadı | Yöntem uygulanmadı |
| Firma toplamı | Ölçülemiyor | Ölçülemiyor | Tam toplam hesaplanamaz |

Dağıtım yapılırsa yöntem (ör. serideki 3 firma arasında eşit), ortak havuz tutarı/kaydı, pay, kalan ve kapsam yazılır. Seri toplamı ya doğrudan firma tutarları + ortak havuzdur ya da pay dahil firma toplamlarıdır; iki yöntem birlikte toplanmaz. Başka muhasebe temelleri/para birimleri ayrı tutulur. Rapor ölçülen kısmi toplamı ve hesaplanamayan tam toplamı açıkça ayırır; eksikler sıfır değildir.
