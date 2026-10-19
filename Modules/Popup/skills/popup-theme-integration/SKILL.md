---
name: popup-theme-integration
description: EkAgent Popup modülünün zamanlanmış duyuru kayıtlarını ve ziyaretçi durumuna göre gösterim uygunluğu servisini gelecekteki tema entegrasyonunda kullan.
---

# Popup okuma ve gösterim uygunluğu

[Modül rule'unu](../../.ai/rules/tema-entegrasyonu.md), [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [ortak modül kurallarını](../../../../.ai/rules/moduller.md) oku. Duyuru/kampanya/bilgilendirme başlıkları, bağımsız içerik ve isteğe bağlı görsel bu modül için ipucudur; görsel düzen veya kelime tek başına kesin eşleme değildir. Belirsiz modül/alan eşleşmesini kullanıcıya sor. Gösterim biçimi bu backend sürümünde kararlaştırılmamıştır; modal veya sayfa içi görünüm varsayma.

## Keşif ve kullanım

Sınıf: `Modules\Popup\Services\PopupFrontendService`, dosya: `app/Services/PopupFrontendService.php`. Model: `Modules\Popup\Models\Popup`. Servis bağımsız somut sınıftır; container çözebilir, facade/helper gerektirmez.

- `published(?DateTimeInterface $at = null): Illuminate\Database\Eloquent\Collection<int, Popup>`: aktif ve yayın aralığında olan tüm kayıtları getirir. Ziyaretçi durumu dikkate alınmaz. `imageMedia` eager load edilir.
- `eligible(array $visitorStates = [], ?DateTimeInterface $at = null): Collection<int, Popup>`: yayınlanan kayıtları ziyaretçi durumuyla filtreler. Dizi kayıt ID'siyle anahtarlanır; her değer `array{closed_at?: ?DateTimeInterface, last_shown_at?: ?DateTimeInterface}` biçimindedir. Eksik kayıt/alan, bilinen olay bulunmadığı anlamına gelir. JSON/string zamanlarını bu metoda vermeden önce entegrasyon sınırında doğrula ve saat dilimi açık tarih nesnelerine çevir.
- `shouldShow(Popup $popup, ?DateTimeInterface $closedAt = null, ?DateTimeInterface $lastShownAt = null, ?DateTimeInterface $at = null): bool`: eldeki modelin aktifliğini, yayın zamanını ve ziyaretçi durumunu değerlendirir. Modeli yeniden sorgulamaz; güncel model için `published`/`eligible` kullan. Model parametresi kayıt seçimi veya ziyaretçi olayını kaydetme işlemi değildir.

`$at` verilmezse güncel an kullanılır; açık tarih parametresi test veya belirli bir anı değerlendirmek içindir. Tarihler uygulamanın `config('app.timezone')` saat diliminde değerlendirilir; admin formundaki datetime-local değerleri de bu saat dilimine aittir. Başlangıç dahil (`starts_at <= at`), bitiş hariçtir (`ends_at > at`). Çakışan kayıtların sırası `starts_at DESC, id DESC` olur; filtreleme bu sırayı korur. Servis limit, tek kayıt seçimi veya eşzamanlı gösterim sayısı belirlemez.

Kod kurulu ama modül bulunamıyorsa/devre dışıysa veya `popups` tablosu henüz yoksa çoklu metotlar boş koleksiyon, `shouldShow` false döndürür. Modülün kodu hiç kurulu değilken sınıfı container'a zorunlu constructor bağımlılığı olarak ekleme. Çağıran çekirdek akışta sınıf varlığını çözümlemeden önce kontrol et:

```php
$class = \Modules\Popup\Services\PopupFrontendService::class;
$popups = class_exists($class)
    ? app($class)->eligible($visitorStates)
    : collect();
```

Bu örnekte `$visitorStates` çağıranın doğruladığı olay durumudur. Hazırlanan `$popups` ilgili view'a açık veri olarak aktarılır. Bu yönerge frontend render entegrasyonu yapma talimatı değildir.

## Tekrar gösterim sözleşmesi

- `never_after_close`: `closedAt` null ise uygun, herhangi bir kapatma zamanı varsa uygun değildir. Son gösterim tek başına engellemez. Kapatma geçmişi aynı kayıt ID'si için içerik düzenlenince otomatik sıfırlanmaz.
- `interval`: admin `repeat_interval` pozitif tam sayısını ve `repeat_unit` (`hours`/`days`) değerini belirler. Referans, `lastShownAt` ve `closedAt` zamanlarından daha yenisidir. İkisi de yoksa ilk gösterim uygundur. Referans + aralık tam olarak dolduğunda tekrar uygundur. Saat 3600, gün 86400 saniyedir; gün takvim/gece yarısı anlamına gelmez. Gelecek tarihli olay süre dolana kadar gösterimi engeller.
- Bilinmeyen politika veya geçersiz aralık güvenli biçimde uygun değildir. Yayın penceresi ve aktiflik her politikada önceliklidir; aralık dolsa bile süresi bitmiş kayıt gösterilmez.

## Entegrasyon sınırı

Servis salt okur ve hesaplar. PHP tarayıcıda kapatma veya görünür olma olayını kendiliğinden algılamaz. Gelecekteki entegrasyon hangi anı “gösterildi” sayacağını belirlemeli, bu olayı ve kapatmayı kaydetmeli, sonraki istekte kayıt ID'siyle durumu servise aktarmalıdır. Kalıcılık yöntemi (örneğin cookie/session veya istemci depolaması), süresi ve doğrulaması bu sürümde uygulanmamıştır. Durum kaybolur/aktarılmazsa yeni ziyaretçi gibi değerlendirilir; tarayıcılar veya cihazlar arasında kalıcı engelleme iddiasında bulunma. Listeyi okumak veya uygunluk hesaplamak bir gösterim/kapatma kaydı oluşturmaz. Frontend görünümü, tetikleme ve aynı anda gösterilecek kayıt adedi sonraki entegrasyonun kararlarıdır.

## Admin ve dinamik alan akışı

`SavePopupRequest` çekirdek alanları ve `ModuleFeatures::form('Popup', 'popup')` tanımlarını doğrular; `PopupController` ayrı create/edit ekranları için `FeatureValues::fields` hazırlar. Form `admin.features.fields` bileşenini kullanır. `PopupService::save` ortak `FieldFiles`, `FeatureValues::prepare` ve medya kullanım senkronizasyonuyla `extra_fields` değerlerini saklar; `delete` ilgili kullanımları temizler. `features.json` başlangıçta boş alan listesi sunar. Şemayı burada çoğaltma; genişletirken ortak modül kurallarına başvur.
