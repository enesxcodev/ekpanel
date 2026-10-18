# Projeler tema entegrasyonu

- “Projelerimiz”, “tamamlanan işler”, “devam eden işler”, “uygulamalarımız”, kapaklı proje kartları ve proje detayları eşleme ipuçlarıdır. Bölümün anlamı ve yapısını diğer modüllerle birlikte değerlendir; salt başlığa göre karar verme. Modül/alan eşleşmesi belirsizse kullanıcıya sor.
- ProjectCategory name, slug ve isteğe bağlı icon alanlarını taşır. İş aşaması kategorilerle yönetilir. Project title, slug, project_category_id, project_date, content, is_active, sort_order alanlarını; images çoklu medya ilişkisini ve coverMedia/cover_url kapağını sunar.
- Ziyaretçi gösteriminde Project::published() sorgusunu kullan; yayın dışındaki kayıtları gösterme. Sıra sort_order ve id ile belirlenir. project_date tek proje tarihidir. Görseller pivot sort_order sırasındadır; kapak seçilen görsellerden biridir. Ayrı sabit iş aşaması alanı yoktur.
- Model/servis erişimini kullan; yalnız tema için ek facade/helper oluşturma. Kurulu ve etkin olma kontrolünü koru.
- Ortak alan genişletme için [modül kurallarını](../../../../.ai/rules/moduller.md) izle; features.json ve extra_fields altyapısını kopyalama.
- [Modüle özgü skill](../../skills/projects-theme-integration/SKILL.md), [ortak tema skill’i](../../../../.claude/skills/theme-integration/SKILL.md) ve [geliştirme workflow’u](../workflows/modul-gelistirme.md) birlikte kullanılır.