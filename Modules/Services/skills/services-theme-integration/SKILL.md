---
name: services-theme-integration
description: Firmanın hizmet kartlarını, listelerini ve hizmet kategorilerini Services modülüne eşle.
---

# Services tema entegrasyonu

[Ortak tema skill’ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Services rule’unu](../../.ai/rules/tema-entegrasyonu.md) oku.

- Modülün `app/Models/`, `database/migrations/`, `features.json` ve form/servis kodunu inceleyerek güncel alanları doğrula. Hizmet kartını `Service`, kategori grubunu `ServiceCategory` ile eşle; belirsizse sor.
- Mevcut model ilişkilerini ve `ServiceService` / `ServiceCategoryService` veri erişimini kullan. `features.json` içindeki `service` ve `category` form anahtarlarını koru; alan genişletmede [ortak kurallara](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) başvur.
