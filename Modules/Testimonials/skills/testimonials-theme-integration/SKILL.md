---
name: testimonials-theme-integration
description: Müşteri yorumları ve başarı hikâyelerini deneyimi aktaran doğru tema bölümüne eşler; marka logoları veya genel referans listeleriyle karıştırmaz.
---

# Müşteri yorumları tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [müşteri yorumları temasına özgü kuralı](../../.ai/rules/tema-entegrasyonu.md) oku. Ek alan altyapısı için [modül kurallarına](../../../../.ai/rules/moduller.md) başvur.

- Tema bölümünü içerik amacı ve yapısıyla belirle. “Müşterilerimiz ne diyor?”, “bizim için ne dediler”, “başarı hikâyeleri”, imzalı alıntılar ve müşteri deneyimi kartları ipucudur; salt anahtar kelimeyle karar verme. Logo duvarı ve şirket listesi tek başına yorum bölümü sayılmaz.
- Testimonial alanlarını güncel model ve migration'dan doğrula: ad customer_name, firma company, unvan job_title, deneyim/sonuç metni content, isteğe bağlı fotoğraf image, yayın onayı is_active, ek alanlar extra_fields. testimonial formu features.json ve admin.features.fields ile bağlıdır.
- Ziyaretçi içeriğinde is_active true kayıtları göster. Pasif/onay bekleyen kayıtları yayınlama; yeni kayıtların varsayılan olarak onaysız olduğunu koru. Görsel bulunmayan kayıtlar da geçerlidir.
- Sıralama sort_order ve id ile yapılır; ziyaretçi sorgusunda Testimonial::published() kullan. Bu modülde şirket, hizmet, proje ilişkisi veya public route katmanı yoktur. Tasarım bunları gerektiriyorsa mevcut kapsamı ve eksik kararı bildir; rastgele ilişki, sıralama ya da kayıt seçimi uydurma.
- Tema erişiminde `TestimonialsFrontendService` kullan. Ortak alan tanımlarını kopyalama.

## Frontend okuma servisi

- Sınıf: `Modules\Testimonials\Services\TestimonialsFrontendService`; dosya: `app/Services/TestimonialsFrontendService.php`.
- `getAll()` bir `Collection<int, Testimonial>`, `getFirst()` bir `Testimonial` veya `null`, `find(int $id)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Yalnız aktif kayıtları döner. Sıra mevcut `sort_order`, `id` artan şeklindedir. Modül kayıtlı/etkin değilse veya gerekli modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `imageMedia`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
