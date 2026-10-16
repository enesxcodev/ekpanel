<x-admin.layout :title="$definition->exists ? 'Ayar alanını düzenle' : 'Ayar alanı ekle'">
    @if ($errors->any())
        <div class="alert alert-danger" role="alert"><ul class="mb-0">@foreach ($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>
    @endif
    @if ($sections->isEmpty())
        <div class="alert alert-info">Alan eklemeden önce bir bölüm oluşturun.</div>
        <a href="{{ route('admin.settings.configuration.index') }}" class="btn btn-primary">Bölümlere dön</a>
    @else
        @php
            $configuredOptions = old('options', $definition->options ?: [['label' => '', 'value' => '']]);
            $configuredOptions = is_array($configuredOptions) ? array_filter($configuredOptions, 'is_array') : [];
            $configuredExtensions = old('validation.extensions', $definition->validation['extensions'] ?? ['jpg', 'png', 'webp']);
            $configuredExtensions = is_array($configuredExtensions) ? $configuredExtensions : [];
        @endphp
        <form method="POST" action="{{ $definition->exists ? route('admin.settings.definitions.update', $definition) : route('admin.settings.definitions.store') }}" data-setting-definition>
            @csrf
            @if ($definition->exists) @method('PUT') @endif
            <div class="card">
                <div class="card-body">
                    @if ($definition->exists)<div class="alert alert-info">Anahtar, input türü ve veri türü sabittir. Diğer özellikleri değiştirebilirsiniz.</div>@endif
                    <div class="row">
                        <div class="col-md-6">
                            <x-admin.form.select name="section_id" label="Bölüm" :options="$sections->map(fn ($section) => ['label' => $section->label, 'value' => $section->id])->all()" :value="$definition->section_id ?? request('section')" required />
                        </div>
                        <div class="col-md-6"><x-admin.form.input name="key" label="Anahtar" :value="$definition->key" placeholder="contact.whatsapp" help="Benzersiz ve sabit anahtar. Örnek: contact.whatsapp" required maxlength="150" :readonly="$definition->exists" /></div>
                        <div class="col-md-8"><x-admin.form.input name="label" label="Etiket" :value="$definition->label" required maxlength="150" /></div>
                        <div class="col-md-4"><x-admin.form.input name="sort_order" label="Sıralama" type="number" :value="$definition->sort_order ?? 0" min="0" max="100000" required /></div>
                        <div class="col-md-6">
                            <x-admin.form.select name="input_type" label="Input türü" :options="array_map(fn ($type) => ['label' => $type->label(), 'value' => $type->value], $inputTypes)" :value="$definition->input_type?->value ?? 'text'" :placeholder="false" required data-setting-type :disabled="$definition->exists" />
                            @if ($definition->exists)<input type="hidden" name="input_type" value="{{ $definition->input_type->value }}">@endif
                        </div>
                        <div class="col-md-6">
                            <x-admin.form.select name="value_type" label="Veri türü" :options="[['label' => 'Metin', 'value' => 'string'], ['label' => 'Tam sayı', 'value' => 'integer'], ['label' => 'Ondalık sayı', 'value' => 'float'], ['label' => 'Açık / Kapalı', 'value' => 'boolean'], ['label' => 'Dosya referansı', 'value' => 'file']]" :value="$definition->value_type?->value ?? 'string'" :placeholder="false" required data-setting-value-type :disabled="$definition->exists" />
                            @if ($definition->exists)<input type="hidden" name="value_type" value="{{ $definition->value_type->value }}">@endif
                        </div>
                    </div>
                    <x-admin.form.textarea name="description" label="Yardım metni" :value="$definition->description" rows="2" maxlength="1000" />
                    <x-admin.form.boolean name="validation[required]" error-key="validation.required" label="Zorunlu alan" :value="$definition->validation['required'] ?? false" />
                    <div data-for-types="text textarea email number range boolean select radio">
                        <x-admin.form.input name="default_value" label="Varsayılan değer" :value="$definition->default_value" help="İsteğe bağlı. Açık / kapalı için 1 veya 0; seçim alanlarında seçenek değerini girin." />
                    </div>
                    <div class="row" data-for-types="text textarea email password">
                        <div class="col-md-12"><x-admin.form.input name="attributes[placeholder]" error-key="attributes.placeholder" label="Placeholder" :value="$definition->attributes['placeholder'] ?? null" maxlength="255" /></div>
                    </div>
                    <div data-for-types="textarea">
                        <x-admin.form.input name="attributes[rows]" error-key="attributes.rows" label="Satır sayısı" type="number" :value="$definition->attributes['rows'] ?? 4" min="2" max="20" />
                    </div>
                    <div class="row" data-for-types="text textarea email password number range">
                        <div class="col-md-6"><x-admin.form.input name="validation[min]" error-key="validation.min" label="Minimum" type="number" step="any" :value="$definition->validation['min'] ?? null" help="Metinlerde karakter sayısı; sayılarda alt sınır." /></div>
                        <div class="col-md-6"><x-admin.form.input name="validation[max]" error-key="validation.max" label="Maksimum" type="number" step="any" :value="$definition->validation['max'] ?? null" help="Metinlerde karakter sayısı; sayılarda üst sınır." /></div>
                    </div>
                    <div data-for-types="number range">
                        <x-admin.form.input name="attributes[step]" error-key="attributes.step" label="Adım" type="number" :value="$definition->attributes['step'] ?? 1" min="0.000001" step="any" />
                    </div>
                    <div data-for-types="radio">
                        <div class="row">
                            <div class="col-md-6"><x-admin.form.select name="attributes[layout]" error-key="attributes.layout" label="Yerleşim" :options="[['label' => 'Dikey', 'value' => 'vertical'], ['label' => 'Yatay', 'value' => 'horizontal']]" :value="$definition->attributes['layout'] ?? 'vertical'" :placeholder="false" /></div>
                            <div class="col-md-6"><x-admin.form.select name="attributes[variant]" error-key="attributes.variant" label="Görünüm" :options="[['label' => 'Standart', 'value' => 'standard'], ['label' => 'Buton grubu', 'value' => 'buttons']]" :value="$definition->attributes['variant'] ?? 'standard'" :placeholder="false" /></div>
                        </div>
                    </div>
                    <div data-for-types="select radio" class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2"><h3 class="mb-0">Seçenekler</h3><button class="btn btn-sm btn-outline-primary" type="button" data-add-option>+ Seçenek ekle</button></div>
                        <div data-setting-options>
                            @foreach ($configuredOptions as $index => $option)
                                <div class="row align-items-start g-2" data-setting-option>
                                    <div class="col"><x-admin.form.input :name="'options['.$index.'][label]'" :error-key="'options.'.$index.'.label'" label="Etiket" :value="$option['label'] ?? ''" required maxlength="150" /></div>
                                    <div class="col"><x-admin.form.input :name="'options['.$index.'][value]'" :error-key="'options.'.$index.'.value'" label="Değer" :value="$option['value'] ?? ''" required maxlength="150" /></div>
                                    <div class="col-auto pt-4"><button class="btn btn-outline-danger" type="button" data-remove-option aria-label="Seçeneği kaldır">Kaldır</button></div>
                                </div>
                            @endforeach
                        </div>
                        <template data-option-template>
                            <div class="row align-items-start g-2" data-setting-option>
                                <div class="col"><x-admin.form.input name="options[__INDEX__][label]" id="option-label-__INDEX__" label="Etiket" required maxlength="150" /></div>
                                <div class="col"><x-admin.form.input name="options[__INDEX__][value]" id="option-value-__INDEX__" label="Değer" required maxlength="150" /></div>
                                <div class="col-auto pt-4"><button class="btn btn-outline-danger" type="button" data-remove-option aria-label="Seçeneği kaldır">Kaldır</button></div>
                            </div>
                        </template>
                        <div class="form-hint">Etiket kullanıcıya gösterilir, değer kaydedilir. Değerler benzersiz olmalıdır.</div>
                    </div>
                    <div data-for-types="file image">
                        <x-admin.form.input name="validation[max_size]" error-key="validation.max_size" label="Maksimum dosya boyutu (KB)" type="number" :value="$definition->validation['max_size'] ?? 2048" min="1" max="10240" />
                        <fieldset class="mb-3">
                            <legend class="form-label">İzin verilen dosya türleri</legend>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach (['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'txt', 'csv', 'docx', 'xlsx', 'zip'] as $extension)
                                    <label class="form-check" data-for-types="{{ in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? 'file image' : 'file' }}">
                                        <input class="form-check-input" type="checkbox" name="validation[extensions][]" value="{{ $extension }}" @checked(in_array($extension, $configuredExtensions, true))>
                                        <span class="form-check-label">{{ $extension }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>
                    <div class="row" data-for-types="image">
                        @foreach (['min_width' => 'Minimum genişlik', 'max_width' => 'Maksimum genişlik', 'min_height' => 'Minimum yükseklik', 'max_height' => 'Maksimum yükseklik'] as $bound => $label)
                            <div class="col-md-3"><x-admin.form.input :name="'validation['.$bound.']'" :error-key="'validation.'.$bound" :label="$label.' (px)'" type="number" :value="$definition->validation[$bound] ?? null" min="1" max="10000" /></div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer d-flex gap-2">
                    <button class="btn btn-primary" type="submit">{{ $definition->exists ? 'Değişiklikleri kaydet' : 'Alanı oluştur' }}</button>
                    <a class="btn btn-link" href="{{ route('admin.settings.configuration.index') }}">Vazgeç</a>
                </div>
            </div>
        </form>
    @endif
</x-admin.layout>
