---
name: users-theme-integration
description: Users web kullanıcı modülünü giriş, hesap, e-posta doğrulama ve şifre sıfırlama alanlarına bağla. Sunucu tarafı frontend servisinin gerçek sözleşmesini kullan; ekip tanıtımı veya admin girişini bu modüle yönlendirme.
---

# Users entegrasyonu

[Modül kuralını](../../.ai/rules/tema-entegrasyonu.md) ve tema entegrasyonu yapılıyorsa [ortak tema skill'ini](../../../../.claude/skills/theme-integration/SKILL.md) oku. Kodun güncel metotlarını `app/Services/UsersFrontendService.php`, route'ları `routes/web.php`, model alanlarını `app/Models/User.php` üzerinden doğrula.

## Frontend servis sözleşmesi

Sınıf: `Modules\Users\Services\UsersFrontendService`. Laravel container üzerinden çözülür; ek facade, repository veya bağımsız kimlik doğrulama altyapısı oluşturma.

| Metot | Parametre ve dönüş | Davranış |
|---|---|---|
| current | parametresiz, User veya null | Yalnız mevcut aktif web hesabı; modül kapalı veya gerekli tablo yoksa null. Genel liste değildir. |
| login | Request, User | email/password ve isteğe bağlı remember; doğrulama, deneme sınırı, web oturumu yenileme. Başarılı girişler Login olayıyla kaydedilir. |
| logout | Request, void | Web guard çıkışı, session invalidation ve CSRF token yenileme. |
| sendEmailVerification | parametresiz, bool | Mevcut aktif hesap gerekir. Zaten doğrulanmışsa false; aksi halde süreli imzalı e-posta gönderir, deneme sınırı uygular. |
| verifyEmail | Request, bool | Mevcut hesap, request route id/hash ve geçerli imza gerekir. İlk doğrulamada Verified olayı; daha önce doğrulanmışsa false. |
| sendPasswordResetLink | string email ve Request, string | users broker üzerinden sıfırlama bildirimi; hesap varlığını açıklamayan ortak durum değeri. |
| resetPassword | array credentials, string | email, token, password, password_confirmation; güçlü/uyumlu şifre doğrulaması, broker token kontrolü, remember token yenileme ve PasswordReset olayı. |

- login, sendPasswordResetLink ve resetPassword modül kullanılamıyorsa 404 üretir. Oturum gerektiren doğrulama işlemleri kullanılabilir aktif hesap yokken AuthenticationException üretir. logout mevcut web oturumunu kapatabilir. login başarısızlığı, doğrulama ve servis deneme sınırları ValidationException üretir. Broker sıfırlama hataları durum metni döndürür; controller başarısız durumları 422 olarak sunar.
- HTTP akışları `users.*` named route'larında, web/session/CSRF ve modül etkinliği middleware'i altında sunulur. JSON istemcileri JSON kabul başlığı ve CSRF korumasını kullanmalıdır. İmzalı doğrulama URL'sini elle yeniden üretip parametrelerini değiştirme.
- E-posta bildirimleri modüle özgü verify/reset route'larını üretir; bunlar şu anda backend JSON yanıtı verir. Tema ekranı yoktur. Yeni tasarımda ekranları ayrıca ekle; var olan ekran veya kayıt olma endpoint'i varsayma.
- Başarılı giriş geçmişi `User::logins()` ile en yeni giriş ve id sırasındadır; son giriş alanları user üzerindedir. Genel kullanıcı okuma, sıralama/limit veya profil güncelleme API'si sunulmaz. Hesap verilerini anonim ziyaretçiye listeleme.
- Profil resmini sunarken modelin public disk yolundan Storage URL'si üret. Backend kayıt güncellemesi `UserService::save`, dosya değişimi ortak FieldFiles ile yapılır. Web ziyaretçisine bu yönetim servisini doğrudan açma.
- Panelde `admin.web-users.*` altında liste/düzenleme/silme ve `users.menu/view/update/delete` admin izinleri vardır. Bunlar web hesaplarına rol eklemez.
- Ortak alan altyapısı için [modül kurallarını](../../../../.ai/rules/moduller.md) kullan; modülün boş features tanımına dayanmayan alanlar uydurma.
