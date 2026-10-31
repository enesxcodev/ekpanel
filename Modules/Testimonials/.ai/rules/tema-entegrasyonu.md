# Müşteri yorumları tema entegrasyonu

- “Müşterilerimiz ne diyor?”, “bizim için ne dediler”, “başarı hikâyeleri”, “müşteri deneyimleri” veya “referanslarımızın görüşleri” gibi bölümler müşteri yorumları adayıdır. Uzun alıntı, müşteri adı, firma/unvan, portre görseli ve imza benzeri kapanış yapısal ipuçlarıdır; yalnız başlığa göre eşleme yapma.
- Tek yorum Testimonial kaydıdır; customer_name, company, job_title, content ve extra_fields seçili dilin TestimonialTranslation satırındadır. image/image_media_id, is_active, sort_order ve kayıt kimliği dillere ortak kalır. Fotoğraf, yayın onayı ve sıralama aynı yorumun tüm dillerinde geçerlidir. Müşteri yorumu bir marka logosu veya logo galerisi değildir.
- Ziyaretçi gösteriminde yalnız is_active değeri true olan yorumları kullan; yeni yorumlar yönetici açıkça yayın onayı verene kadar beklemede kalır. Görsel opsiyoneldir; olmayan görsel için uydurma logo/portre gösterme.
- Ziyaretçi yalnızca aktif dilde çevirisi bulunan aktif kayıtları alır; eksik çeviriyi başka dilden doldurma. Sıralama sort_order ve id artandır. Firmalar, hizmetler, projeler veya başka modüllerle ilişki tanımlanmamıştır. Tasarım bu ilişkilerden, belirli bir müşteri seçiminden veya özel sıralamadan yararlanıyorsa bunları kendiliğinden üretme.
- Modül yönetim CRUD'u ve frontend veri okuma servisi sağlar. Bu servis ziyaretçi route'u oluşturmaz; tasarımın URL ihtiyacını mevcut route sözleşmesinden doğrula.
- Alan genişletme için features.json içindeki testimonial formunu ve ortak [modül kurallarını](../../../../.ai/rules/moduller.md) izle; ortak şemayı kopyalama.

Uygulama adımları için [Müşteri yorumları tema skill'ini](../../skills/testimonials-theme-integration/SKILL.md), genel eşleme için [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) izle.

- Tema okumasında `TestimonialsFrontendService` kullan; gerçek ana sayfa tüketicisi `HomePageService::testimonials()` seçili dil kodunu geçirir. Güncel metotlar, parametreler, dönüş türleri ve yayın/sıralama davranışı [modülün tema skill'inde](../../skills/testimonials-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce bu sözleşmeyi oku.
