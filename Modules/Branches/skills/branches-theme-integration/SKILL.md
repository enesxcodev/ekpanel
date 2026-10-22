---
name: branches-theme-integration
description: EkAgent Branches modülünü çok konumlu işletmelerin şube, bayi veya ofis kartı/listelerine eşlerken kullan; tekil iletişim adreslerini otomatik olarak konum kayıtlarına dönüştürme.
---

# Branches tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Branches rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Bölüm anlamı ve içerik sınırları rule'da; ortak alan genişletme [modül kurallarındadır](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme).

- Gerçek veri `Modules\Branches\Models\Branch` ve `Modules\Branches\Models\BranchCategory` modellerindedir. `BranchesFrontendService` kullan; doğrudan sorgu, etkinlik ve tablo kontrollerini temada tekrarlama.
- `getAll(?string $category = null, int $limit = 50)` aktif Branch kayıtlarını sıralı Eloquent Collection olarak döndürür; kategori slug'ı isteğe bağlıdır ve limit 1–100 aralığında tutulur. `getFirst(?string $category = null)` kayıt veya `null`, `find(int $id)` görünür kayıt veya `null` verir. Sıralama `sort_order`, sonra ID'dir.
- Servis Branches etkin değilse/tabloları yoksa veya Konum hazır değilse boş koleksiyon ya da `null` döndürür. `category`, `city`, `town`, `district`, `neighbourhood`, `street` ilişkileri önceden yüklenir. Pasif Branch ve pasif kategoriler yayınlanmaz.
- Kartta ad ve kategori ana bilgidir. Adres satırında yalnız çözümlenebilen coğrafi ilişki adlarını ve varsa `address_detail` değerini kullan. İletişim düğmesi yalnız ilgili telefon, e-posta veya HTTPS web/harita bağlantısı doluysa gösterilir; çalışma saatleri düz metindir. Boş alan için placeholder içerik üretme.
- Kullanıcı kaynak kaydı geçersizleştiğinde özel public gizleme/uyarı akışı istemedi. Eksik ilişkiyi gösterme; filtre veya fallback davranışı ekleme. Kullanıcı kaynak coğrafi kimliğini elle düzenlemez.
- Harita sağlayıcı API'si, iframe, geocoding, yakındaki konum sıralaması veya detay sayfası bu modül tarafından sunulmaz. Temanın işaret ettiği görsel/tasarım belirsizse ortak skill'in kullanıcıya sorma kuralını uygula.
