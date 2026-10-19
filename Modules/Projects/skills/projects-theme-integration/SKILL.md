---
name: projects-theme-integration
description: Projeler içeriğini anlamı ve yapısıyla doğru tema bölümüne eşler.
---

# Projeler tema entegrasyonu

[Ortak tema skill’ini](../../../../.claude/skills/theme-integration/SKILL.md), [modül kuralını](../../.ai/rules/tema-entegrasyonu.md) ve [ortak modül kurallarını](../../../../.ai/rules/moduller.md) oku.

- Kategoriye bağlı iş örnekleri, proje kapakları ve tarih/içerik içeren detaylar Projeler adayıdır; müşteri yorumları veya sıradan görsel ızgaralarıyla karıştırma.
- ProjectCategory name, slug ve isteğe bağlı icon alanlarını taşır. İş aşaması kategorilerle yönetilir. Project title, slug, project_category_id, project_date, content, is_active, sort_order alanlarını; images çoklu medya ilişkisini ve coverMedia/cover_url kapağını sunar.
- Ziyaretçi gösteriminde Project::published() sorgusunu kullan; yayın dışındaki kayıtları gösterme. Sıra sort_order ve id ile belirlenir. project_date tek proje tarihidir. Görseller pivot sort_order sırasındadır; kapak seçilen görsellerden biridir. Ayrı sabit iş aşaması alanı yoktur.
- Eşleme belirsizse kullanıcıya sor. Mevcut model/servis erişimini ve ortak alan altyapısını kullan; yeni tema facade/helper zorunluluğu getirme.

## Frontend okuma servisi

- Sınıf: `Modules\Projects\Services\ProjectsFrontendService`; dosya: `app/Services/ProjectsFrontendService.php`.
- `getAll(?int $categoryId = null)` bir `Collection<int, Project>`, `getFirst(?int $categoryId = null)` bir `Project` veya `null`, `find(int $id)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Yalnız aktif kayıtları döner. Sıra mevcut `sort_order`, `id` artan şeklindedir. Modül kayıtlı/etkin değilse veya gerekli modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `category`, `coverMedia`, `images`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse kategorisiz olanlar dahil tüm görünür kayıtlar döner; parametre verilirse yalnız ilgili kategori filtrelenir. Bulunmayan kategori boş sonuç verir; sabit kategori ID'si veya adı varsayma.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
