# SSS tema entegrasyonu

- Sıkça sorulan sorular, merak edilenler, yardım veya bilgi merkezi gibi tekrar eden soru-cevap bölümleri SSS adayıdır. Akordeon/katlanır soru listesi, soru başlıkları ve yanıt metinleri görsel/yapısal ipuçlarıdır; tek başına başlık kelimelerine göre eşleme yapma.
- Soru kaydının ortak `category_id` alanı diller arasında aynıdır. Soru `name`, `content` ve `extra_fields` değerleri `FaqTranslation` içinde; kategori `name` ve `extra_fields` değerleri `FaqCategoryTranslation` içinde dile bağlıdır. SSS'de slug alanı yoktur ve eklenmemelidir.
- `FaqCategory` yalnız SSS gruplandırması içindir. Hizmetler ya da başka modüllerle ilişki tanımlanmadı; bir tasarım alanı hizmet SSS'si gibi görünse bile modüller arası ilişki/filtre uydurma. Tüm SSS'leri gösterme, belirli kategori seçme veya filtreleme gereksinimi tasarımdan açık değilse kullanıcıya sor.
- `features.json` içindeki `faq` ve `category` formları çevrilen kaydın ek alanlarını tanımlar. Alanların ortak şeması ve kayıt akışı için [.ai/rules/moduller.md](../../../.ai/rules/moduller.md) yönergelerini izle; SSS alanlarını tekrar eden bir ayar veya tema helper'ına taşıma.
- İçerik erişiminde mevcut `Faq` / `FaqCategory` modellerini ve ilişkilerini kullan. Bu modül yönetim ekranı sağlar; ziyaretçi tarafı SSS gösterimi/route'u veya renderer eklemez. İstenen tema gösterimi için sıralama ya da görünürlük alanı kodda yoktur; tasarım bunları gerektiriyorsa kullanıcıya sor.

Uygulama adımları için [SSS tema skill'ini](../../skills/faqs-theme-integration/SKILL.md), ortak alan ve modül sınırları için [modül kurallarını](../../../.ai/rules/moduller.md) izle.

- Tema okumasında `FaqsFrontendService` kullan. Güncel servis sözleşmesi, dil seçimi, fallback ve eksik çeviri davranışı [modülün tema skill'inde](../../skills/faqs-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce oku.
