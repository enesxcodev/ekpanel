# Agent başlangıç indexi

Bu dosya başlangıçta okunur. Önce yalnız bu index, `seri-durumu.md`, gerekli alanlarıyla `yapilandirma.md` ve `business-index.jsonl` yüklenir. Uzun plan, modül kataloğu, sektör raporları ve geçmiş firma dosyalarının tamamı varsayılan bağlama alınmaz; yalnız mevcut aşama gerektirirse ilgili bölüm/dosya açılır.

## Çalışma durumu

- Otomasyon: duraklatılmış; kullanıcı açıkça başlatmadan araştırma/üretim yok.
- Maps: görev başına en çok 10 farklı profil.
- Mükerrerlik: önce `check-business-duplicate.ps1` ile JSONL kayıtlarını karşılaştır; belirsiz telefon eşleşmesi otomatik birleştirilmez.
- Eski kriterle elenmiş aday: aynı kalıcı kimliği koruyarak yeni kurallarla yeniden değerlendirilebilir.
- Aşama kapıları ve tam kurallar: [is-plani.md](is-plani.md); yalnız gereken bölümleri aç.
- Durum/kota: [seri-durumu.md](seri-durumu.md).
- Yapılandırma: [yapilandirma.md](yapilandirma.md); yalnız çalışılan aşama için gereken satırlar.
- Tasarım kararı: 75+ nihai puanda [tasarim-karari-sablonu.md](tasarim-karari-sablonu.md) kullan; otomatik ImageGen çağrısı yapma.
- Kimliği doğrulanmış Instagram/sosyal hesapları hizmet metni ve görsel örnek kaynağı olabilir; post URL/tarih, görsel denetimi ve kullanım izni durumunu tasarım kararında ayrı kaydet. Ayrıntı: [iş planı](is-plani.md#içerik-yeterliliği-ve-tasarım-stratejisi).
- Referanslar: önce [sektör notları](sektorler/teklif-sektor-raporlari.md); yetersiz/eskiyse en fazla bir rakip sitesinin yalnız ana sayfası.
- JSONL kayıtları güncellenir aşama sırasında; günlük Markdown raporu yalnız tur kapanışında üretilir. Mevcut/historical dosyalar silinmez veya sessizce yeniden yazılmaz.
- Gönderim ve yanıtların kaynağı kullanıcının doldurduğu `takip.md`; müşteriyle iletişim yok.
- Maliyet/token ölçümü erişilemiyorsa `ölçülemiyor` yaz; gerçek gibi tahmin üretme.
