---
name: projects-theme-integration
description: Projeler içeriğini anlamı ve yapısıyla doğru tema bölümüne eşler.
---

# Projeler tema entegrasyonu

[Ortak tema skill’ini](../../../../.claude/skills/theme-integration/SKILL.md), [modül kuralını](../../.ai/rules/tema-entegrasyonu.md) ve [ortak modül kurallarını](../../../../.ai/rules/moduller.md) oku.

- Kategoriye bağlı iş örnekleri, proje kapakları ve tarih/içerik içeren detaylar Projeler adayıdır; müşteri yorumları veya sıradan görsel ızgaralarıyla karıştırma.
- ProjectCategory name, slug ve isteğe bağlı icon alanlarını taşır. İş aşaması kategorilerle yönetilir. Project title, slug, project_category_id, project_date, content, is_active, sort_order alanlarını; images çoklu medya ilişkisini ve coverMedia/cover_url kapağını sunar.
- Ziyaretçi gösteriminde Project::published() sorgusunu kullan; yayın dışındaki kayıtları gösterme. Sıra sort_order ve id ile belirlenir. project_date tek proje tarihidir. Görseller pivot sort_order sırasındadır; kapak seçilen görsellerden biridir. Ayrı sabit iş aşaması alanı yoktur.
- Eşleme belirsizse kullanıcıya sor. Mevcut model/servis erişimini ve ortak alan altyapısını kullan; yeni tema facade/helper zorunluluğu getirme.