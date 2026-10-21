# TEMSİLİ durum ve karar kaydı

Firma kimliği: ORNEK-OTO-001. Veri türü: tamamen kurgusal. Son durum: `ornek_veri` (gerçek akış durumlarından ayrıdır).

| Zaman | Aktör / model | Önceki → yeni durum | Karar / gerekçe | Kanıt |
| --- | --- | --- | --- | --- |
| 2026-10-21; gerçek işlem saati yok | Doküman yazarı; akış ajanı çalışmadı | Yok → ornek_veri | Klasör yapısını göstermek için temsili kayıt | Araştırma dosyasındaki uyarı |
| Temsili; gerçek kontrol yok | Analist rolü örneği | ornek_veri → ornek_veri | Marka rengi bilinmiyor; hizmet görsellerinden çıkarım yapılmaz | F1–F6 dosyaları yok |
| Temsili; gerçek kontrol yok | Koordinatör rolü örneği | ornek_veri → ornek_veri | Site kontrolü yok, beş gerçek yorum ve altı fotoğraf yok; üretime devredilemez | K1–K5 boş, Y1–Y5 temsili |

Tasarım: üretilmedi. Teklif: yalnız koşullu metin örneği. Gönderim: yapılmadı. Model yükseltme ve ücretli görsel çağrısı: yapılmadı.

Gerçek kullanım karar şablonu: `[ISO zaman] | [aktör/model] | [önceki → yeni durum] | [gerekçe] | [kanıt kimliği/yolu] | [sonraki işlem/sorumlu]`.

Gerçek kullanım kalıcı devam alanları: seri/firma kimliği; run kimliği; mevcut ve sonraki aşama; girdi sürümü; doğrulanmış çıktı yolları/sürümleri; aşama bazlı toplam deneme sayısı; devam eden araç çağrı kimliği; engel; son doğrulama zamanı. Heartbeat bu alanlardan devam eder; tamamlanan aynı aşama tekrar üretilmez. Seri kilidi ana `seri-durumu.md` kaydından kontrol edilir; başka run aktifse çalışma açılmaz.

Hazır kontrolü: araştırma/kaynaklar, 5 gerçek yorum, 6+ gerçek referans görseli ve manifest, doğrulanmış analist brifi, gerçek tasarım dosyası ve görsel inceleme, Luna teklif metni, karar/durum kayıtları: bu örnekte EKSİK. Serinin 3 tamamlanmış teklif kotasına katkısı: 0. Şehir İstanbul; ilçe stratejisi kullanıcı incelemesi bekler, gerçek araştırma duraklatılmıştır. Bu örnek yeni seri veya otomasyon başlatmaz.

Gerçek firma için yeni kimlik açılır; temsili yorumlar, sayılar ve kararlar kopyalanıp gerçekmiş gibi kullanılmaz.

Zorunlu teslim: [maliyet.md](maliyet.md). Bu örnekte kayıt şablonu hazır; gerçek kullanım ve fatura eksik, firma tam toplamı ölçülemiyor. Gerçek firmada çağrı kimliği bazında maliyet güncellenmeden aşama kapanmaz; fiyatı bilinmeyen alan açıkça işaretlenir. Bu, gerçek tasarım ve diğer eksik teslimlerin yerine geçmez.
