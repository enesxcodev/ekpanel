---
name: blog-theme-integration
description: Blog, haber ve duyuru içeriklerini müşteri temasına bağla.
---

# Blog tema entegrasyonu

Bu modülün [tema entegrasyonu kurallarını](../../.ai/rules/tema-entegrasyonu.md) izle.

- `BlogContent` ortak içerik kaynağıdır. İçerik grupları kategori ağacındaki Blog, Haberler ve Duyurular kökleri ile tanımlanır.
- Public görünümde yalnızca yayındaki kayıtları göster; taslak veya arşiv kaydını sunma.
- Ana kategori listeleri alt kategorileri de kapsamalıdır.
- HTML içerik `BlogService` sanitizasyonundan geçmelidir.
- Öne çıkan görsel `image_url` erişimcisi ve medya kullanım ilişkisi üzerinden alınır.
- SEO ve müşteri temasına özgü gösterim, ortak slug, içerik, kategori ve medya davranışlarını korumalıdır.
