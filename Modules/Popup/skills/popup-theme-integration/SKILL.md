---
name: popup-theme-integration
description: EkAgent Popup modülünün zamanlanmış ve dile göre çevrilen duyurularını, ziyaretçi durumuna göre gösterim uygunluğu servisini kullan.
---

# Popup okuma ve gösterim uygunluğu

[Modül rule'unu](../../.ai/rules/tema-entegrasyonu.md), [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [ortak modül kurallarını](../../../../.ai/rules/moduller.md) oku. Duyuru/kampanya/bilgilendirme başlıkları, bağımsız içerik ve isteğe bağlı görsel bu modül için ipucudur; görsel düzen veya kelime tek başına kesin eşleme değildir. Belirsiz modül/alan eşleşmesini kullanıcıya sor. Gösterim biçimi bu backend sürümünde kararlaştırılmamıştır; modal veya sayfa içi görünüm varsayma.

## Keşif ve kullanım

Sınıf `Modules\Popup\Services\PopupFrontendService`, model `Modules\Popup\Models\Popup`'tır. Servis bağımsız somut sınıftır; container çözebilir, facade/helper gerektirmez.

- `published(?DateTimeInterface $at = null, Language|string|null $language = null): Collection<int, Popup>` aktif ve yayın aralığındaki kayıtları döndürür. `imageMedia` ve dil çevirileri yüklenir.
- `eligible(array $visitorStates = [], ?DateTimeInterface $at = null, Language|string|null $language = null): Collection<int, Popup>` yayınlanan kayıtları ziyaretçi durumuna göre filtreler. Dizi Popup ID'siyle anahtarlanır; her değer `array{closed_at?: ?DateTimeInterface, last_shown_at?: ?DateTimeInterface}` biçimindedir. JSON/string zamanlarını doğrulayıp saat dilimi açık tarih nesnelerine dönüştür.
- `shouldShow(Popup $popup, ?DateTimeInterface $closedAt = null, ?DateTimeInterface $lastShownAt = null, ?DateTimeInterface $at = null, Language|string|null $language = null): bool` aktiflik, yayın aralığı, çeviri varlığı ve ziyaretçi durumunu değerlendirir. Modeli yeniden sorgulamaz; güncel seçim için `published`/`eligible` kullan.

`$language` aktif dil kodu veya `App\Models\Settings\Language` modelidir. Verilmezse `popup_language` request niteliği, yoksa `tr` kullanılır. İstenen dil aktif değilse veya çevirisi yoksa aktif Türkçe çevirisine dönülür; ikisi de yoksa kayıt döndürülmez/gösterilmez. Servis `selectedTranslation` relation'ını ayarlar; `title`, `content`, `extra_fields` bu seçili çeviriden okunur. Görsel, aktiflik, tarihler ve tekrar politikası ortaktır. Visitor-state, Popup ID'siyle anahtarlanır ve diller arasında paylaşılır.

`$at` verilmezse güncel an kullanılır; tarihler uygulama saat diliminde değerlendirilir. Başlangıç dahil, bitiş hariçtir. Sıralama `starts_at DESC, id DESC` olur. Servis limit veya aynı anda gösterilecek kayıt sayısını belirlemez.

Modül etkin değilse/kod bulunmuyorsa veya `popups`/`popup_translations` tabloları yoksa çoklu metotlar boş koleksiyon, `shouldShow` false döndürür. Modül kodu kurulu değilken sınıfı zorunlu constructor bağımlılığı yapma; çağıran çekirdek akışta sınıfı çözümlemeden önce kontrol et:

```php
$class = \Modules\Popup\Services\PopupFrontendService::class;
$popups = class_exists($class)
    ? app($class)->eligible($visitorStates, language: 'en')
    : collect();
```

## Tekrar gösterim sözleşmesi

- `never_after_close`: `closedAt` null ise uygun, kapatma zamanı varsa uygun değildir. Son gösterim tek başına engellemez.
- `interval`: `repeat_interval` pozitif tam sayıdır; `repeat_unit` `hours` veya `days` olur. Referans son gösterim ve kapatmadan daha yenisidir. İkisi de yoksa ilk gösterim uygundur. Aralık tam dolduğunda tekrar uygundur; gün 86400 saniyedir.
- Bilinmeyen politika veya geçersiz aralık uygun değildir. Aktiflik ve yayın aralığı her politikada önceliklidir.

## Entegrasyon sınırı

Servis salt okur; tarayıcıda kapatma/görünür olma olayını algılamaz veya kaydetmez. Entegrasyon bu olayları Popup ID'siyle saklayıp sonraki istekte servise aktarmalıdır. Kalıcılık yöntemi, süresi ve doğrulaması bu modülde uygulanmaz. Olay aktarılmazsa ziyaretçi yeni kabul edilir. Listeyi okumak veya uygunluk hesaplamak gösterim/kapatma kaydı oluşturmaz. Frontend görünümü ve tetikleme bu sürümde yoktur.

## Admin ve dinamik alan akışı

`SavePopupRequest` aktif `lang_id`, çeviri alanlarını ve `ModuleFeatures::form('Popup', 'popup')` tanımlarını doğrular. `PopupController` yalnız aktif dilleri listeler; formdaki başlık, içerik ve feature değerleri seçili `PopupTranslation` kaydından gelir. `PopupService::save` tarih, aktiflik, tekrar ve görseli ortak Popup kaydına; içerik alanlarını çeviriye yazar. Dinamik dosyalar dil başına saklanır, medya kullanımı translation sahibine bağlanır ve Popup silinirken her çevirinin kullanımları temizlenir. `features.json` şemasını tekrarlama; genişletirken ortak modül kurallarına başvur.
