@csrf
@isset($menu) @method('PUT') @endisset
<x-admin.form.input name="name" label="Menü adı" :value="$menu->name ?? null" required maxlength="255" />
<x-admin.form.input name="key" label="Menü anahtarı" :value="$menu->key ?? null" required maxlength="100" pattern="[a-z][a-z0-9_-]*" :help="'Temada menüyü çağırmak için kullanılan benzersiz anahtar. Küçük harfle başlayın; küçük harf, rakam, alt çizgi ve tire kullanın. Ana menü için yapılandırılan anahtar: '.config('menus.main_menu', 'main').'.'" />
<x-admin.form.select name="is_active" label="Durum" :value="(int) ($menu->is_active ?? true)" :options="[['value' => 1, 'label' => 'Var'], ['value' => 0, 'label' => 'Yok']]" :placeholder="false" required />
<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($menu) ? 'Değişiklikleri kaydet' : 'Menü ekle' }}</button>
    <a href="{{ route('admin.menus.index') }}" class="btn btn-link">Vazgeç</a>
</div>
