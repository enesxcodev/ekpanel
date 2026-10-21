# Ticari paket yapılandırması — KULLANICI ONAYI YOK

Bu boş şablon gerçek teklif veya taahhüt değildir. Fiyat/şartlar henüz verilmedi; tüm değerleri kullanıcı belirler. AI üretim maliyeti satış fiyatı değildir, firma `maliyet.md` kaydında ayrı kalır.

Yapılandırma sürümü: kullanıcı belirleyecek. Onaylayan: kullanıcı belirleyecek. Açık onay tarihi/kaydı: kullanıcı belirleyecek. Durum: **onaysız / eksik; geçerli ticari teklif üretilemez**.

| Alan | Kullanıcı onaylı değer |
| --- | --- |
| Paket adı / kapsam | Kullanıcı belirleyecek |
| Toplam fiyat / para birimi | Kullanıcı belirleyecek |
| Tek seferlik geliştirme bedeli | Kullanıcı belirleyecek |
| Vergi dahil / hariç ve varsa uygulanacak oran | Kullanıcı belirleyecek; oran varsayılmaz |
| Domain dahil / hariç; varsa bedel / dönem | Kullanıcı belirleyecek |
| Hosting dahil / hariç; varsa bedel / dönem | Kullanıcı belirleyecek |
| Yıllık bakım dahil / hariç; varsa bedel / dönem | Kullanıcı belirleyecek |
| Toplamın kapsadığı dönemsel kalemler / ayrı ödenecekler | Kullanıcı belirleyecek |
| Teslim süresi / başlangıç koşulu | Kullanıcı belirleyecek |
| Revizyon hakkı / kapsamı | Kullanıcı belirleyecek |
| Yönetim paneline dahil modüller | Kullanıcı belirleyecek |
| Kapora tutarı veya oranı / ödeme zamanı | Kullanıcı belirleyecek |
| Kalan ödeme tutarı veya oranı / ödeme zamanı | Kullanıcı belirleyecek |
| Teslim koşulları | Kullanıcı belirleyecek |

Ajan yalnız açık kullanıcı onaylı, sürümü belirli değerleri kopyalar; fiyat, indirim, teslim, revizyon, modül veya ödeme koşulu üretmez. Hariç/uygulanmaz kararı da kullanıcıdan gelir. Eksik alan “kullanıcı belirleyecek” kalır ve ticari teklif hazır sayılmaz. Açık onay, kapsam ve tutar tutarlılığı doğrulanmadan kullanıma hazır etiketlenmez.

Firma için `ticari-teklif.md` oluşturulurken kaynak yolu, sürüm, onay kaydı/tarihi ve kopyalama zamanı yazılır; onaylı değerlerin o sürümdeki görüntüsü korunur. Sonraki global değişiklik mevcut firma teklifini değiştirmez; kullanıcı isterse yeni sürüm açılır, eski kayıt korunur.

Brifteki yumuşak modül önerileri sözleşmesel dahil modül listesi değildir. Dahil modüller yalnız bu paketin kullanıcı onaylı listesinden gelir. Müşteri “ne kadar” dediğinde onaylı doldurulmuş paket kullanıcıya gösterilebilir; yanıt veya gönderim otomatik yapılmaz.
