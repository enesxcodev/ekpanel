# SSS tema entegrasyonu

- Sıkça sorulan sorular, merak edilenler, yardım veya bilgi merkezi gibi tekrar eden soru-cevap bölümleri SSS adayıdır. Akordeon/katlanır soru listesi, soru başlıkları ve yanıt metinleri görsel/yapısal ipuçlarıdır; tek başına başlık kelimelerine göre eşleme yapma.
- Soru kaydındaki `name` ziyaretçiye gösterilen soruyu, `content` yanıtı taşır. `category_id` isteğe bağlıdır; temadaki soru listesi kategorisiz kayıtları da korumalıdır. Kategori kullanımı, tasarımda belirgin konu grupları varsa anlamlıdır; kategorisiz kayıtları gizleme veya yapay kategoriye atama.
- `FaqCategory` yalnız SSS gruplandırması içindir. Hizmetler ya da başka modüllerle ilişki tanımlanmadı; bir tasarım alanı hizmet SSS'si gibi görünse bile modüller arası ilişki/filtre uydurma. Tüm SSS'leri gösterme, belirli kategori seçme veya filtreleme gereksinimi tasarımdan açık değilse kullanıcıya sor.
- `features.json` içindeki `faq` ve `category` formları ek alanları ayırır. Alanların ortak şeması ve kayıt akışı için [.ai/rules/moduller.md](../../../.ai/rules/moduller.md) yönergelerini izle; SSS alanlarını tekrar eden bir ayar veya tema helper'ına taşıma.
- İçerik erişiminde mevcut `Faq` ve `FaqCategory` modellerini ve ilişkilerini kullan. Bu modül yönetim ekranı sağlar; ziyaretçi tarafı gösterim/route mevcut değilse varmış gibi davranma. İstenen tema gösterimi için sıralama veya görünürlük alanı kodda yoktur; tasarım bunları gerektiriyorsa kullanıcıya sor.

Uygulama adımları için [SSS tema skill'ini](../../skills/faqs-theme-integration/SKILL.md), ortak alan ve modül sınırları için [modül kurallarını](../../../.ai/rules/moduller.md) izle.

- Tema okumasında `FaqsFrontendService` kullan. Güncel metotlar, parametreler, dönüş türleri ve yayın/sıralama davranışı [modülün tema skill'inde](../../skills/faqs-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce bu sözleşmeyi oku.
