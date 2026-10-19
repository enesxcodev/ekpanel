---
name: faqs-theme-integration
description: SSS kategori ve soru-cevap kayıtlarını müşteri temasındaki anlamlı SSS bölümlerine eşler; tüm akordeon veya yardım bölümlerini otomatik olarak SSS kabul etmez.
---

# SSS tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [SSS tema kuralını](../../.ai/rules/tema-entegrasyonu.md) oku. Alan genişletme ve ortak form altyapısı için [modül kurallarına](../../../../.ai/rules/moduller.md) başvur.

- `Faq` ve `FaqCategory` modellerini, migration'ları ve `features.json` dosyasını modül kökünden okuyarak rule'daki eşlemeyi güncel kodla doğrula. Soru metni `Faq.name`, yanıt `Faq.content`, isteğe bağlı grup `Faq.category_id`/`Faq.category`, kategori etiketi `FaqCategory.name` alanındadır.
- `category` ve `faq` ayrı features form anahtarlarıdır. Yeni tema alanı gerekirse mevcut ilgili formu kullan; ortak alan şemasını kopyalama. Hizmet ilişkisi, sıralama, aktiflik veya yayın durumu mevcut değildir; tasarım bunlardan birini gerektiriyorsa alanın gerekip gerekmediğini kullanıcıyla netleştir.
- SSS tasarımını anlam ve yapıdan eşleştir: soru-cevap çiftleri, accordion satırları ve konu başlıkları destekleyici ipuçlarıdır. Benzer görünen destek, hizmet açıklaması veya genel bilgi içeriğinin sahipliğini salt anahtar kelimeyle belirleme.
- Kategori ilişkisinin nullable olduğunu koru. Kategorisiz soruları tema çıktısından kendiliğinden çıkarma; kategori yokluğunda yapay grup üretme. Kategori tabanlı filtre tasarımdan anlaşılmıyorsa tüm kayıtları ya da bir kategoriyi seçme, kullanıcıya sor.
- Tema okumasında `FaqsFrontendService` kullan; bu servis ziyaretçi route'u veya bir renderer oluşturmaz.

## Frontend okuma servisi

- Sınıf: `Modules\Faqs\Services\FaqsFrontendService`; dosya: `app/Services/FaqsFrontendService.php`.
- `getAll(?int $categoryId = null)` bir `Collection<int, Faq>`, `getFirst(?int $categoryId = null)` bir `Faq` veya `null`, `find(int $id)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Modelde aktiflik/yayın alanı yoktur; böyle bir filtre uydurulmaz, tüm kayıtlar okunabilir. Tanımlı bir iş sırası yoktur; ilk/son/en yeni gibi bir öncelik garantisi verilmez. Modül kayıtlı/etkin değilse veya gerekli modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `category`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse kategorisiz olanlar dahil tüm görünür kayıtlar döner; parametre verilirse yalnız ilgili kategori filtrelenir. Bulunmayan kategori boş sonuç verir; sabit kategori ID'si veya adı varsayma.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
