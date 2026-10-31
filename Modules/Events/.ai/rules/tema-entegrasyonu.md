# Events tema entegrasyonu

Etkinlik takvimi, seminer, buluşma veya kayıt bölümlerini `Event` kayıtlarına bağla. Blog duyuru içeriğidir; etkinlik zaman ve kontenjan kaydı değildir. Bölümün anlamını ve görsel ipuçlarını mevcut tema kapsamıyla birlikte değerlendir; salt anahtar kelime eşleşmesine göre modül seçme. Alan eşlemesi belirsizse kullanıcıya sor.

- Başlık, mekân ve açıklama `EventTranslation` üzerinden; başlangıç/bitiş, kapasite ve aktiflik tekil `Event` kaydından okunur. Seçili etkinlik dilinde çeviri yoksa başka dil metnini gösterme; o etkinliği içerik listesinden çıkar.
- Tema yalnız aktif etkinlikleri, başlık, mekân, başlangıç/bitiş saati ve gerekiyorsa kapasite bilgisini gösterir.
- Kayıt formu `EventRegistration` kaydı oluşturur; katılımcı listesi ve yönetim durumları ziyaretçiye açık değildir.
- Her kayıt aynı `event_id` ile o etkinliğe bağlı kalır. Kapasite doluysa yeni kayıt düğmesi gizlenir veya anlaşılır bir açıklama gösterilir; sunucu kapasiteyi doğrular.
- Tekrarlayan etkinlik veya harici takvim entegrasyonu ekleme.
- Modülde public route veya frontend servisi bulunmadığından kural yeni endpoint ya da var olmayan servis imzası tanımlamaz. Gerçek bir tüketici doğarsa frontend okuma servisi için [modül kurallarına](../../../../.ai/rules/moduller.md) başvur.

Ortak alan genişletmesi için [modül kurallarına](../../../../.ai/rules/moduller.md) başvur.
