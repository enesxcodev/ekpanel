---
name: konum-theme-integration
description: Türkiye adres seçimleri ve il/ilçe/mahalle/cadde filtrelerini Konum modülünün doğrulanmış referans verisine bağlarken kullan.
---

# Konum tema entegrasyonu

Önce [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md), [Konum kuralını](../../.ai/rules/tema-entegrasyonu.md) ve [ortak modül kurallarını](../../../../.ai/rules/moduller.md) oku.

1. Bölümün referans konum seçimi mi yoksa iletişim/mağaza içeriği mi olduğunu belirle; belirsizse kullanıcıya sor.
2. Modülün kurulu/etkin ve importun tamamlanmış olduğunu servis üzerinden denetle. Sınıf hiç yokken de isteğe bağlı entegrasyon çalışabilmelidir.
3. `app/Services/KonumFrontendService.php` içindeki `search()` imzasını ve izin verilen seviyeleri doğrula. Koleksiyon boşsa boş seçim durumu göster. Sıralama ad, ardından ID; varsayılan sınır 25 ve üst sınır 100'dür. Tüm cadde/sokakları isteme.
4. Seçim zincirinde il, ilçe, kaynak ara bölgesi, mahalle ve cadde/sokak ilişkilerini koru. İlçe `towns`, mahalle `neighbourhoods` tablosudur. Çocuk JSON özetlerini ilişkisel kayıtların yerine kullanma.
5. Polygon gerekliyse `PolygonGeometry::decode()` ve kaynak SRID'sini doğrula. Kaynak baytlarını koru; hex veriyi şifreli metin sayma.
6. Alan genişletme kurallarını bu dosyaya kopyalama; ortak kuralları izle. Mevcut kaynak verisinin güncelliği veya kapsamı için garanti üretme.
