---
name: documents-theme-integration
description: Belgeler, sertifikalar, indirmeler ve ürün kataloğu dosyalarını içerik amacı ve kategori yapısına göre müşteri temasına eşler.
---

# Belgeler tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md), [Belgeler tema kuralını](../../.ai/rules/tema-entegrasyonu.md) ve [modül kurallarını](../../../../.ai/rules/moduller.md) oku.

- Document ve DocumentCategory modellerini, migration'ları ve features.json dosyasını okuyarak alanları güncel kodla doğrula. Belge adı name, açıklama content, dosya file, kısa bağlantı slug, yayın durumu status, isteğe bağlı kategori category_id/category; kategori adı name ve ikon adı icon alanındadır.
- Belge kartları, sertifika galerileri, dosya türü simgeleri ve indirme bağlantıları içerik eşleme ipuçlarıdır. Katalog dosyasını ürün kaydıyla veya sertifika dosyasını başka modüllerin görselleriyle yalnız görünüşüne göre eşleme.
- Ziyaretçi listelerinde status published filtresini kullan. Kategorisiz ve dosyasız kayıtları geçerli kabul et; dosyası olmayan kayıtta indirme bağlantısı üretme.
- Başlangıç kategorileri düzenlenebilir; sabit ID veya kategori adına göre iş kuralı kurma. Kategori seçimi, sıralama veya içerik sahipliği belirsizse kullanıcıya sor.
- DocumentService kaydı ortak HtmlSanitizer ve FieldFiles ile yapar. Ek alanlar document/category anahtarlarıyla ModuleFeatures, FeatureValues ve admin.features.fields üzerinden bağlıdır; paralel altyapı kurma.
- Mevcut model ilişkilerini kullan. Ziyaretçi route'u, özel dosya erişim katmanı veya tema sorgu servisi varmış gibi davranma; sadece tema için facade/helper ekleme.
