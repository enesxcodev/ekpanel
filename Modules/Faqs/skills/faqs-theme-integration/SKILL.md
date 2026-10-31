---
name: faqs-theme-integration
description: SSS kategori ve soru-cevap kayıtlarını müşteri temasındaki anlamlı SSS bölümlerine eşler; tüm akordeon veya yardım bölümlerini otomatik olarak SSS kabul etmez.
---

# SSS tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [SSS tema kuralını](../../.ai/rules/tema-entegrasyonu.md) oku. Alan genişletme ve ortak form altyapısı için [modül kurallarına](../../../../.ai/rules/moduller.md) başvur.

- `Faq` ve `FaqCategory` modellerini, migration'ları ve `features.json` dosyasını modül kökünden okuyarak rule'daki eşlemeyi güncel kodla doğrula. `Faq.category_id` diller arasında ortak kategori kimliğidir. Soru `name`, `content`, `extra_fields` değerleri `FaqTranslation`; kategori `name` ve `extra_fields` değerleri `FaqCategoryTranslation` içindedir. SSS kaydında slug alanı bulunmaz ve eklenmemelidir.
- `category` ve `faq` ayrı features form anahtarlarıdır. Yeni tema alanı gerekirse mevcut ilgili formu kullan; ortak alan şemasını kopyalama. Hizmet ilişkisi, sıralama, aktiflik veya yayın durumu mevcut değildir; tasarım bunlardan birini gerektiriyorsa alanın gerekip gerekmediğini kullanıcıyla netleştir.
- SSS tasarımını anlam ve yapıdan eşleştir: soru-cevap çiftleri, accordion satırları ve konu başlıkları destekleyici ipuçlarıdır. Benzer görünen destek, hizmet açıklaması veya genel bilgi içeriğinin sahipliğini salt anahtar kelimeyle belirleme.
- Kategori ilişkisinin nullable olduğunu koru. Kategorisiz soruları tema çıktısından kendiliğinden çıkarma; kategori yokluğunda yapay grup üretme. Kategori tabanlı filtre tasarımdan anlaşılmıyorsa tüm kayıtları ya da bir kategoriyi seçme, kullanıcıya sor.
- Tema okumasında `FaqsFrontendService` kullan; bu servis ziyaretçi route'u veya renderer oluşturmaz.

## Frontend okuma servisi

- Sınıf: `Modules\Faqs\Services\FaqsFrontendService`; dosya: `app/Services/FaqsFrontendService.php`.
- `getAll(?int $categoryId = null, ?string $languageCode = null)` bir `Collection<int, Faq>`, `getFirst(?int $categoryId = null, ?string $languageCode = null)` bir `Faq` veya `null`, `find(int $id, ?string $languageCode = null)` bir `Faq` veya `null` döndürür. Yeni dil argümanı sona eklenmiştir; eski çağrı imzaları uyumludur. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Dil kodu verilmezse servis `faqs_language`, `pages_language`, route `lang` bağlamını ve son çare `tr` kodunu sırayla kullanır. Yalnız etkin dil ve o dilde çevirisi bulunan sorular döner. İstenen dil etkin değilse ya da soru çevirisi yoksa sonuç dönmez; başka dile sessiz fallback yapılmaz. Servis seçili metni `selectedTranslation` ilişkisinde sağlar.
- Kategori çevirisi ayrıca yüklenir; eksik kategori çevirisi başka dil metnini göstermez. `category_id` nullable ortak kimliktir. Kategori filtresi yalnız açıkça verilen ID'yi sorular üzerinde uygular; kategorisiz sorular filtresiz listede kalır ve kategori çevirisinin olmaması soru listesini daraltmaz.
- Modelde aktiflik/yayın alanı yoktur; böyle bir filtre uydurulmaz, tüm çevirili kayıtlar okunabilir. Tanımlı bir iş sırası yoktur; ilk/son/en yeni gibi bir öncelik garantisi verilmez. Modül kayıtlı/etkin değilse veya gerekli FAQ, kategori, çeviri ya da dil tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Kategori parametresi verilmezse seçili dil çevirisi olan bütün sorular (kategorisiz olanlar dahil) döner; verilirse yalnız ortak `category_id` değeri eşleşen kayıtlar döner. Bulunmayan kategori boş sonuç verir.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
