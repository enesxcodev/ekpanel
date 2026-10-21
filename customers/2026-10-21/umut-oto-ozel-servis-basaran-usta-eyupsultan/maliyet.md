# Firma maliyet kaydı — gerçek çalışma, ölçüm eksik

Seri PILOT-20261021-01; firma UMUT-OTO-OZEL-SERVIS-BASARAN-USTA-EYUPSULTAN-20261021. Bu kayıt gerçek harcama tutarı bulunduğu anlamına gelmez. Kullanılan ortam Codex/ChatGPT araçlarıdır; çağrı bazlı fatura, ham token usage ve servis katmanı erişilebilir değil. Bu nedenle API liste fiyatıyla gerçek ödeme hesaplanmadı; abonelik kullanımı token fiyatı sayılmadı.

| Ajan / aşama | Gerçek model / reasoning | Çağrı / tekrar adedi | Input / output / cached / cache-write / reasoning token | Kullanım/fatura kaynağı | Birim fiyat / para birimi / kontrol tarihi | Tutar / tür |
| --- | --- | --- | --- | --- | --- | --- |
| Araştırmacı / site ve Maps doğrulama | GPT-6 Luna low | Bir görev turu; araç çağrı adedi ölçülemiyor | Ölçülemiyor | Delege görev ve araştırma dosyaları; billing yok | Bu ortamda çağrı fiyatı bilinmiyor | Ölçülemiyor |
| Analist / brif ve teklif yazarı / kısa teklif | GPT-6 Luna low | Bir devam görevi; ham çağrı adedi ölçülemiyor | Ölçülemiyor | Delege görev; billing yok | Bilinmiyor | Ölçülemiyor |
| Koordinatör / dosya ve görsel kalite kontrolü | Bu sohbet; model billing kimliği/ayar telemetrisi erişilebilir değil | Ölçülemiyor | Ölçülemiyor | Sohbet; fatura yok | Bilinmiyor | Ölçülemiyor; ortak koordinasyondan ayrıştırılamıyor |

## Image Gen — metin maliyetinden ayrı

Durum: henüz çağrılmadı. Gerçek araç: yerleşik image_gen.imagegen. Üretici model/ücret tarifesi araç yanıtında sunulmadıkça bilinmiyor. Çağrı/çıktı/yeniden üretim adedi üretim sonrası güncellenecek; fatura tutarı erişilemiyorsa ölçülemiyor kalır. Görsel yorumlama tokenı, görsel üretim bedeli değildir.

## Hesaplama ve toplam

[Resmi API fiyatı](https://developers.openai.com/api/docs/pricing), [cache](https://developers.openai.com/api/docs/guides/prompt-caching) ve [reasoning](https://developers.openai.com/api/docs/guides/reasoning) muhasebesi 21.10.2026 tarihinde belge aşamasında kontrol edildi; referans birim fiyatlar [maliyet şablonunda](../../ornekler/2026-10-21/temsili-oto-atolyesi/maliyet.md) bulunur. Bu kaydın gerçek çağrı faturalamasına uygulanabildiği doğrulanmadığı ve usage eksik olduğu için fiyat aktarılıp tutar uydurulmadı.

Gerçek fatura tutarı: ölçülemiyor. API liste tahmini: hesaplanmadı (usage/katman eksik). Ölçülen parasal kısmi toplam: yok; sıfır harcama iddiası değildir. Firma tam toplamı: **ölçülemiyor**. Para birimi: gerçek billing için bilinmiyor; referans API tarifesi USD. TL dönüşümü yok.

Ortak koordinasyon/araştırma havuzu [günlük raporda](../gunluk-rapor.md) tutulur; bu firmaya keyfî pay dağıtılmadı. Koordinatörün yukarıdaki satırı kapsam bildirir, eklenebilir ikinci ücret değildir. Sağlayıcı toplamı/alt ajan toplamı aynı çağrı kimliğiyle iki kez toplanmaz. Cached ve cache-write input alt kırılımları, reasoning output alt kırılımıdır; ücret iki kez sayılmaz. Başarısız araç çağrıları, kalite kontrolü ve yeniden üretim kapsam içidir; usage/fatura olmadığı için ücretleri ayrı uydurulmadı. Eksikler kapanış raporunda korunur.
