# Dil Entegrasyonu Modül Raporları

Modül raporları, uygulama ve doğrulama tamamlandıkça aşağıya kronolojik/modül sırasıyla eklenecektir.

## Sayfalar

Sayfa ve kategori çevirileri, Türkçe varsayılanı, dil seçici, kategoriye göre yerelleştirilmiş URL/SEO ve menü/frontend servisleri uygulandı; kaydedilmemiş form verisi dil değişiminde korunuyor. Pint, 17 özellik testi, 2 JS testi, SEO regresyonu, Vite build ve asset yayını geçti. Ego Browser’da TR/EN CRUD ve public URL’ler doğrulandı. Migration yalnız izole SQLite’ta çalıştırıldı; Pages kaynakları staging ile eşleşiyor.

## Menüler

Menü adları ve öğe ağaçları dil bazında ayrıldı; Pages/Services kaynakları seçili dili izliyor. Dil geçişinde menü seçimi korunuyor; sıralama ve silme diğer dile taşmıyor, kaydedilmemiş değişiklik korunuyor. 19 test/91 assertion, 2 JS regresyonu, Pint, Vite build ve asset yayını geçti. Ego Browser’da TR/EN CRUD, sıra, silme ve renderer çıktısı doğrulandı. QA menüsü temizlendi; migration uygulandı, staging eşleşti.

## Şubeler ve Bayiler

Şube/kategori çevirileri, Türkçe backfill, dil seçici, locale duyarlı frontend ve güncel rule/skill eklendi. Edit alanı dolumu ve dil geçişindeki kayıpsız uyarı düzeltildi. Branches testleri 27/27, JS 2/2; Pint ve Vite build geçti. Migration uygulandı, Ego Browser TR/EN kayıt/düzenleme ve uyarıları doğruladı. Geçici kayıtlar temizlendi; staging kaynakla eşleşti.

## Popup

Başlık/içerik çevirileri ve Türkçe backfill eklendi; ortak yayın ayarları diller arasında korundu. Admin dil seçici, frontend Türkçe fallback’i ve tema rule/skill güncellendi. Popup migration’ı uygulandı; hedefli regresyonlar 47/47, Pint ve Ego Browser TR/EN akışı geçti. QA kaydı silindi; 16 kaynak dosyası staging ile hash doğrulandı.

## Ürünler

Ürün/kategori çevirileri, Türkçe backfill, dil seçiciler ve locale uyumlu frontend eklendi; kategori ilişkisi ve medya ortak kaldı. Migration uygulandı; 4 ürün/3 kategori Türkçe çevirili, eksik kayıt yok. Products testleri 31/31 geçti. Ego Browser’da TR/EN ürün ve kategori akışı doğrulandı; QA verisi temizlendi. 25 staging dosyasının hash’i eşleşti.

## Slider

Başlık, alt başlık ve ek alanlar dile bağlandı; görsel/aktiflik ortak kaldı, sıralama eklenmedi. Migration uygulandı; mevcut Slider’ın Türkçe çevirisi var, eksik backfill yok. Servis kayıt FK’si ve DataTable dil aktarımı düzeltildi. Slider testleri 32/32 geçti. Ego Browser TR/EN alan ayrımını doğruladı; QA kaydı silindi. 16 staging dosyasının hash’i eşleşti.

## Mağaza

Ürün/kategori içerikleri, slug, dinamik alanlar ve Store arayüzü dile bağlandı; fiyat/stok ve varyant anahtarları ortak kaldı. Sipariş metinleri dilde snapshot’lanıyor. Store migration’ı uygulandı; mevcut 1 ürünün TR çevirisi mevcut, kategori yok. TR/EN yönetim seçicisi ve public akış doğrulandı; İngilizce çevirisi olmayan ürün gizleniyor. Hedefli testler 20/20 geçti (129 assertion); Ego Browser kontrolünde QA kaydı bırakılmadı. Rule/skill ve staging eşitlendi.

## Ekipler

Üye adı, ünvan ve ek alanlar dil çevirilerine taşındı; fotoğraf ve aktiflik ortak kaldı. Türkçe backfill migration’ı uygulandı; mevcut üye yoktu. Yönetim formundaki TR/EN değişimi Ego Browser’da doğrulandı; İngilizce alanlar Türkçe veriyi değiştirmedi. Hedefli Team testleri 48/48 geçti (234 assertion); QA kaydı temizlendi. Rule/skill güncellendi, 48 staging dosyasının hash’i eşleşti.

## Markalar

Marka adı, kısa açıklama ve ek alanlar dile bağlandı; logo/link gibi ortak alanlar korundu. Migration sonrası 5 markanın 5 Türkçe çevirisi mevcut, QA kaydı yok. Brand hedefli testleri 50/50 (222 assertion) geçti; Ego Browser TR/EN oluşturma-düzenleme ve ortak alanları doğruladı. Rule/skill/frontend servisleri güncellendi; 48 staging dosyasının hash’i eşleşti.

## Etkinlikler

Başlık, mekân ve açıklama çevirileri ayrıldı; tarih, kapasite, aktiflik ve kayıt ilişkisi ortak kaldı. Events migration’ı uygulandı; bu DB’de etkinlik yoktu (0/0 TR backfill, orphan yok). Hedefli testler 6/6 (18 assertion) geçti. Ego Browser’da TR/EN CRUD, ortak alanlar ve kayıt seçimi doğrulandı; QA verisi temizlendi. 50 modül dosyasının staging hash’leri eşleşti.

## Sektörler

Başlık, slug, içerik, SEO ve ek alanlar çeviriye taşındı; görsel, sıra ve aktiflik ortak kaldı. Migration 4 mevcut sektörü 4 TR çevirisine aktardı; orphan yok. Hedefli test grupları 67/67 (298 assertion) geçti. Ego Browser TR/EN form ve listeyi, ortak alanları doğruladı; QA silindi. Tam klasör testi 30 saniyeyi aştı; kapsamlı modül hedefli gruplar geçti. 50 dosya staging ile eşleşti.

## Müşteri Yorumları

Müşteri/firma/unvan/yorum ve ek alanlar çevrildi; fotoğraf, yayın ve sıra ortak kaldı. Migration 2 mevcut yorumu 2 TR çevirisine aktardı; orphan yok. Testimonials testleri 15/15 (72 assertion) geçti. Ego Browser TR/EN admin akışı ve TR anasayfa kartı doğrulandı; QA temizlendi. EN ana sayfa rotası mevcut değil; EN servis testi geçti. 50 dosya staging ile eşleşti.

## Sıkça Sorulan Sorular

Soru/yanıt/ek alanlar ve kategoriler çevrildi; kategori kimliği ortak kaldı. Migration 4 SSS kaydını Türkçe'ye taşıdı; kategori yok, orphan yok. Hedefli testler 11/11 (33 assertion) geçti. Ego Browser TR/EN soru/kategori listelerini doğruladı, QA temizlendi. Ana sayfada EN rotası olmadığı için bölüm Türkçe kaldı; EN servis testi geçti. 61 dosya staging ile eşleşti.

## Belgeler

Belge/kategori metinleri, slug ve ek alanlar çevrildi; dosya, durum ve kategori kimliği ortak kaldı. Migration uygulandı; mevcut belge/kategori yoktu, QA verisi silindi. Hedefli testler 9/9 (28 assertion) geçti. Ego Browser TR/EN içerik ve ortak dosya/durum/ilişkiyi doğruladı. Ana sayfanın EN rotası yok; TR kaynak kartı doğrulandı. 61 dosya staging ile eşleşti.

## Projeler

Proje/kategori metin, slug ve ek alanları çevrildi; kategori kimliği, tarih, yayın, sıra, kapak ve galeri ortak kaldı. Migration 3 projeyi ve 3 kategoriyi TR'ye aktardı; orphan yok. Hedefli testler 28/28 (140 assertion) geçti. Ego Browser TR/EN alanlarını doğruladı; kapak seçici asset bağlantısı düzeltildi ve ikinci görselin seçimi tekrar sınandı. QA temizlendi. Anasayfanın EN rotası mevcut değil; 63 staging dosyası eşleşti.
