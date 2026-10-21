# Ortak firma geçmişi — BOŞ ŞABLON

Bu ana `customers` kaydı bütün günleri ve sektörleri kapsayan firma kimliği/tekrar seçmeme dizinidir. Temsili örnekler eklenmez. Üretimin ayrıntılı kaynağı firma dosyalarıdır; satış durumlarının esas kaynağı kullanıcının doldurduğu günlük `takip.md` dosyasıdır. Buradaki satış görünümü yalnız kaynağa bağlı türetilmiş özettir; ayrı manuel satış kaynağı değildir.

| Kalıcı firma / şube kimliği | Ad / sektör / konum | Maps URL / Place ID (varsa) | Normalize telefon / adres + ad eşleştirme | İlk gün / firma dosyaları | Teklif hazır mı / teklif yolu / doğrulama tarihi | Manuel gönderim-sonuç özeti / günlük takip kaynağı | Son işlem / tarih / kanıt | Tekrar seçmeme durumu / gerekçe |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| UMUT-OTO-OZEL-SERVIS-BASARAN-USTA-EYUPSULTAN-20261021 | UMUT OTO ÖZEL SERVİS BAŞARAN USTA / oto servis / İstanbul Eyüpsultan | ChIJK1vefIWxyhQR3whz037uc3Q; Maps bağlantısı kanıt dosyasında | +905375420262; Rami Yeni Havuzbaşı Sk. 1/B | [2026-10-21 araştırma](2026-10-21/umut-oto-ozel-servis-basaran-usta-eyupsultan/arastirma-kanitlar.md) | Hayır; tasarım üretilmedi | Gönderilmedi; hazır paket olmadığı için takip satırı açılmadı | 21.10.2026; K6–K8: Yandex aynı adres/telefonla basaranoto.com, site indeksinde farklı konum; ilişki belirsiz | yeniden_inceleme_bekliyor; çelişki çözülmeden tekrar aday/üretim sayılmaz |

## Kontrol ve güncelleme sözleşmesi

Diğer gerçek ön eleme kayıtları (21.10.2026; kaynaklar [günlük raporda](2026-10-21/gunluk-rapor.md)):

- BLUE-WHITE-KADIKOY: Blue white güzellik, Rasimpaşa Söğütlüçeşme Cad. No:101 Kadıköy; +905417442034; Maps kimliği `0x14cab9152eaecaa7:0x1bae26859eb68869`. `elendi`: resmi bluewhitebeauty.com.tr/iletisim aynı adres ve telefonla eşleşiyor. Teklif yok; gönderim yok.
- JUST-BEAUTY-HOUSE-KADIKOY: Just Beauty House, Rasimpaşa Beydağı Sk. No:52/A Kadıköy; +905443213790; Maps kimliği `0x14cab905b7b6a2b5:0x5686fd21811a15c6`. `yeniden_inceleme_bekliyor`: üçüncü taraf eski adres Hasırcı Başı Cad.6A ile ilişki ve altı farklı fotoğraf doğrulanamadı. Teklif yok; gönderim yok.

Bu kayıtlar hazır paket değildir; aynı kimlikler tekrar yeni aday kotasına alınmaz. Satış özeti kaynaksız üretilmez; günlük takipte henüz müşteri satırı yoktur.

Ön eleme kimlik kaydı: SARIOGLU-OTO-EMNIYETTEPE, Sarıoğlu Oto Tamir Bakim Servisi / oto servis / İstanbul Eyüpsultan; +905302365650; Emniyettepe Meydan Dönüşü Sk. No:1. 21.10.2026: `elendi`; altı farklı fotoğraf doğrulanmadı, galeri kartları aynı kaynak kimliğine gidiyor. Teklif hazırlanmadı/gönderilmedi. [Günlük araştırma notu](2026-10-21/gunluk-rapor.md). Maps/Place ID ayrıntı bağlantısı bekleniyor; belirsiz kimlik yeni firma gibi tekrar seçilmez.

- Araştırma başlamadan bu dosya, [sektör özeti](teklif-goturulen-sektorler.md) ve bağlı tüm gün kayıtları kontrol edilir. Her adayın kimliği derin araştırma/üretimden önce karşılaştırılır. Aynı firma/şube yeni günlük aday veya yeni tamamlanmış paket gibi yeniden sayılmaz.
- Eşleştirme önce Place ID, sonra normalize telefon ve tam adres + firma adıdır. Telefonun ülke kodu/biçimi normalize edilir; adres ve şube farkı korunur. Aynı telefon tek başına şubeleri birleştirmez; belirsizlik kanıtla çözülene kadar duraklatılır. Aynı sektördeki başka firmalar topluca elenmez.
- Durumlar için `yeni_kayit`, `islemde_tekrar_secme`, `tamamlandi_tekrar_secme`, `elendi`, `yeniden_inceleme_bekliyor` kullanılabilir; gerekçe ve dosya kanıtı yazılır. Eski firma değişiklik nedeniyle yeniden ele alınacaksa kullanıcı kararı ve değişiklik kanıtı kaydedilir, aynı kimlik korunur ve yeni firma gibi kotaya katılmaz.
- [Teklif götürülecekler](teklif-goturulecekler.md) bu dizinden ve firma üretim dosyalarından alınan çalışma görünümüdür; kimliği ikinci kez bağımsız yönetmez. Günlük `takip.md` içindeki kullanıcı hücreleri/durumları değiştirilmez. Kaynak güncellenince türetilmiş özetin yenilenme zamanı yazılır; kaynaksız gönderim/yanıt/sonuç üretilmez.
- Basit kayıt/hesap önce araçla, metin gerekiyorsa Luna low ile; medium yalnız gerekçeli üst sınırdır. Bu belge çalışan dedup, veri senkronizasyonu veya kilit kodu değildir. Otomasyon duraklatılmıştır; iletişim yoktur.

## Araştırma kimliği kaydı — Suvaroğlu Oto

- Kalıcı kimlik: `SUVAROGLU-OTO-KAPORTA-BOYA-SULTANBEYLI-20261021`.
- SUVAROĞLU OTO KAPORTA BOYA FIRINLI BOYA / oto kaporta-boya / Sultanbeyli, İstanbul; Maps `0x14cad099c5b8fa93:0xf1b0431d72fd22b0`; +905425054412.
- 2026-10-21 araştırma/brif/koşullu metin: [kanıtlar](2026-10-21/suvaroglu-oto-kaporta-boya-sultanbeyli/arastirma-kanitlar.md), [manifest](2026-10-21/suvaroglu-oto-kaporta-boya-sultanbeyli/gorseller-manifesti.md), [brief](2026-10-21/suvaroglu-oto-kaporta-boya-sultanbeyli/analiz-ve-brif.md), [taslak](2026-10-21/suvaroglu-oto-kaporta-boya-sultanbeyli/teklif-taslagi.md). Araştırma kapıları geçti. ImageGen bir kez çağrıldı; çıktı [tasarim.png](2026-10-21/suvaroglu-oto-kaporta-boya-sultanbeyli/tasarim.png) olarak kayıtlı. Tek ekranlık konsept kullanıcı tarafından eksik bildirildi; [tasarım durumu](2026-10-21/suvaroglu-oto-kaporta-boya-sultanbeyli/tasarim-ciktisi.md). Yeni üretim ve gönderim yapılmadı; 0/3 kotasında kalır, kullanıcı talimatı beklenir.


## 2026-10-21 — ilk 10 içindeki altı detay inceleme

Mevcut ön tarama kimlikleri korundu; mükerrer yeni firma kaydı oluşturulmadı. Ayrıntılar `2026-10-21/{firma-klasörü}/arastirma-ve-nihai-puan.md` içindedir.

| Firma | Ön → nihai | Durum |
|---|---:|---|
| Su Yolu Tesisat | 74 → 52 | aday havuzu; resmi site doğrulandı |
| Kavaca Premium Araç Kaplama Merkezi | 65 → 54 | aday havuzu; kurumsal alan adı ile yerel merkez bağı belirsiz |
| Krom İstanbul jant boyama kumlama cnc | 68 → 68 | aday havuzu; olası business.site açılmadı/telefon eşleşmedi |
| Merter Çınar Yapı Tesisat İnşaat | 70 → 77 | yüksek öncelik; tasarım kararı ve teklif taslağı dosyada |
| İsista Beauty & Kuaför | 71 → 76 | yüksek öncelik; tasarım kararı ve teklif taslağı dosyada |
| ARTS BEAUTY STUDIO | 70 → 75 | yüksek öncelik; tasarım kararı ve teklif taslağı dosyada |

Gönderim yok. ImageGen çağrısı yok. Kullanıcı takip hücreleri değiştirilmedi. Her altı firma için maliyet kaydı vardır; erişilemeyen token/fatura ölçümleri `ölçülemiyor` yazıldı.

ARTS BEAUTY STUDIO ek doğrulaması (2026-10-21): kullanıcı tarafından verilen `instagram.com/artsbeautystudio/` kamuya açık profilinde 317 gönderi ve 1.722 takipçi, Sağlık/Güzellik kategorisi ve bio hizmet listesi (protez tırnak, kalıcı oje, G5, lazer epilasyon, kirpik lifting, kaş tasarımı/laminasyonu) görüldü. Tasarım kararı bu doğrulanmış metinle güncellendi. Görseller indirilmedi veya izinli varlık olarak kaydedilmedi.
