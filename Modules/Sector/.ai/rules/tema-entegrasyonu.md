# Sector tema entegrasyonu

- Firmanın çalıştığı iş alanlarını anlatan “Hizmet verdiğimiz sektörler”, “Çalıştığımız alanlar” veya “Uzmanlık alanlarımız” bölümleri Sector adayıdır. Sektör adı, görsel ve açıklama içeren kartlar ya da listeler yapısal ipuçlarıdır; başlığa bakarak tek başına eşleştirme yapma.
- Sektör, firmanın hangi alanda çalıştığını anlatır; hizmet firmanın ne yaptığını, marka temsil ettiği veya portföyünde sunduğu markaları, referans ise müşteri ya da tamamlanmış projeyi anlatır. Bu içerik türlerini birbirinin yerine kullanma.
- Çevrilen alanlar `SectorTranslation` üzerindedir: `title`, `slug`, `short_description`, `content`, `meta_title`, `meta_keywords`, `meta_description`, `extra_fields`. Ortak `Sector` alanları `image`/`image_media_id`, `imageMedia`, `sort_order`, `is_active` ve kimlik alanlarıdır. Public detay route'u tanımlı değildir; slug'ı route varmış gibi kullanma.
- `content` HugeRTE ile düzenlenir ve ortak HTML temizleyicisinden geçirilir. Ek alanları `features.json` tanımına göre oku.
- Veriyi mevcut `SectorFrontendService` üzerinden al. Dil kodu belirtilmezse geçerli request dil bağlamı veya Türkçe seçilir. Seçilen dilde çevirisi olmayan sektör döndürülmez; başka dile fallback yapılmaz. Metinleri `selectedTranslation` üzerinden oku, ortak alanları Sector modelinden al.
- Güncel metotlar, imzalar, dönüş türleri ve yayın/sıralama davranışı [modülün tema skill'inde](../../skills/sector-theme-integration/SKILL.md#frontend-okuma-servisi) tanımlıdır; önce bu sözleşmeyi oku.
