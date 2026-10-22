# Faz 1 — CMS Dashboard ve Analitik Planı

## Amaç

CMS yönetim panelinde işletme sahibine güncel ve eyleme dönük bir özet sunmak; ziyaret ve cihaz kullanımını aylık grafiklerle göstermek ve en çok ziyaret edilen 20 sayfayı listelemek. Bu dosya Faz 1 planını tanımlar; bu aşamada kod veya veritabanı değişikliği yapılmayacaktır. Uygulama daha sonra güçlü bir modele devredilecektir.

## Kapsam

### Hızlı özet kartları

- Bu ayki ziyaret sayısını geçen ayla karşılaştırır.
- Yeni form başvurularının sayısını gösterir ve form başvuruları ekranına geçiş sağlar.

### Yıllık toplam ziyaret grafiği

- Seçilen yılın Ocak–Aralık ziyaret sayılarını gösterir.
- Veriyi Misafir ve Kullanıcı serilerine ayırır.

### Yıllık cihaz kullanımı grafiği

- Seçilen yılın Ocak–Aralık cihaz kullanımını gösterir.
- Bilgisayar, Telefon ve Diğer serileri bulunur.
- Cihaz türü filtresi seçildiğinde grafikte ilgili türün aylık dağılımı gösterilir.

### En çok ziyaret edilen ilk 20 sayfa

- Sayfaları ziyaret sayısına göre azalan sırada listeler ve 20 kayıtla sınırlar.
- Dashboard ana görünümünde ilk birkaç sayfa kısa özet olarak gösterilir; tam listede ilk 20 sayfa yer alır.
- Aynı sayfanın URL'leri gruplanırken takip parametreleri gibi değişken sorgu parametreleri normalize edilir.
- Tarih aralığı (seçilen yıl veya tüm zamanlar) henüz kararlaştırılmadı.
- Mevcut panelde görülen tarayıcı, cihaz ve şehir alt bilgileri bu listenin kesin kapsamına henüz alınmadı.

## Ziyaret sayımı ve tekrarları birleştirme

- Aynı IP adresinden gelen tekrarlar 30 dakikalık pencere içinde birleştirilir.
- Genel ziyaret sayımında aynı IP'nin pencere içindeki tekrarı tek ziyaret kabul edilir.
- Sayfa listesinde aynı IP'nin aynı normalize edilmiş sayfaya pencere içindeki tekrarları tek sayılır; aynı ziyaretçinin farklı sayfaları her sayfa için birer kez sayılabilir.
- Ham IP adresi kalıcı olarak saklanmaz; tekrar kontrolü için geçici, anahtarlı bir özet kullanılır.
- IP temelli sayım yaklaşık sonuç verir: ortak IP kullanan kişiler birleşebilir, IP'si değişen aynı kişi yeniden sayılabilir.

## Veri saklama yaklaşımı

- Bu analitik özellik mevcut bir analitik yapı olmadığı için sıfırdan tasarlanacaktır.
- Kayıtlar genel amaçlı kullanıcı hareketleri tablosuna karıştırılmaz.
- Her sayfa isteğini ham olay olarak kalıcı biçimde saklamak yerine günlük özet sayaçları tutulur.
- Yalnızca gerçek ziyaretçi sayfaları ölçülür; yönetim paneli, API, statik dosyalar ve bot trafiği kapsam dışıdır.
- 30 dakikalık tekrar denetimi geçici önbellekte yapılır. Uygulama sırasında kullanılacak cache sürücüsü kontrol edilir; sırf bu özellik için yeni altyapı eklenmez.

### Önerilen analitik özet tablosu

Bu şema kavramsal taslaktır; uygulama aşamasında Laravel ve veritabanı kurallarına göre kesinleştirilecektir.

| Alan | Amaç |
|---|---|
| `id` | Birincil anahtar |
| `stat_date` | Günlük sayım tarihi |
| `scope_key` | `__site__` genel ziyaretler için; normalize edilmiş sayfa yolu sayfa istatistiği için |
| `visitor_type` | `guest` veya `user` |
| `device_type` | `computer`, `phone` veya `other` |
| `visit_count` | İlgili kırılımdaki günlük toplam |
| `created_at`, `updated_at` | Oluşturma ve güncelleme zamanları |

Önerilen benzersiz kırılım: `stat_date + scope_key + visitor_type + device_type`. Yeni sayım, aynı kırılımın `visit_count` değerini artırır.

## Arayüz referansı

- Kullanıcının paylaştığı Tabler admin template önizlemesi grafik ve sayfa tablosu için görsel referanstır.
- Projenin onaylı admin arayüz tercihi Tablerio'dur. Uygulamada proje içindeki mevcut Tabler bileşenleri ve tablo düzeni kullanılmalıdır.

## Faz 1 dashboard tasarım taslağı

![Faz 1 dashboard tasarım taslağı](Faz%201%20Dashboard%20Tasar%C4%B1m%C4%B1.png)

Bu görsel, mevcut boş dashboard yerleşimi ve paylaşılan Tabler grafik/tablo örnekleri temel alınarak hazırlanmış bir tasarım önerisidir. Grafikteki sayılar yalnızca örnektir. Popüler sayfa tablosu Faz 1 kapsamına uygun olarak Sayfa, Ziyaret ve Trend bilgilerini gösterir; tekil ziyaret ve hemen çıkma oranı kapsamda değildir.

## Faz 2

- Kurulu ve etkin modüllere göre değişen modül bazlı dashboard özetleri Faz 2'de ayrıca planlanacaktır.
- Modül bazlı özetler Faz 1 kapsamına dahil değildir.

## Kapsam dışı

- Tarayıcı girişleri ve genel toplam veri kartları.
- Modül bazlı dashboard özetleri (Faz 2'de planlanacak).
- Uzun süre saklanan ham ziyaretçi/IP hareket geçmişi.
- Bu planlama aşamasında migration, model, servis, dashboard ekranı veya test yazılması.

## Uygulamadan önce netleştirilecekler

1. İlk 20 sayfa hangi zaman aralığına göre sıralanacak: seçilen yıl mı, tüm zamanlar mı?
2. İlk 20 tablosunda URL ve ziyaret sayısına ek olarak tarayıcı, cihaz ve şehir bilgileri istenecek mi?
3. 30 dakikalık IP birleştirme kuralının paylaşılan IP'lerde doğurabileceği yaklaşık sayım etkisi kabul ediliyor mu?
