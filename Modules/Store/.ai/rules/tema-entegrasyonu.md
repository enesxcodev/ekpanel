# Store tema entegrasyonu

- “Mağaza”, “satın al”, “sepet”, “checkout” veya fiyat, stok ve varyant içeren ürün kartları Store adayıdır. Salt anahtar kelimeyle eşleştirme yapma; Products ve Store ayrımı belirsizse kullanıcıya sor.
- Store, kendi `StoreProduct` kayıtlarının sahibidir; Products modülüne zorunlu bağımlılık veya örtük senkronizasyon kurmaz.
- Liste ve detay verileri Blade içinde sorgulanmaz. Aktiflik, stok ve para birimi kuralları Store frontend servisinde hazırlanır.
- Store kapalıyken tema bölümü boş davranmalı; çekirdek controller Store sınıflarına zorunlu bağımlılık eklememelidir.
- Ayarların alan şeması için ortak [modül kurallarına](../../../../.ai/rules/moduller.md#modüle-özgü-tema-entegrasyon-yönergeleri) başvur.
