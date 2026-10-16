---
name: sector-theme-integration
description: Firmanın çalıştığı iş alanlarını anlatan sektör bölümlerini Sector modülüne eşle.
---

# Sector tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Sector rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku.

- Güncel `app/Models/Sector.php`, migration, `features.json` ve servis kodunu incele. Sektör içeriğini hizmet, marka ve müşteri/proje referanslarından bölümün amacı ve görsel yapısıyla ayır; eşleşme belirsizse kullanıcıya sor.
- Mevcut alanları yeniden kullan: `title`, `short_description`, `content`, `image`, `is_active`, `extra_fields`. Sektör için sıralama, detay sayfası veya başka modülle ilişki varmış gibi davranma.
- Veriyi mevcut Sector model ve servis örüntüsüyle al. Yönetilebilir yeni içerik gerekiyorsa [ortak alan genişletme kurallarına](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) uy.
