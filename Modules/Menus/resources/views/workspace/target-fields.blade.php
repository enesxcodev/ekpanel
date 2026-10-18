@if (in_array($type, ['url', 'anchor'], true))
    <label class="form-label {{ $type === 'url' ? 'required' : '' }}" for="{{ $prefix }}-url">{{ $type === 'url' ? 'Bağlantı adresi' : 'Sayfa adresi (isteğe bağlı)' }}</label>
    <input id="{{ $prefix }}-url" name="url" class="form-control mb-2" maxlength="2048" placeholder="/iletisim veya https://ornek.com" @required($type === 'url')>
@endif
@if ($type === 'anchor')
    <label class="form-label required" for="{{ $prefix }}-anchor">Bölüm kimliği</label><input id="{{ $prefix }}-anchor" name="anchor" class="form-control mb-2" maxlength="255" pattern="[A-Za-z][A-Za-z0-9_:.-]*" placeholder="iletisim" required><p class="form-hint mb-3"># işareti olmadan girin. Bölümün sayfada bulunduğu otomatik doğrulanmaz.</p>
@endif
@if ($type === 'route')
    <label class="form-label required" for="{{ $prefix }}-route">Site rotası</label><input id="{{ $prefix }}-route" name="route_name" class="form-control mb-2" list="{{ $prefix }}-routes" maxlength="255" required>
    <datalist id="{{ $prefix }}-routes">@foreach ($configuration['routes'] as $route)<option value="{{ $route['value'] }}">{{ $route['label'] }}</option>@endforeach</datalist>
    <label class="form-label" for="{{ $prefix }}-params">Rota parametreleri (JSON)</label><textarea id="{{ $prefix }}-params" name="route_parameters_json" class="form-control mb-3" rows="2" maxlength="8192" placeholder='{"slug":"hakkimizda"}'></textarea>
@endif
@if ($type === 'content')
    <label class="form-label required" for="{{ $prefix }}-content" data-item-source-label>Bağlantı (İçerik)</label><select id="{{ $prefix }}-content" name="source_id" class="form-select mb-2" required></select>
    <p class="text-secondary small" data-item-source-status></p>
    <div data-item-dynamic hidden><label class="form-check"><input type="checkbox" name="dynamic_children" class="form-check-input"><span class="form-check-label">Kaynağın alt içeriklerini otomatik listele</span></label><p class="form-hint">Kaynağın desteklediği doğrudan yayımlanmış içerikler kullanılır.</p></div>
@endif
@if ($type === 'group')<p class="text-secondary small mb-0">Bağlantısız grup başlığı; alt öğeleri bir arada gösterir.</p>@endif
