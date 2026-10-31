---
name: documents-theme-integration
description: Belgeler, sertifikalar, indirmeler ve ürün kataloğu dosyalarını içerik amacı ve kategori yapısına göre müşteri temasına eşler.
---

# Belgeler tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md), [Belgeler tema kuralını](../../.ai/rules/tema-entegrasyonu.md) ve [modül kurallarını](../../../../.ai/rules/moduller.md) oku.

- Document ve DocumentCategory modellerini, migration'ları ve features.json dosyasını okuyarak alanları güncel kodla doğrula. Belge `name`, `content`, `slug` ve `extra_fields` değerleri ile kategori `name` ve `extra_fields` değerleri translations tablolarında dil bazında tutulur. `file`/`file_media_id`, `status`, `category_id`, kayıt kimliği ve kategori `icon` ortaktır; kategori ilişkisi ortak ID üzerinden kurulur.
- Belge kartları, sertifika galerileri, dosya türü simgeleri ve indirme bağlantıları içerik eşleme ipuçlarıdır. Katalog dosyasını ürün kaydıyla veya sertifika dosyasını başka modüllerin görselleriyle yalnız görünüşüne göre eşleme.
- Ziyaretçi listelerinde status published filtresini kullan. Kategorisiz ve dosyasız kayıtları geçerli kabul et; dosyası olmayan kayıtta indirme bağlantısı üretme.
- Başlangıç kategorileri düzenlenebilir; sabit ID veya kategori adına göre iş kuralı kurma. Kategori seçimi, sıralama veya içerik sahipliği belirsizse kullanıcıya sor.
- DocumentService kaydı ortak HtmlSanitizer ve FieldFiles ile yapar. Ek alanlar document/category anahtarlarıyla ModuleFeatures, FeatureValues ve admin.features.fields üzerinden bağlıdır; paralel altyapı kurma.
- Tema okumalarında `DocumentsFrontendService` kullan ve aktif dil kodunu son parametre olarak geçir. Servis, yalnız aktif dilde çevirisi bulunan yayınları döndürür; eksik/inaktif çeviride başka dile fallback yapmaz. Gerçek tek ziyaretçi tüketicisi `HomePageService::resources()` kaynak kartıdır; kart başlığı `name`, açıklaması `content`, indirme URL'si ortak `file_url` alanıdır. Ortak dosya çeviriden bağımsız kalır.
- Slug için ziyaretçi route'u oluşturma. Modülde ziyaretçi belge listeleme, detay veya indirme route'u bulunmaz; servis de route/HTML üretmez.

## Frontend okuma servisi

- Sınıf: `Modules\Documents\Services\DocumentsFrontendService`; dosya: `app/Services/DocumentsFrontendService.php`.
- `getAll(?int $categoryId = null, ?string $languageCode = null)` bir `Collection<int, Document>`, `getFirst(?int $categoryId = null, ?string $languageCode = null)` bir `Document` veya `null`, `find(int $id, ?string $languageCode = null)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Yalnız `status=published` ve seçili aktif dilde çevirisi bulunan kayıtlar döner. Eksik/inaktif dilde başka dile sessiz fallback yapılmaz. Tanımlı bir iş sırası yoktur; ilk/son/en yeni gibi bir öncelik garantisi verilmez. Modül kayıtlı/etkin değilse veya gerekli dil/çeviri/modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: aynı dilde `selectedTranslation`, aynı dile ait çevirisi bulunan `category` ve ortak `fileMedia`. Çeviri alanları `selectedTranslation` üzerindedir; URL için ana belge `file_url` erişimcisini kullan. Servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse kategorisiz olanlar dahil tüm görünür kayıtlar döner; parametre verilirse yalnız ilgili kategori filtrelenir. Bulunmayan kategori boş sonuç verir; sabit kategori ID'si veya adı varsayma.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
