@csrf
@isset($item) @method('PUT') @endisset
@php
    $selectedSource = old('source', $item->source ?? '');
    $selectedSource = is_scalar($selectedSource) ? (string) $selectedSource : '';
    $selectedSourceId = old('source_id', $item->source_id ?? '');
    $selectedSourceId = is_scalar($selectedSourceId) ? (string) $selectedSourceId : '';
    $routeParameters = isset($item) && $item->route_parameters
        ? json_encode($item->route_parameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        : null;
@endphp
<x-admin.form.input name="label" label="Görünen metin" :value="$item->label ?? null" required maxlength="255" data-menu-label />
<div class="row">
    <div class="col-md-8">
        <x-admin.form.select name="parent_id" label="Üst öğe" :options="$parentOptions" :value="$item->parent_id ?? null" placeholder="Üst öğe yok (ana seviye)" help="Alt menü oluşturmak için bir üst öğe seçin." />
    </div>
    <div class="col-md-4">
        <x-admin.form.input name="sort_order" label="Aynı seviyedeki sıralama" type="number" :value="$item->sort_order ?? 0" min="0" max="65535" required />
    </div>
</div>
<x-admin.form.select
    name="target_type" label="Hedef türü" :value="$item->target_type ?? 'group'" :placeholder="false" required data-menu-target
    :options="[
        ['value' => 'group', 'label' => 'Grup başlığı (bağlantısız)'],
        ['value' => 'url', 'label' => 'URL'],
        ['value' => 'route', 'label' => 'Site rotası'],
        ['value' => 'anchor', 'label' => 'Sayfa içi bağlantı'],
        ['value' => 'content', 'label' => 'Modül içeriği'],
    ]"
/>
<div data-menu-fields="url anchor">
    <x-admin.form.input name="url" label="Bağlantı adresi" :value="$item->url ?? null" maxlength="2048" placeholder="https://ornek.com veya /iletisim" help="Site içindeki / ile başlayan bir yol veya http/https adresi girin. Sayfa içi bağlantıda boş bırakırsanız mevcut sayfa kullanılır; adres girerseniz hedef bölüm bu adrese eklenir." data-menu-required="url" />
</div>
<div data-menu-fields="route">
    <x-admin.form.input name="route_name" label="Site rotası" :value="$item->route_name ?? null" maxlength="255" list="menu-route-options" help="Bir rota adı girin veya ziyaretçilere açık GET rotası önerilerinden seçin. Özel rota adını doğrudan yazabilirsiniz." data-menu-required />
    <datalist id="menu-route-options">
        @foreach ($routeOptions as $routeOption)
            <option value="{{ $routeOption['value'] }}">{{ $routeOption['label'] }}</option>
        @endforeach
    </datalist>
    <x-admin.form.textarea name="route_parameters_json" label="Rota parametreleri (JSON)" :value="$routeParameters" rows="3" placeholder='{"slug": "hakkimizda"}' help="Rota parametre istemiyorsa boş bırakın. Gerekiyorsa parametre adları ve değerlerinden oluşan bir JSON nesnesi girin." />
</div>
<div data-menu-fields="anchor">
    <x-admin.form.input name="anchor" label="Sayfa içi hedef" :value="$item->anchor ?? null" maxlength="255" pattern="[A-Za-z][A-Za-z0-9_:.-]*" placeholder="iletisim" help="# işareti olmadan hedef bölümün HTML id değerini girin. Bölümün sayfada bulunduğu otomatik doğrulanmaz." data-menu-required />
</div>
<div data-menu-fields="content">
    <x-admin.form.field name="source" label="İçerik kaynağı" id="menu-source" :required="true">
        <select id="menu-source" name="source" class="form-select @error('source') is-invalid @enderror" data-menu-source data-menu-required>
            <option value="">Kaynak seçin</option>
            @if ($selectedSource !== '' && !array_key_exists($selectedSource, $sourceOptions))
                <option value="{{ $selectedSource }}" selected>{{ $selectedSource }} (kaynak kullanılamıyor)</option>
            @endif
            @foreach ($sourceOptions as $sourceKey => $sourceOption)
                <option value="{{ $sourceKey }}" data-supports-children="{{ $sourceOption['supports_children'] ? '1' : '0' }}" @selected($selectedSource === (string) $sourceKey)>{{ $sourceOption['label'] }}</option>
            @endforeach
        </select>
    </x-admin.form.field>
    <x-admin.form.field name="source_id" label="İçerik" id="menu-source-id" :required="true" help="Seçtiğiniz kaynağa ait bir kayıt seçin. Ziyaretçi bağlantısı olmayan içerikler yayınlanan menüde bağlantıya dönüşmez.">
        <select id="menu-source-id" name="source_id" class="form-select @error('source_id') is-invalid @enderror" data-menu-source-id data-menu-required>
            <option value="">İçerik seçin</option>
            @foreach ($sourceOptions as $sourceKey => $sourceOption)
                <optgroup label="{{ $sourceOption['label'] }}" data-menu-source-group="{{ $sourceKey }}">
                    @php($sourceItemExists = false)
                    @foreach ($sourceOption['options'] as $contentOption)
                        @php($isSelectedContent = $selectedSource === (string) $sourceKey && $selectedSourceId === (string) $contentOption['value'])
                        @php($sourceItemExists = $sourceItemExists || $isSelectedContent)
                        <option value="{{ $contentOption['value'] }}" @selected($isSelectedContent)>{{ $contentOption['label'] }}</option>
                    @endforeach
                    @if ($selectedSource === (string) $sourceKey && $selectedSourceId !== '' && !$sourceItemExists)
                        <option value="{{ $selectedSourceId }}" selected>{{ $selectedSourceId }} (kayıt kullanılamıyor)</option>
                    @endif
                </optgroup>
            @endforeach
            @if ($selectedSource !== '' && !array_key_exists($selectedSource, $sourceOptions) && $selectedSourceId !== '')
                <optgroup label="Kullanılamayan kaynak" data-menu-source-group="{{ $selectedSource }}">
                    <option value="{{ $selectedSourceId }}" selected>{{ $selectedSourceId }} (kaynak kullanılamıyor)</option>
                </optgroup>
            @endif
        </select>
    </x-admin.form.field>
    <p class="text-secondary small" data-menu-source-status aria-live="polite">Kaynak veya kayıt kullanılamıyorsa farklı bir içerik seçin. Bağlantı adresi bulunmayan içerikler için URL hedefi kullanabilirsiniz.</p>
    <div data-menu-dynamic-fields>
        <x-admin.form.boolean name="dynamic_children" label="Kategorideki yayımlanmış sayfaları otomatik ekle" :value="$item->dynamic_children ?? false" data-menu-dynamic help="Destekleyen sayfa kategorisi kaynaklarında kullanılabilir. Seçilirse doğrudan bu kategoriye bağlı yayımlanmış sayfalar menüye eklenir." />
    </div>
</div>
<x-admin.form.select name="is_active" label="Durum" :value="(int) ($item->is_active ?? true)" :options="[['value' => 1, 'label' => 'Var'], ['value' => 0, 'label' => 'Yok']]" :placeholder="false" required />
<x-admin.form.boolean name="new_tab" label="Bağlantıyı yeni sekmede aç" :value="$item->new_tab ?? false" />
<div class="d-flex flex-wrap gap-2 mt-3">
    <button type="submit" class="btn btn-primary">{{ isset($item) ? 'Değişiklikleri kaydet' : 'Öğe ekle' }}</button>
    <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-outline-secondary">Menüye dön</a>
    <a href="{{ route('admin.menus.index') }}" class="btn btn-link">Vazgeç</a>
</div>
