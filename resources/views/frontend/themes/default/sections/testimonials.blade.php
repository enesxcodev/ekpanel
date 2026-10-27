@if($testimonials->isNotEmpty())
    <section class="section-space bg-slate-50" aria-labelledby="testimonials-heading">
        <div class="site-container">
            <div class="text-center"><p class="eyebrow">{{ $copy['testimonials_eyebrow'] }}</p><h2 id="testimonials-heading" class="section-title mt-2">{{ $copy['testimonials_title'] }}</h2></div>
            <div class="mt-7 grid gap-5 md:grid-cols-2">
                @foreach($testimonials as $testimonial)
                    <figure class="content-card !p-7">
                        <blockquote class="text-sm leading-7 text-slate-500">“{{ $testimonial['content'] }}”</blockquote>
                        <figcaption class="mt-5 flex items-center gap-3">
                            <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-sm font-extrabold text-indigo-600">{{ $testimonial['initials'] }}</span>
                            <div><p class="text-sm font-bold">{{ $testimonial['name'] }}</p><p class="mt-0.5 text-xs text-slate-500">{{ $testimonial['company'] }}</p></div>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
