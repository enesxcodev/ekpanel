# Sektörler (Sector) — dil uyarlama planı

Durum: uygulama ve doğrulama tamamlandı (30.10.2026).

## İnceleme ve kararlar

- Sector ana kaydı `Modules\Sector\Models\Sector`, tablo `sectors`; temel migration'larda metin alanları `title`, `short_description`, `content`, `meta_title`, `meta_keywords`, `meta_description`; çevrilmeyen alanlar `image`, `image_media_id`, `is_active`, `sort_order`, ana kimlik ve tarih damgalarıdır.
- `slug` mevcut tabloda global unique ve şu an yönetim formunda düzenleniyor. Dil başına slug olacağından `slug` translation'a taşınmalı ve eski unique indeks kaldırılmalı; yeni tabloda `(lang_id, slug)` unique olmalı. `sort_order` ana kayıtta kalır.
- `extra_fields` Sector form tanımlarına bağlıdır ve içerik metnidir; translation'a taşınmalıdır. Dosya extra field değerleri için path `sector/{sectorId}/translations/{langId}/features` ve medya kullanım sahibi SectorTranslation olmalı. Görsel `image_media_id` ve `image` ana kayıtta kalır.
- Dil modeli `App\Models\Settings\Language`, alanları `code`, `is_active`; uygulama locale'i bu ortamda `en` olabileceğinden birincil içerik dili açıkça `tr` olmalı, mevcut Türkçe Language ID korunarak yoksa eklenip aktif edilmeli.
- Blog yalnız çeviri tablo/model ve aktif dil seçimi ile admin dil panelinin yerleşim örüntüsüdür. Blog kategori, yayın ve SEO akışı birebir aktarılmamalı; Sector'un kendi SEO alanları çevrilebilir içeriktir.
- Sector şu an yalnız yönetim route'larına sahiptir. Slug'ı public route'a veya yeni public controller'a bağlama; `SectorFrontendService` mevcut imzalarını koruyup isteğe bağlı dil kodu kabul edebilir.
- Mevcut Sector dosyaları başlangıçta temiz; çalışma ağacındaki başka modül ve ortak altyapı değişiklikleri korunmalı, bu görev yalnız Sector kapsamındadır.

## Dosya odaklı uygulama adımları

1. `Modules/Sector/database/migrations/<timestamp>_create_sector_translations_table.php` ekle. `sector_translations`: id, `sector_id` cascade FK, `lang_id` restrict FK, `title`, nullable `slug`, `short_description`, `content`, `meta_title`, `meta_keywords`, `meta_description`, JSON `extra_fields`, timestamps; unique `(sector_id, lang_id)` ve `(lang_id, slug)` (slug nullable). Önce `tr` kaydını ID'sini koruyarak upsert et/aktif et; `sectors` kayıtlarını ID sırasıyla chunk edip eski alanları Türkçe çeviriye insertOrIgnore ile taşı. Var olan çeviriyi ezme. Kaynak-hedef sayı ve eksik eşleşme kontrolünden sonra eski çevrilebilir sütunları ve `slug` unique indeksini kaldır. Migration down veri kaybettirecek şekilde otomatik geri alınmasın; açıkça engellensin.
2. `SectorTranslation` modeli (`language()`, `sector()` ilişkileri; `extra_fields` array cast) ve `Sector` ilişkileri/selectedTranslation erişimini ekle. Ana modelden taşınan alanları fillable/kolon varsayımından çıkar; image ilişkisi, imageUrl, ortak alanlar korunmalı. Factory varsayılan Türkçe translation üretecek şekilde güncellenmeli.
3. `SectorLanguageService`: aktif dilleri (TR önce) ver, admin `?lang=<code>` yalnız aktif dili kabul etsin, seçim yoksa TR/ilk aktif dili seçsin ve `sector_language` request bağlamına koysun. Frontend explicit/request dilini yalnız aktif Language ile çözsün; geçersiz/pasif dilde boş/null dönsün. Global locale değiştirme.
4. Controller, request ve `SectorService`: `lang_id` aktif Language üzerinden doğrulansın. Yeni Sector sadece TR'de açılabilsin. Her kaydetmede ortak `image`, `sort_order`, `is_active` sadece TR formunda yazılsın; diğer dillerde formda gösterilip kilitlensin ve ana değerler ezilmesin. Seçilen dil yalnız kendi translation alanlarını güncellesin; eksik translation boş açılsın. `content` sanitizasyonu, `ContentMediaService`, FeatureValues/FieldFiles kullanımı ve transaction korunsun. Extra field dosyaları/media usage translation sahibi ve dil bazlı path kullansın; silmede her translation'ın dosya ve medya kullanımları temizlensin. Bildirim route'ları ve redirect'ler `lang` değerini korusun.
5. Formu Blog'un `col-lg-8/4` dil paneli örüntüsüyle Sector alanlarına uyarla. Yalnız aktif diller, çeviri var/yok durumu ve gizli `lang_id`; dil değişim linkleri kaydın edit ID'sini ve seçili dili korusun. Başlık, slug, özet, içerik, SEO ve extra fields translation'dan gelir. Ortak görsel/sıra/aktiflik TR'de düzenlenir, başka dillerde salt okunur. Liste/DataTable seçili dil metinlerini gösterip o dilde çevirisi bulunmayan kayıtları göstermemeli; slug üzerinden sıralama ve arama translation sorgusunu kullanmalı, ortak sütunlar Sector kaydında kalmalı.
6. `SectorFrontendService` mevcut `getAll()`, `getFirst()`, `find(int $id)` çağrılarını geriye uyumlu tutup opsiyonel `?string $languageCode = null` alabilir. Yalnız aktif Sector ve seçilen aktif dil translation'ı olan kayıtları döndür; `selectedTranslation` ve `imageMedia` yükle, mevcut `sort_order`, `id` sıralamasını koru. Modül, `sectors`, `languages` veya `sector_translations` tablosu yoksa koleksiyon/null davranışı sürsün. Tema rule/skill gerçek imzalarla güncellensin; public route varmış gibi belge yazılmasın.
7. `Modules/Sector/.ai/rules/tema-entegrasyonu.md` ve `Modules/Sector/skills/sector-theme-integration/SKILL.md`: çeviri alanlarının SectorTranslation'dan; görsel, sıra ve aktifliğin Sector'den okunduğunu; seçili dil çevirisi yoksa Sector'un saklı olduğunu; frontend servis parametreleri ve boş dönüş sözleşmesini belirt.

## Doğrulama kapsamı (uygulama koordinasyonunda)

- Migration: eski Sector kayıtları ve medya/ortak alan kimlikleri korunur; Türkçe backfill eşleşir; var olan translation ezilmez; başka dillerin verisi silinmez. Migration uygulama veritabanında yalnız bu modül migration'ı ile çalıştırılmalı.
- Admin: iki aktif dilde başlık/slug/içerik/SEO/ek alan ayrışır; dil değişince doğru form yüklenir; eksik çeviri boş açılır; pasif/olmayan dil reddedilir; ortak image/sort_order/is_active çevrilen formdan değişmez; slug aynı dilde benzersiz, diller arasında aynı olabilir; dosya/media usage sahipliği doğru translation'a bağlanır.
- Frontend: aktif ve seçili dil translation'ı olan kayıtlar görünür; olmayan çeviri, pasif dil, devre dışı modül ve eksik tablo boş/null verir; medya ilişkisi ve sıralama korunur.
- Sırf tarayıcı doğrulaması için kayıt gerekiyorsa `QA` adlandırıp doğrulamadan sonra sil; mevcut kullanıcı verisi silinmemeli.

## Açık not

Sector slug'ının public adresi mevcut değil. Bu uyarlama slug'ı çeviri alanı yapar ve admin/DataTable davranışını değiştirir; yeni public detay route'u eklemez.
