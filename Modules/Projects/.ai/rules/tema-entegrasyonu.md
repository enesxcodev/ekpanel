# Projeler tema entegrasyonu

- “Projelerimiz”, “tamamlanan işler”, “devam eden işler”, “uygulamalarımız”, kapaklı proje kartları ve proje detayları eşleme ipuçlarıdır. Bölümün anlamını ve yapısını diğer modüllerle birlikte değerlendir; salt başlığa göre karar verme. Modül/alan eşleşmesi belirsizse kullanıcıya sor.
- Proje `project_category_id`, `project_date`, `is_active`, `sort_order`, `cover_media_id` ve `images` galerisini ortak tutar. Kategori `icon` alanı da ortaktır. `ProjectTranslation` title, slug, content ve proje `extra_fields` alanlarını; `ProjectCategoryTranslation` name, slug ve kategori `extra_fields` alanlarını dile göre saklar.
- Frontend kart metni proje `selectedTranslation` alanlarından ve `category.selectedTranslation.name` değerinden okunmalıdır. Başka dile fallback yapma. Kategori çevirisi olmayan yayınlı proje gösterilmez; eksik dil içeriği veya gerekli çeviri tabloları boş sonuç verir.
- Ziyaretçi gösteriminde yalnız yayınlı projeleri kullan. Sıra `sort_order`, `id` artandır. `project_date` ortak tek proje tarihidir. Galeri görselleri pivot `sort_order` sırasındadır; kapak seçilen görsellerden biridir. Ayrı sabit iş aşaması alanı yoktur.
- Model/servis erişimini kullan; yalnız tema için ek facade/helper oluşturma. Kurulu ve etkin olma kontrolünü koru.
- Ortak alan genişletme için [modül kurallarını](../../../../.ai/rules/moduller.md) izle; `features.json` ve `extra_fields` altyapısını kopyalama.
- [Modüle özgü skill](../../skills/projects-theme-integration/SKILL.md), [ortak tema skill’i](../../../../.claude/skills/theme-integration/SKILL.md) ve [geliştirme workflow’u](../workflows/modul-gelistirme.md) birlikte kullanılır.

- Tema okumasında `ProjectsFrontendService` kullan. Güncel metotlar, parametreler, dönüş türleri ve yayın/sıralama davranışı [modülün tema skill'inde](../../skills/projects-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce bu sözleşmeyi oku.
