# Slider tema entegrasyonu

- Ana tanıtım alanındaki hero, banner, carousel veya tek büyük görsel Slider adayıdır. Büyük görsel üzerinde başlık, destek metni, çağrı butonu ve slayt gezinme işaretleri görsel/yapısal ipuçlarıdır; bunlar kesin eşleştirme değildir. Sayfa ortasındaki her görsel bandı otomatik Slider sayma; hizmet, ekip veya başka içerik bölümünün anlamını ve mevcut modülleri birlikte değerlendir.
- `title`, `subtitle` ve `extra_fields` seçili dilin `slider_translations` kaydında tutulur. `image`, `image_media_id` ve `is_active` ana Slider kaydında dil bağımsızdır. `features.json` içindeki `slider` alanları da `extra_fields` ile birlikte çevrilir.
- Aktif dile ait çevirisi bulunmayan Slider frontend sonuçlarında gösterilmez; otomatik Türkçe fallback yapılmaz. Tema metinlerini servis tarafından seçilmiş çeviriden oku. Ana görsel `imageMedia` ilişkisi global kayıttan gelir.
- `is_active` ile aktif kayıtları filtrele. Sıralama alanı yoktur; `getFirst()` için kayıt önceliği garantisi yoktur. Tek görselde hangi kaydın kullanılacağı ya da carousel sırası ayrıca tanımlanmamışsa kullanıcıya sor; rastgele ilk/son kayıt seçme.
- Modül/alan eşleşmesi belirsizken entegrasyona devam etme. Veri erişimini mevcut model/servis örüntüsüyle yap; ek facade/helper oluşturma zorunluluğu yoktur.
- Uygulama adımları için [Slider skill'ini](../../skills/slider-theme-integration/SKILL.md), ortak alan genişletme için [modül kurallarını](../../../../.ai/rules/moduller.md#tema-için-yönetilebilir-alan-genişletme) oku.
