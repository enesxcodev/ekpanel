# Services tema entegrasyonu

- Firmanın sunduğu işleri anlatan “Hizmetlerimiz”, “Neler yapıyoruz” veya “Çözümlerimiz” bölümleri; başlık, açıklama, resim/ikon içeren kart ve listeler Services adayıdır. Bölümün anlamını ve mevcut modülleri birlikte değerlendir; eşleşme belirsizse kullanıcıya sor.
- Tekil hizmet `Service`, hizmet grubu `ServiceCategory` ile temsil edilir. Hizmette nullable `slug`, `sort_order` ve `title`, `short_description`, `content`, `meta_title`, `meta_keywords`, `meta_description`, `image`, `icon`, `is_active`, `extra_fields` vardır. `category()` ilişkisi opsiyoneldir; kategori `services()` ile birden fazla hizmet içerir. Resim ve ikon birbirinden bağımsız boş olabilir.
- Kategori nullable `sort_order` ve varsayılanı true olan `is_active` taşır. Kategori adı `name`, kısa açıklaması `extra_fields.short_description`, rozeti `extra_fields.badge_text` alanındadır. Kategori ile tekil hizmeti ayır; tasarımdaki detay bağlantısı, kayıt sırası veya resim/ikon tercihi belirsizse sor.
- Güncel alanları koddan doğrula. Alan genişletmede [ortak modül kurallarını](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme), uygulamada [Services skill’ini](../../skills/services-theme-integration/SKILL.md) izle.

- Hizmet ekleme, düzenleme ve silme ayrı izinlerle yönetilir. `content` HugeRTE ile düzenlenir ve ortak HTML temizleyicisinden geçirilir. Slug eklenmiş olması public detay route oluşturmaz.

- Tema okumasında `ServicesFrontendService` kullan. Güncel metotlar, parametreler, dönüş türleri ve yayın/sıralama davranışı [modülün tema skill'inde](../../skills/services-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce bu sözleşmeyi oku.
