# Popup tema entegrasyonu

Önce [Popup skill'ini](../../skills/popup-theme-integration/SKILL.md), [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [modül kurallarını](../../../../.ai/rules/moduller.md) oku.

- Duyuru, kampanya bildirimi, önemli bilgilendirme gibi bağımsız, zaman aralığı olan kayıtlar bu modüle adaydır. Başlık, içerik ve isteğe bağlı görsel anlamı birlikte değerlendirilir; salt “duyuru” kelimesi yeterli değildir. Hero/banner Slider, tekil sabit site metni Settings olabilir. Alan eşleşmesi belirsizse kullanıcıya sor.
- `title` başlık; `content` ortak `HtmlSanitizer` ile temizlenmiş HTML içerik; `image_media_id` medya kütüphanesindeki isteğe bağlı görseldir. Görsel URL'si modelin `image_url` özelliğidir. Başlık ve diğer normal alanlar varsayılan Blade escaping ile sunulur. `extra_fields`, `popup` formunun ortak dinamik alan değerleridir. Alan genişletme için yukarıdaki ortak modül kurallarını izle.
- Veri erişimi `Modules\Popup\Services\PopupFrontendService` üzerinden controller/servis sınırında yapılır. Metotlar, ziyaretçi durumu sözleşmesi, örnek kullanım ve güvenli boş sonuç davranışı [skill'de](../../skills/popup-theme-integration/SKILL.md) açıklanır. Blade içinde sorgu veya gösterim uygunluğu hesabı yapma.
- Bu sürüm ziyaretçi arayüzü, modal, sayfa içi duyuru, otomatik tetikleme veya tarayıcı JavaScript'i sağlamaz. Modal/sayfa içi seçimi ertelenmiştir. Servisin uygun kayıt listesi aynı anda kaç kayıt gösterileceğine karar vermez.
- PHP tarayıcıdaki kapatma olayını kendiliğinden algılamaz. Kapatma/son gösterim olaylarını alma ve saklama gelecekteki entegrasyonun sorumluluğudur. Bu modül ziyaretçi takip tablosu, cookie/session/localStorage yazımı, cihazlar arası takip veya analitik içermez.
