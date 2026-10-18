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
- Mevcut modülde ziyaretçi route'u veya tema query servisi yoktur. Temaya veri gerekirken bu eksikliği belirt ve mevcut model/ilişki erişimini kullan; sırf entegrasyon için yeni facade/helper oluşturma.
