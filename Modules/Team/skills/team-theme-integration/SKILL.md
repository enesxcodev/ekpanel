---
name: team-theme-integration
description: EkAgent Team modülünü müşteri temasındaki ekip ve çalışan tanıtım bölümlerine eşlerken kullan; uzmanlık veya kullanıcı profil kartlarını salt başlıkla Team kabul etme.
---

# Team tema entegrasyonu

[Ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) ve [Team rule'unu](../../.ai/rules/tema-entegrasyonu.md) oku. Anlamsal eşleme, gerçek alanlar ve kullanım sınırları rule'dadır; [ortak alan genişletme kurallarını](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) tekrar etmeden referans al.

- Modül kökündeki `app/Models/TeamMember.php`, `database/migrations/` ve `features.json` dosyalarını okuyarak rule'daki alan ve ilişki bilgisini güncel kodla karşılaştır. `name/title/image` alanlarını kartın ad/ünvan/fotoğraf öğeleriyle değerlendir; bölüm başlığını üyenin `title` alanına bağlama. Boş olabilen ünvan ve fotoğrafın tasarımda nasıl ele alınacağını mevcut tema örüntüsünden doğrula; yeni içerik uydurma.
- `app/Http/Controllers/TeamMemberController.php` create/edit akışında `ModuleFeatures::form('Team', 'member')` ve `FeatureValues::fields` kullanır. `resources/views/partials/form.blade.php` ortak `admin.features.fields` bileşenini render eder; `app/Http/Requests/SaveTeamMemberRequest.php` ek alan doğrulamasını `FeatureValues::rules` üzerinden yürütür. `app/Services/TeamMemberService.php` ek değerleri `FeatureValues::prepare` ile hazırlar, `extra_fields` içinde kaydeder ve dosyaları `FieldFiles` ile işler. Bu bağlantıları doğrula; boş `forms` nesnesini altyapının bağlı olmadığı şeklinde yorumlama. İlgili form anahtarı `member` şeklindedir.
- Tema okuması için `Modules\Team\Models\TeamMember` ve mevcut sorguları incele. `app/DataTables/TeamMembersDataTable.php` model sorgusunu kullanır; üretilen `photo` sütunu veri alanı değildir. `TeamMemberService` mevcut durumda `save/delete` işlemlerini sağlar; var olmayan tema okuma metodunu veya ilişkiyi çağırma. Mevcut model/servis erişimini kullan; sırf entegrasyon için facade/helper veya tema veri sınıfı ekleme.
- Modülün kurulu ve etkin olma durumunu ortak skill'e göre doğrula; bunu üyeye ait `is_active` durumundan ayrı değerlendir. Tema kodunda veya kullanıcı talebinde aktiflik filtresi, sıralama, gösterilecek üye grubu, limit ya da kayıt seçimi tanımlı olup olmadığını kontrol et. Admin tablosunun davranışını tema politikası sayma; gereken karar belirsizse kullanıcıya SOR ve yanıt gelmeden ilgili entegrasyonu sürdürme.
