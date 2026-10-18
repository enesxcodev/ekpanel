@if(!$unstyled) @once('site-form-styles') @vite('resources/css/site-forms.css') @endonce @endif
@php($formErrors = $errors->getBag('form_'.$form->id))
<form method="POST" action="{{ route('forms.submit',$form->key) }}" enctype="multipart/form-data" {{ $attributes->class(['ek-form', $form->css_class]) }} data-form="{{ $form->key }}">
    @csrf
    <input type="hidden" name="_form_id" value="{{ $form->id }}">
    @if(session('form_success.'.$form->id))<div class="ek-form__success" role="status">{{ session('form_success.'.$form->id) }}</div>@endif
    @if($formErrors->any())<div class="ek-form__errors" role="alert"><ul>@foreach($formErrors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif
    <div class="ek-form__trap" hidden aria-hidden="true"><label>Website<input name="website" type="text" tabindex="-1" autocomplete="off"></label></div>
    @include($themeView, ['form'=>$form])
    <button class="ek-form__submit" type="submit">{{ $form->submit_label }}</button>
</form>
