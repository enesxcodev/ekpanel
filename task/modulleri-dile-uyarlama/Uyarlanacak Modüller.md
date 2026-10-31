# Dil Entegrasyonu Yapılacak Modüller

- Sayfalar ✓ — `D:\localhost\ekagent\Modules\Pages`
- Menüler ✓ — `D:\localhost\ekagent\Modules\Menus`
- Şubeler ve Bayiler ✓ — `D:\localhost\ekagent\Modules\Branches`
- Popup ✓ — `D:\localhost\ekagent\Modules\Popup`
- Ürünler ✓ — `D:\localhost\ekagent\Modules\Products`
- Slider ✓ — `D:\localhost\ekagent\Modules\Slider`
- Mağaza ✓ — `D:\localhost\ekagent\Modules\Store`
- Ekipler ✓ — `D:\localhost\ekagent\Modules\Team`
- Markalar ✓ — `D:\localhost\ekagent\Modules\Brand`
- Etkinlikler ✓ — `D:\localhost\ekagent\Modules\Events`
- Sektörler ✓ — `D:\localhost\ekagent\Modules\Sector`
- Müşteri Yorumları ✓ — `D:\localhost\ekagent\Modules\Testimonials`
- Sıkça Sorulan Sorular ✓ — `D:\localhost\ekagent\Modules\Faqs`
- Belgeler ✓ — `D:\localhost\ekagent\Modules\Documents`
- Projeler ✓ — `D:\localhost\ekagent\Modules\Projects`

## Planlama ajanı için ortak arayüz gereksinimi

Dil uyarlaması planlanırken ilgili modülün form arayüzüne dil seçici de dahil edilmelidir:

- Form düzeni, içerik alanı ve dil seçici için uygun sütun oranıyla kurulmalıdır; Blog modülündeki form frontend uygulaması incelenerek 8/4 veya 9/3 düzenlerinden uygun olanı seçilmelidir.
- Sağ tarafta yalnızca aktif diller listelenmelidir.
- Kullanıcı bir dili seçtiğinde, form o dile ait mevcut verileri yükleyip göstermelidir. Seçili dilin alanları birbirine karışmamalı ve kullanıcı dil değiştirince doğru dile ait kayıtlar düzenlenebilmelidir.
- Plan, sadece ana modül kayıtlarını değil; kategori gibi ilişkili ve dile göre değişmesi gereken alt verileri, ayrıca ilgili rule, skill ve frontend servislerini de kapsamalıdır.
- Planlama ajanı Blog modülündeki tamamlanmış dil uyarlamasını ve özellikle Blog formunun frontend kısmını fikir ve örüntü referansı olarak incelemelidir. Blog yapısı birebir kopyalanmamalı; her modül kendi alanlarına, ilişkilerine ve davranışına göre tasarlanmalıdır.

## Dil altyapısı ve veri kuralları

- Dil modeli `Models\Settings\Language.php` olacaktır. Planlama ve uygulama öncesinde projedeki mevcut model ve namespace yapısı doğrulanmalıdır.
- Çeviri şeması için Blog modülündeki `blog` ve `blog_translation` tablo/model ilişkisi örnek alınmalıdır. Her modül kendi ana tablosu ve uygun çeviri tablosu üzerinden planlanmalı; var olan proje kalıpları incelenmeden yeni bir ortak şema varsayılmamalıdır.
- Varsayılan dil Türkçe ve aktif olacaktır. Mevcut varsayılan dil ayarı/seed davranışı korunmalı ve tutarlı biçimde kullanılmalıdır.
- Kategori ve benzeri ilişkili kayıtlar dile göre doğru bağlanmalı; listeleme, form seçimi ve kayıt işlemlerinde dil uyumu kontrol edilmelidir.

## Otomasyonun çalışma akışı

- Otomasyon yalnızca kullanıcı açıkça “başlat” veya “devam et” dediğinde çalışmaya başlar. Her tetiklemede listedeki sıradaki **tek** tamamlanmamış modül işlenir. O modül planlama, uygulama, migration, regresyon testi ve Ego Browser doğrulamasından geçip tamamlanınca koordinatör raporu yazar, `✓` koyar ve durur. Yeni modüle geçmek için kullanıcıdan yeni tetikleme beklenir.
- GPT-6 Luna koordinatör rolü, listedeki ilk tamamlanmamış modülü seçer ve yalnızca o modül için sırayla planlama, uygulama ve test görevlerini devreder.
- Planlama ajanı GPT-6 Luna (low) olur. Modülün kendi alanlarını, ilişkilerini, migration/veri korumasını, model/servis/frontend davranışını ve modüle ait rule/skill dosyalarını inceler; Blog'u yalnız örüntü referansı alır. Blog yapısını birebir kopyalamaz. Plan kısa ve dosya odaklı olmalı; belirsiz veya ilgisiz araştırma tekrarlanmamalıdır.
- Uygulama ajanı GPT-6 Luna (low) olur. Planı ve mevcut modül kodunu kullanır; planlamada zaten doğrulanmış noktaları yeniden keşfetmez. Plan teknik olarak yanlışsa düzeltip gerekçesini plana yazar. Uygulamayı tamamladıktan sonra yalnız kendi modülünün bekleyen migration'larını koordinatöre bildirir.
- Koordinatör, uygulama tamamlanınca test ajanına geçmeden önce yalnızca o modülün bekleyen migration'larını uygulama veritabanında çalıştırır (modül migration yolu/scope'u ile). Genel `migrate:fresh`, reset veya ilgisiz modül migration'ları çalıştırılmaz. Migration başarısız olursa test devri yapılmaz; hata düzeltilir ya da somut engel raporlanır.
- Migration başarılı olunca test ajanı yalnızca o an işlenen modülün regresyonunu doğrular. Proje Pest kullanır (PHPUnit üzerinde çalışır); Laravel test araçları Pest sözdizimiyle kullanılır. Önce modülün mevcut testleri ve `testing-best-practices` yönergesi incelenir. Saf, dış bağımlılığı olmayan modül mantığı (dil/çeviri seçimi gibi) için kısa unit test tercih edilir. Veritabanı, HTTP veya framework servisi gerekiyorsa mevcut teste küçük bir senaryo eklenir ya da en dar kapsamlı feature testi yazılır. Yeni test dosyası/senaryosu yalnızca belirli bir regresyon açığını kapatıyorsa eklenir; aynı davranış unit, feature ve tarayıcıda tekrar tekrar sınanmaz. Yalnız ilgili modülün hedefli testleri çalıştırılır; tüm test paketi ve başka modüller çalıştırılmaz. Test kodu `phpunit.xml` içindeki izole testing bağlantısını ve küçük factory/fixture verisini kullanır; uygulama verisinin tamamı veya seçilmiş ilişkili tablolar başka bir SQLite'a kopyalanmaz. Modül migration'ı uygulama veritabanında çalıştıktan sonra, backfill ve mevcut kayıtların korunması aynı uygulama tablolarında salt okunur sorgularla doğrulanır; legacy içerik Türkçe çevirisine taşınır, mevcut çeviriler ezilmez. Ego Browser/Playwright ile o modülün tek bir temel uçtan uca dil akışı doğrulanır; yalnız test için açılan kayıtlar açıkça QA olarak adlandırılıp kontrol bitince silinir. Modül planlarındaki daha geniş test fikirleri kabul kriterlerini belirlemek içindir; hepsini ayrı testlere dönüştürmek zorunlu değildir.
- Testler veya tarayıcı doğrulaması hata bulursa uygulama ajanına yalnız ilgili hata ve dosya/senaryo bilgisi devredilir; değişmeyen keşif ve raporlar tekrar gönderilmez. En fazla iki odaklı düzeltme-test döngüsünden sonra sorun çözülmediyse modül işaretlenmez ve somut engel raporlanır.
- Bir modül ancak uygulama, kendi migration'ı, hedefli regresyon kontrolleri, Ego Browser doğrulaması, rule/skill/frontend service uyumu ve staging tamamlanınca `✓` alır. Ajanlar sonucu raporlayıp koordinatöre döner; koordinatör `Raporlar.md` kaydını yapar.
- Ajanlara sohbet geçmişi veya konuşma özeti aktarılmaz. Her ajan yalnız kendi görev tanımını, bu ortak yönergeyi, ilgili modülün klasör yolunu ve gerekiyorsa o modülün plan/durum dosyasını okur. Bu dosyalarda olmayan zorunlu bir kullanıcı kararı varsa koordinatör yalnız o kararı görev metnine ekler. Yeni ajanlar konuşma geçmişi almadan başlatılır (`fork_turns: none`); tekrar araştırma yerine mevcut ortak dosyalara başvurulur.
- Yarım bırakma olmaması için her devirde beklenen çıktı ve tamamlanma ölçütü açıkça belirtilir. Ajan görevi bitirir ya da hangi somut teknik/araç engelinin durdurduğunu, hangi adımların kaldığını bildirir; sessizce kısmi değişiklikle tamamlandı demez.

## Modül tamamlanma raporları

- Her modülün uygulaması ve doğrulaması tamamlandığında `Raporlar.md` dosyasına o modüle ait rapor eklenmelidir.
- Rapor; modül adı ve tarih, incelenen kapsam/ilişkili alanlar, yapılan dil uyarlama değişikliklerinin özeti, eklenen veya değiştirilen migration/veri yapısı, güncellenen model/servis/rule/skill/frontend parçaları, çalıştırılan testler ve sonuçları, Ego Browser/Playwright doğrulama sonucu ve bilinen notları içermelidir.
- Başarısız veya çalıştırılamamış kontroller açıkça belirtilmelidir; doğrulanmamış iş başarılı gibi raporlanmamalıdır.
- Rapor kaydı tamamlanmadan modül listesinde tamamlandı işareti konmamalı; rapor ve doğrulama başarılıysa modülün yanına `✓` eklenmelidir.
- `Raporlar.md` modül sırasına göre tutulmalı; her yeni rapor önceki raporları koruyarak eklenmelidir.

## Ajanlar arası devir özeti

- Aynı modül içinde planlama → uygulama → ilgili migration → hedefli regresyon ve Ego Browser → rapor ve ✓ sırası otomatik ilerler; kullanıcı arada onay vermez.
- Modül tamamlanınca iş akışı sona erer. Koordinatör sıradaki modülü başlatmaz; yeni kullanıcı tetiklemesini bekler.
- Model veya akıl yürütme ayarı platformda seçilemiyorsa koordinatör bunu açıkça bildirir; farklı model kullanılmış gibi davranmaz.





