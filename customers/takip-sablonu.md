# Günlük seri manuel takip şablonu

Gerçek kullanımda bu dosyayı `customers/YYYY-MM-DD/takip.md` olarak oluştur; firma/teklif bağlantılarını o günlük klasöre göre doldur. Bu bir iletişim otomasyonu değildir. Teklifleri kullanıcı manuel gönderir; müşteri yanıtını, tutarı ve sonucu kullanıcı elle girer. Kullanıcının manuel durumları esastır.

Seri kimliği: DOLDURULACAK. Hazır paket sayımı satış durumundan bağımsızdır.

Firma kimliği [ana firma geçmişiyle](firma-gecmisi.md) eşleşir; bu günlük kullanıcı kaydı satış durumunun esas kaynağıdır. Ortak firma görünümü ve [sektör özeti](teklif-goturulen-sektorler.md) yalnız buraya bağlantı veren türetilmiş özetlerdir, kullanıcı hücrelerini değiştirmez. Günlük dosyaya kopyalanınca bu bağlantılar `../firma-gecmisi.md` ve `../teklif-goturulen-sektorler.md` olarak ayarlanır. Kullanıcı isterse gerçek kabul ve ayrı tahsilat tutarı/para birimi/tarih/kanıtını müşteri notuna ekler; kazanım durumu tek başına tahsilat kanıtı değildir.

| Firma / klasör bağlantısı | Teklif dosyası | Durum | Gönderim tarihi | Son iletişim tarihi | Müşteri yanıtı / notu | Sonraki takip tarihi | Teklif tutarı / para birimi | Kayıp nedeni | Durum kanıtı |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| DOLDURULACAK | DOLDURULACAK | teklif_taslagi_hazir | — | — | — | — | Bilinmiyor | — | Hazır paket kontrol kaydı |

Otomasyon yalnız doğrulanmış hazır firma/teklif bağlantıları ve `teklif_taslagi_hazir` durumuyla yeni satır açabilir; mevcut satır ve kullanıcı hücreleri asla ezilmez. Firma/seri kimliğiyle mükerrer satır engellenir. Gönderim, yanıt, tutar ve sonuç alanları kullanıcıya aittir; otomasyon bunları uydurmaz veya değiştirmez. Kullanıcının bu sohbete maddeleri “gönderdim” demesi müşteriye gönderim kanıtı değildir.

| Durum | Kısa anlam |
| --- | --- |
| teklif_taslagi_hazir | Tam paket hazır; gönderim yapılmış sayılmaz. |
| gonderime_hazir | Kullanıcı insan incelemesini/onayını kaydetti. |
| gonderildi | Kullanıcı gerçek gönderim tarihi ve kanıtını kaydetti. |
| yanit_bekleniyor | Kullanıcı gönderimden sonra yanıt beklediğini kaydetti. |
| ilgileniyor | Kullanıcının kaydettiği gerçek müşteri ilgisi var. |
| fiyat_gorusmesi | Kullanıcı fiyat görüşmesi yapıldığını kaydetti. |
| kazanildi | Kullanıcı kabul/sonuç kanıtını kaydetti; gelir ayrıca gerçek kayda dayanır. |
| kaybedildi | Kullanıcı olumsuz sonucu ve varsa kayıp nedenini kaydetti. |
| takip_gerekli | Kullanıcı sonraki iletişim ihtiyacını kaydetti; otomatik mesaj yetkisi vermez. |

Durumlar her zaman doğrusal değildir. Hazır taslak otomatik gönderim/onay/kazanım yaratmaz. Bilinmeyen tutar boş veya bilinmiyor kalır; takip tarihi yalnız kayıt alanıdır. WhatsApp, mesaj gönderimi ve otomatik müşteri takibi kurulmaz. Gerçek gönderim aşamasında ilgili iletişim kuralları ayrıca doğrulanır.
