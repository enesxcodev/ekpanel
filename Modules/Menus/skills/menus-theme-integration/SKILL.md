---
name: menus-theme-integration
description: EkAgent Menus verisini müşteri temasındaki ana, mobil veya alt alan gezinmesine bağla; yönetilen ziyaretçi menüsü ve tema konumlarını eşleştir.
---

# Menus tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Menus tema kurallarını](../../.ai/rules/tema-entegrasyonu.md) oku. Modülün mevcut model, NavigationService ve public renderer'ını incele.

- Tema konumunun menü anahtarını belirle. Mevcut ana konum `menus.main_menu` ayarındaki anahtarı tüketir; varsayılan `main`dir. Yeni konuma otomatik kayıt/örnek içerik ekleme.
- Hazırlanmış ağacı `NavigationService::tree($key)` üzerinden al; filtrelemeyi, route/URL çözümünü veya kaynak içerik sorgularını Blade içinde tekrarlama.
- İç içe `children` bütün seviyeleri taşır. Tema için aç/kapa kontrolleri eklenirse klavye ve mobil erişimi koru; grup etiketine sahte URL verme.
- Kaynak/alan eşlemesi belirsizse kullanıcıya sor. Pages kategorileri ve Services için bulunmayan public adres varsayma; anchor bölümünün varlığını kontrol etmeden başarı iddia etme.
- Ek alanlar ve features.json kullanımı için [ortak modül kurallarına](../../../../.ai/rules/moduller.md) başvur. Menus'un bugün ek form tanımı yoktur; sırf görünüm için paralel ayar veya helper katmanı ekleme.
