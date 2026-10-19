---
name: team-theme-integration
description: EkAgent Team modülünü müşteri temasındaki ekip ve çalışan tanıtım bölümlerine eşlerken kullan; uzmanlık veya kullanıcı profil kartlarını salt başlıkla Team kabul etme.
---

# Team tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Team rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Anlamsal eşleme, gerçek alanlar ve kullanım sınırları rule'dadır; [ortak alan genişletme kurallarını](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) tekrar etmeden referans al.

- Modül kökündeki `app/Models/TeamMember.php`, `database/migrations/` ve `features.json` dosyalarını okuyarak rule'daki alan ve ilişki bilgisini güncel kodla karşılaştır. `name/title/image` alanlarını kartın ad/ünvan/fotoğraf öğeleriyle değerlendir; bölüm başlığını üyenin `title` alanına bağlama. Boş olabilen ünvan ve fotoğrafın tasarımda nasıl ele alınacağını mevcut tema örüntüsünden doğrula; yeni içerik uydurma.
- `app/Http/Controllers/TeamMemberController.php` create/edit akışında `ModuleFeatures::form('Team', 'member')` ve `FeatureValues::fields` kullanır. `resources/views/partials/form.blade.php` ortak `admin.features.fields` bileşenini render eder; `app/Http/Requests/SaveTeamMemberRequest.php` ek alan doğrulamasını `FeatureValues::rules` üzerinden yürütür. `app/Services/TeamMemberService.php` ek değerleri `FeatureValues::prepare` ile hazırlar, `extra_fields` içinde kaydeder ve dosyaları `FieldFiles` ile işler. Bu bağlantıları doğrula; boş `forms` nesnesini altyapının bağlı olmadığı şeklinde yorumlama. İlgili form anahtarı `member` şeklindedir.
- Tema okumasında `TeamFrontendService` kullan. Üretilen admin `photo` sütunu veri alanı değildir; görsel için modelin `image_url` erişimini kullan.
- Modülün kurulu ve etkin olma durumunu ortak skill'e göre doğrula; bunu üyeye ait `is_active` durumundan ayrı değerlendir. Tema kodunda veya kullanıcı talebinde aktiflik filtresi, sıralama, gösterilecek üye grubu, limit ya da kayıt seçimi tanımlı olup olmadığını kontrol et. Admin tablosunun davranışını tema politikası sayma; gereken karar belirsizse kullanıcıya SOR ve yanıt gelmeden ilgili entegrasyonu sürdürme.

## Frontend okuma servisi

- Sınıf: `Modules\Team\Services\TeamFrontendService`; dosya: `app/Services/TeamFrontendService.php`.
- `getAll()` bir `Collection<int, TeamMember>`, `getFirst()` bir `TeamMember` veya `null`, `find(int $id)` yalnız görünür kaydı veya `null` döndürür. `getAll` kendiliğinden limit uygulamaz; sayfalama API'si değildir.
- Yalnız aktif kayıtları döner. Tanımlı bir iş sırası yoktur; ilk/son/en yeni gibi bir öncelik garantisi verilmez. Modül kayıtlı/etkin değilse veya gerekli modül tabloları yoksa koleksiyon metotları boş koleksiyon, tek kayıt metotları `null` döndürür.
- İlişkiler önceden yüklenir: `imageMedia`. Görsel/dosya URL'si için mevcut model erişimcisini kullan; servis yeni route veya HTML üretmez.
- Tek öğe tasarımında `getFirst`, tekrar eden listede `getAll`, bilinen kayıt kimliğinde `find` seç; metodu yalnız kayıt sayısına göre seçip arayüzü otomatik değiştirme. Görünürlük ve modül kontrollerini çağıran tarafta tekrarlama.
