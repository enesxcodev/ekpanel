---
name: pages-theme-integration
description: EkAgent Pages kayıtlarını müşteri temasındaki uzun biçimli bilgi sayfalarına ve kategori URL hiyerarşisine eşle.
---

# Pages tema entegrasyonu

[Ortak tema entegrasyon skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Pages tema rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Eşleme sınırları bu rule'da, ortak alan genişletme ise [modül kurallarında](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) tutulur.

- Modülün `app/Models/`, `database/migrations/`, `features.json`, controller ve servis akışını okuyup mevcut alanları doğrula. Uzun biçimli sayfa içeriğini `Page`, ağaç kategori metaverisini `PageCategory` temsil eder.
- `Page::category` ilişkisini ve `Page::url_path` erişimini kullan. Public detay route'u iç içe kategori slug'larını çözer ve yalnız yayındaki kayıtları sunar; draft sayfaları filtre dışı bırakma.
- `features.json` içindeki `page` ve `category` form tanımları mevcut ve boş olabilir. Kullanıcı ihtiyacı varsa alan eklemesini ortak `ModuleFeatures`, `FeatureValues`, `admin.features.fields` ve `FieldFiles` akışı üzerinden yap; paralel alan sistemi kurma.
- Hakkımızda/Misyon/Vizyon adlarını örnek anlamdaşlar olarak kullan; otomatik kayıt veya sabit tip varsayma. Hizmet, ekip, slider veya sabit site ayarı ile eşleşme belirsizse mevcut tema bağlamına göre karar ver, gerçekten çözülemiyorsa kullanıcıya sor.
- Sayfa içeriği HTML'sini public görünümde doğrudan basmadan önce `PageService::sanitizeContent` üzerinden geçir. Mevcut veri erişimini ve hizmet akışını kullan; tema için ek facade/helper oluşturma.
