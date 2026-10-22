# Onay Gerektiren İşler

Bu dosya, modüllerin geliştirme sırasında kullanıcı onayı gerektiren kapsamlarını takip eder. Amaç; dış servis, API, hesap veya henüz mevcut olmayan altyapıya bağlanan işleri onay gelmeden başlatmamaktır. Genel uygulama kuralı [İş Akışı](İş%20Akışı.md) içindedir.

## Karar kuralı

- 6.1 SOL, her yeni modül planında dış API veya eksik altyapı gerektiren işleri burada kayıtlı kuralla uyumlu biçimde **Onay bekliyor** olarak işaretler; hangi servis ve kararların gerektiğini, bağımsız yerel kapsamı ve ertelenecek işi açıkça yazar.
- 5.6 TERA, ilgili kapsam için kullanıcıdan açık onay gelmeden entegrasyon kodu, gerçek dış işlem, API çağrısı, callback/webhook veya canlı sağlayıcı akışı geliştirmez. Onay gelmeyen işler `Bekliyor` kalır; bağımsız ve plana alınmış yerel işler ayrı ilerleyebilir.
- Onay, yalnız belirtilen servis ve kapsam için geçerlidir; GitHub push, release veya katalog yayını izni değildir.
- Bu kayıt zamanla yeni modül ve entegrasyon ihtiyaçlarıyla genişletilir. Kararlar tarih ve kapsamıyla eklenir; eski kararlar geçmiş olarak saklanır.

## Ertelenen entegrasyonlar

Store'un yerel uygulama işi tamamlandığı için aşağıdaki iki entegrasyon bu görev kapsamında kapatılıp ertelendi. Bunlar onaylanmış veya uygulanmış değildir. İleride yeniden ele almak için yeni bir Store işi ve ilgili entegrasyon için açık kullanıcı onayı gerekir.

| Modül | Kapsam                                                                                                                               | Neden onay gerekiyor                                                   | Onay gelene kadar yapılabilecekler                                                                               | Durum    |
| ----- | ------------------------------------------------------------------------------------------------------------------------------------ | ---------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------- | -------- |
| Store | Gerçek ödeme sağlayıcısı entegrasyonu: sağlayıcı seçimi, hesap/anahtar yapılandırması, ödeme başlatma, callback/webhook, iade API'si | CMS projesinde onaylanmış bir ödeme sağlayıcısı/altyapısı belirtilmedi | Ürün, sepet ve yerel sipariş kapsamı; ödeme sağlayıcısından bağımsız durumlar yalnız Store planında yer alıyorsa | Ertelendi — Store işi kapandı; yeni istek ve açık onay gerekir |
| Store | Harici kargo/taşıyıcı entegrasyonu: fiyat teklifi, etiket/gönderi oluşturma, takip ve iptal API'si | CMS projesinde onaylanmış bir kargo sağlayıcısı/altyapısı belirtilmedi | Ürün/sepet/sipariş kapsamı; manuel teslimat bilgileri ancak Store planında ayrıca seçilip tanımlanırsa | Ertelendi — Store işi kapandı; yeni istek ve açık onay gerekir |

## Onay kararı geçmişi

Store'un tamamlanan yerel uygulaması için ödeme veya kargo entegrasyonu onayı verilmedi; bu iki kapsam mevcut görevde ertelendi.

Yeni kayıt eklerken şu alanları kullan:

```markdown
### <Modül> — <entegrasyon veya kapsam>
- durum: Bekliyor | Onaylandı | Reddedildi | Ertelendi
- karar tarihi: <YYYY-AA-GG veya henüz yok>
- onaylanan sınır: <yalnız onaylanan servis ve işlevler>
- açık kararlar/önkoşullar: <servis seçimi, hesap, anahtar saklama, ülke/para birimi vb.>
- not: <gerekçe veya karar özeti>
```
