@extends('frontend.themes.general.layouts.app', $layout)

@section('content')
    @include('frontend.themes.general.sections.hero', $hero)
    @include('frontend.themes.general.sections.services.index', $services)
    @include('frontend.themes.general.sections.about', $about)
    @include('frontend.themes.general.sections.cta', $cta)
@endsection
