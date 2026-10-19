# Belgeler tema entegrasyonu

- Belgeler, sertifikalar, indirmeler, ürün kataloğu, broşürler veya doküman merkezi bölümleri Documents adayıdır. Dosya türü simgesi, belge adı, kategori grupları ve indirme bağlantıları yapısal ipuçlarıdır; salt başlığa göre eşleme yapma. Ürün kaydı ile ürün kataloğu dosyasını aynı içerik türü kabul etme.
- Document alanları: name belge adı, content isteğe bağlı zengin metin, slug benzersiz kısa bağlantı, file isteğe bağlı dosya yolu, status yayın durumu ve category_id isteğe bağlı gruptur. DocumentCategory alanları name ve isteğe bağlı icon ikon adıdır. İkon adı HTML değildir.
- İndirmeler, Belgeler, Sertifikalar ve Ürün Kataloğu başlangıç kategorileridir; kategoriler yönetim panelinde değiştirilebilir. Kategori adını veya ID'sini sabit varsayma. Belirli kategori seçimi tasarımdan anlaşılmıyorsa kullanıcıya sor.
- Ziyaretçi listelerinde yalnız status değeri published olan belgeleri göster; draft kayıtları yayınlama. Kategorisiz kayıtlar geçerlidir; kategori silindiğinde belgeler korunur ve category_id null olur.
- File bulunmadığında indirme bağlantısı uydurma; content bulunmadığında boş açıklama bölümü üretme. İçerik ortak HtmlSanitizer ile kaydedilir. Dosya türleri PNG, JPG, JPEG, WebP, PDF, DOC ve PPTX; yükleme sınırı 15 MB'dır.
- Modül yönetim ekranları sağlar; ziyaretçi detay/indirme route'u mevcut değildir. Slug alanı tek başına bir ziyaretçi route'u oluşturmaz. Mevcut model ve ilişkilerle eriş; modül etkinliğini ve yayın durumunu koru. Kayıt sıralaması veya özel dosya erişim kuralı tanımlanmamışsa bunları varsayma.
- Features alanları document ve category form anahtarlarıyla ayrılır. Ortak alan altyapısını kopyalamadan [modül kurallarını](../../../../.ai/rules/moduller.md) izle.

Uygulama adımları için [Belgeler tema skill'ini](../../skills/documents-theme-integration/SKILL.md) ve [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) oku.

- Tema okumasında `DocumentsFrontendService` kullan. Güncel metotlar, parametreler, dönüş türleri ve yayın/sıralama davranışı [modülün tema skill'inde](../../skills/documents-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce bu sözleşmeyi oku.
