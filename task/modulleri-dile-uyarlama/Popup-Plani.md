# Popup dil uyarlama planı

## Kapsam ve karar

Popup tekil zamanlanmış duyuru kaydıdır; kategori/başka ilişkili taksonomi yok. `title`, `content` ve ortak `extra_fields` içerik diline bağlı olmalı. `image_media_id`, `is_active`, `starts_at`, `ends_at`, `repeat_policy`, `repeat_interval`, `repeat_unit` ise tek Popup kaydına bağlı ortak yayın/gösterim ayarları olarak kalmalı; çeviri eksikliği aynı kampanyanın zamanlama/tekrar durumunu çoğaltmamalı. Her dilde içerik yoksa frontend servisinde varsayılan Türkçe (tr) çevirisine dön; o da yoksa kayıt yayınlanmasın. Ziyaretçi durum anahtarı Popup ID’si olarak kalır.

Blog yalnız örüntü referansıdır: `App\Models\Settings\Language` gerçekten mevcut; Blog’daki `blog_contents`/`blog_content_translations`, `lang_id` ve Türkçe backfill yaklaşımına bakıldı. Popup alanları ve visitor-state sözleşmesi farklı olduğu için Blog’un modeli/formu kopyalanmamalı.

## Veri modeli ve migration

- Yeni `Modules/Popup/database/migrations/<timestamp>_create_popup_translations_table.php`: `popup_translations` (`popup_id`, `lang_id`, `title`, `content`, `extra_fields`, timestamps), her Popup/dil çifti için unique; Popup silinince çeviri cascade, kullanılan dil silinmesini engelleyen FK. `extra_fields` dosya alanları olursa media usage owner’ı çeviri modeline taşınmalı.
- Migration önce `languages` içinde aktif `tr` kaydını idempotent biçimde bul/oluşturmalı, mevcut `popups` satırlarını ID sırasıyla Türkçe çeviri satırlarına backfill etmeli, sayım eşitliğini doğrulamalı; ardından `popups.title/content/extra_fields` kolonlarını kaldırmalı. Eski kayıt ve dinamik alan değerleri kaybolmamalı; rollback çeviri kaybı yaratacaksa Blog örüntüsündeki gibi açıkça geri alınamaz olmalı.
- `image_media_id`, tarih aralığı, aktiflik ve tekrar alanları ana tabloda kalır. Önceki kullanım/yerel tablo adlandırma ve migration convention’ları uygulama sırasında doğrulansın.

## Uygulama dosyaları

- `app/Models/Popup.php`: fillable’dan çevrilen alanları çıkar, `translations()` ilişkisi ekle; frontend/admin seçili dili açık relation/servis ile çözümle. `PopupTranslation.php`: Popup ve `Language` ilişkileri.
- `app/Services/PopupService.php`: kayıt transaction’ında aktif `lang_id` doğrula; ortak alanları ana kayda, title/content ve `FeatureValues` extra_fields değerlerini ilgili çeviriye yaz. Dosya yollarını `popup/{id}/translations/{langId}/features` gibi dile özgü yap, save/delete media usage senkronunu translation owner’a göre düzenle.
- `app/Services/PopupLanguageService.php`: aktif dilleri listele, tr’yi ilk sıraya al, query `lang` kodunu yalnız aktif dillerden seç; seçili dil çevirisini ve mevcut çeviri ID’lerini hazırla.
- `app/Http/Requests/SavePopupRequest.php`: zorunlu aktif `lang_id` ve çeviri alanlarını doğrula; ortak tarih/tekrar/medya kurallarını koru. Feature kuralları mevcut Popup feature set’i üzerinden kalmalı.
- `app/Http/Controllers/PopupController.php`: create/edit için seçilen dile ait translation ve feature values, aktif dil listesi/çeviri durumu ver; store/update seçili dil koduna geri dönsün. `app/DataTables/PopupsDataTable.php`: listede varsayılan tr (yoksa tanımlı fallback) başlığı göster; dil seçimi yalnız edit formunu etkilesin.
- `resources/views/partials/form.blade.php`, `create.blade.php`, `edit.blade.php`: içerik kolonu 8/4 (sol içerik, sağ seçici ve ortak ayarlar) düzeninde. Sağ sütunda yalnız aktif diller; seçim linki aynı Popup ID + `lang` ile o dile ait mevcut veriyi açmalı, eksik çeviri boş form göstermeli. POST’ta seçili `lang_id` korunmalı. Ortak yayın/tekrar/görsel kontrolleri sağ sütunda veya içerik alanından görsel olarak ayrılmış bir blokta durmalı; dil değiştirirken bu değerlerin yanlışlıkla çevrildiği izlenimi oluşmamalı.
- `app/Services/PopupFrontendService.php`: isteğe bağlı dil kodu/Language girdisi ekleyip aktif dili doğrula; `published/eligible` sonuçlarında o dil çevirisini seç, eksikse tr’yi dene, iki dil de yoksa gösterme. `shouldShow` zamanlama ve visitor-state davranışı değişmeden seçilmiş/fallback çevirinin varlığını dikkate alsın. Modül kapalı/tablo yok güvenli boş davranışı korunmalı.
- `Modules/Popup/.ai/rules/tema-entegrasyonu.md` ve `Modules/Popup/skills/popup-theme-integration/SKILL.md`: alanların artık translation’da olduğunu, `PopupFrontendService` dil parametresini, fallback ve çevirisiz kayıt davranışını, zamanlama/gösterim-state’in dil bağımsız olduğunu güncelle.

## Hedefli regresyon ve temel akış

- Yalnız `Modules/Popup/tests/Feature/PopupFrontendServiceTest.php` ve `PopupControllerTest.php` üzerinde hedefli senaryolar: legacy kayıtların tr backfill’i/kayıt sayısı, aynı popup’ta iki dilin izole kaydı ve feature değerleri, aktif olmayan `lang_id` reddi, aktif dil içerik seçimi, çeviri yokken tr fallback ve iki çeviri yokken güvenli gösterim dışı kalma. Mevcut zaman aralığı, repeat policy, ordering, disabled/missing-table regresyonları korunmalı; testler çalıştırılacaksa sadece bu kapsam.
- Ego Browser tek temel uçtan uca akış: admin’de Popup oluştur → Türkçe içerik/tarih/gösterim politikasını kaydet → sağdaki aktif ikinci dili seç → ayrı başlık/içerik girip kaydet → iki dili tekrar değiştirerek değerlerin karışmadığını doğrula → frontend servisin seçili dil/fallback sonucunu teyit et.

## Riskler

- `extra_fields` dosyalarının medya kullanımı migration sırasında eski Popup owner’dan yeni translation ID’sine taşınmazsa orphan/yanlış owner oluşabilir; backfill eşlemesi ve `media_usages` güncellemesi transaction/idempotency açısından dikkatle ele alınmalı.
- `popups` kolonlarını kaldıran migration geri döndürülemez veri kaybı riski taşır; backfill sayım/alan kontrolü başarısızsa kolonları silmemeli. Uygulama veritabanında yalnız Popup migration scope’u çalıştırılmalı.
- Varsayılan dil kapalıyken admin’de hangi dilin açılacağı ve frontend fallback politikası belirsiz bırakılmamalı; Türkçe varsayılanın mevcut seed/ayar davranışıyla uyumu doğrulanmalı.
- `PopupFrontendService` çağrı noktaları modül dışındaki tema entegrasyonlarında da olabilir; imza ve skill sözleşmesi birlikte güncellenmeli, dil verilmediğinde önceki davranışın uyumlu varsayılanı korunmalı.
