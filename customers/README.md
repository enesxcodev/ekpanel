# Müşteri adayları çalışma alanı

Bu alan işletme araştırması, iki aşamalı kanıta dayalı lead scoring, tasarım kararı ve kişiselleştirilmiş teklif taslağı sürecini tarif eder. Bu kural güncellemesinde gerçek işletme araştırması veya ImageGen yapılmadı; zamanlanmış otomasyon duraklatılmıştır.

Yürütme sözleşmesi: her 30 dakikada heartbeat ile kaldığı aşamadan devam; seri 3 doğrulanmış teklif taslağında durur. Otomasyon duraklatılmıştır ve bu belge güncellemesi onu etkinleştirmez. Her görevde en çok 10 farklı Google Maps profili incelenir. Fotoğraf/yorum sayıları eleme nedeni değildir; içerik stratejisini belirler. Luna low iki ayrı skor alanı kullanır: ön elemede 60+ veya eksik/belirsiz veri detaylı incelemeye geçer; nihai 75+ tasarım kararı/teklif taslağına, 50–74 aday havuzuna, 0–49 şimdilik hazırlık dışına geçer. Önce sektörel referanslar kullanılır; yetersiz/eskiyse isteğe bağlı en çok bir rakip ana sayfası incelenir. JSONL mükerrerlik indeksi ve script model çağrısından önce kullanılır. Otomatik ImageGen kaldırılmıştır; 75+ adayda `tasarim-karari.md` ve ayrıntılı prompt hazırlanır. Aktiflik, sektör uyumu, iletişim ve site durumu temel kontrollerdir; sitesi doğrulanmış biçimde bulunamayanlar öncelik alır, belirsiz sonuç kesinleştirilmez. İstanbul için gerçek araştırma duraklatılmıştır; bu plan değişikliği otomasyon kurmaz.

- [Kısa agent başlangıç indexi](agent-index.md)\n- [Mükerrerlik JSONL indeksi](business-index.jsonl) ve [kontrol scripti](check-business-duplicate.ps1)\n- [Tasarım kararı şablonu](tasarim-karari-sablonu.md)\n- [Geliştirilmiş iş planı](is-plani.md)
- [Yapılandırma şablonu](yapilandirma.md)
- [Kullanıcı onaylı ticari koşullar şablonu](ticari-kosullar.md) ve [temsili firma ticari teklifi](ornekler/2026-10-21/temsili-oto-atolyesi/ticari-teklif.md): fiyat/şartlar henüz belirlenmedi.
- [Günlük rapor şablonu](gunluk-rapor-sablonu.md)
- [Firma maliyet şablonu — temsili](ornekler/2026-10-21/temsili-oto-atolyesi/maliyet.md): her gerçek firma klasöründe `maliyet.md` oluşturulur.
- [Teklif götürülecekler listesi](teklif-goturulecekler.md)
- [Ortak firma geçmişi](firma-gecmisi.md): tüm günlerde kimlik ve tekrar seçmeme kaydı.
- [Ortak sektör özeti](teklif-goturulen-sektorler.md): kaynaklı üretim/satış/maliyet görünümü; henüz gerçek veri yok.
- [Manuel takip şablonu](takip-sablonu.md) ve [temsili günlük takip](ornekler/2026-10-21/takip.md): gönderim ve sonuçları kullanıcı elle işler.
- [Tamamen kurgusal örnek gün](ornekler/2026-10-21/gunluk-rapor.md)
- [Mevcut sektör notları](sektorler/teklif-sektor-raporlari.md)
- [Modül kataloğu — Hızlı seçim](module-catalog.md#hızlı-seçim): marka analisti için yalnız modül, iş amacı ve başlıca sınır; teknik ayrıntılar uygulama aşamasında okunur.

Mevcut sektör notları fikir girdisidir; içlerindeki istatistikler, satış beklentileri ve genellemeler bu çalışmada doğrulanmadı. Firma raporuna veya teklife doğrulanmadan taşınmaz.

Gerçek kullanımda gün klasörü `customers/YYYY-MM-DD/`, firma klasörü bunun altında `<firma-kimligi>/` olur. `ornekler/` altındaki kayıtlar gerçek aday listesine veya günlük kotaya alınmaz. Örnek firma klasöründeki dosyalar yeni firmaya kopyalanıp tüm temsili içerik temizlenerek şablon olarak kullanılabilir.


