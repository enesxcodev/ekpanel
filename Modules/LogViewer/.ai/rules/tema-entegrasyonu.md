---
name: logviewer-theme-integration
description: LogViewer kapsamı admin uygulama loglarının salt okunur yönetim ekranıdır; müşteri temasıyla eşleştirme yapılmaz.
---

# LogViewer tema entegrasyonu

[Ortak tema entegrasyon skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [modül kurallarını](../../../../.ai/rules/moduller.md) izleyin.

- LogViewer müşteri temasına veri sunmaz; `LogFileBrowserService` yalnız yetkilendirilmiş admin isteklerinde `storage/logs` içindeki `.log` dosyalarını listeler/okur.
- Log içeriğini public Blade, SEO metadata, modül `features.json` alanları veya frontend servisleri için kullanmayın. Log içeriğinin hassas olabileceği varsayımı korunur.
- Tasarımdaki bir bölüm log/teknik tanı içeriği istiyorsa yalnız kelime eşleşmesiyle LogViewer'a bağlamayın; içerik ihtiyacı ve güvenlik sınırı açıklığa kavuşmadan tema entegrasyonu yapmayın.
- Modülün public frontend service'i veya ziyaretçi verisi sözleşmesi bulunmaz.
