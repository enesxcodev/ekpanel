---
name: events-theme-integration
description: Etkinlik teması verisini Events modülünden eşlemek için kullan.
---

# Events

## Ne zaman kullanılır

Tema etkinlik, seminer, buluşma veya katılım formu içerdiğinde kullan. Bölümün anlamını ve görsel ipuçlarını mevcut tema kapsamıyla değerlendir; yalnız anahtar kelime eşleştirmesiyle karar verme. `Event`, `EventTranslation` ve `EventRegistration` alanlarını koddan doğrula. Eşleşme belirsizse kullanıcıya sor.

## Veri sınırı

Başlık, mekân ve açıklama `EventTranslation`'dan; başlangıç/bitiş, kapasite ve aktiflik ortak `Event` kaydından gelir. Etkinlik kartında başlık, mekân, başlangıç/bitiş saati ve kapasite yer alabilir. Seçili dilde çeviri yoksa başka dil metnini gösterme; etkinliği içerik listesinden çıkar. Kayıt verisi kişiseldir; katılımcı listesi veya durumları tema üzerinden gösterilmez.

## Uygulama

Yalnız aktif etkinlikleri kullan. Kayıtlar aynı `event_id` ile etkinliğe bağlı kalır; kapasite doluysa kayıt eylemini devre dışı bırak ve sunucuda kapasiteyi doğrula. Events modülünde public route veya frontend okuma servisi yoktur; gerçek tüketici bulunmadan endpoint veya servis imzası tanımlama. Harici takvim, biletleme ve ödeme entegrasyonları ekleme.
