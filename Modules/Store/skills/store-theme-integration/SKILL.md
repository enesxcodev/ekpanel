---
name: store-theme-integration
description: EkAgent Store modülünün satılabilir ürün, sepet ve checkout bölümlerini mevcut temaya bağla.
---

# Store tema entegrasyonu

Önce ortak [tema entegrasyonu skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Store kuralını](../../.ai/rules/tema-entegrasyonu.md) oku. “Mağaza”, “sepet”, “satın al” veya fiyat/stok/varyant içeren bölümleri yalnız anlam ve görsel yapıyla birlikte değerlendir; Products ile eşleşme belirsizse kullanıcıya sor.

Store ürünleri kendi kayıtlarıdır; Products'a zorunlu bağımlılık veya çift yönlü senkronizasyon ekleme. Store sınıfı kurulu/etkin değilken tema sayfası çalışmaya devam etmelidir. Blade içinde sorgu, stok denetimi, vergi hesabı veya sepet iş kuralı çalıştırma; controller veya Store frontend hizmetinden hazırlanan veriyi kullan.

Fiyat tutarları kuruş cinsinden tamsayı ve para birimi koduyla saklanır. Verginin dahil gösterimi ile misafir checkout Store ayarlarından gelir; tema bu kararları yeniden uygulamaz. Gerçek ödeme ve taşıyıcı entegrasyonları ayrıca açık onay gerektirir.
