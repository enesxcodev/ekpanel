@if($faqs->isNotEmpty())
    <section id="sss" class="section-space !pt-6">
        <div class="site-container grid gap-8 lg:grid-cols-2 lg:gap-20">
            <div>
                <p class="eyebrow">{{ $copy['faqs_eyebrow'] }}</p>
                <h2 class="section-title mt-2">{{ $copy['faqs_title'] }}</h2>
                <p class="mt-4 max-w-md text-sm leading-7 text-slate-500">{{ $copy['faqs_description'] }}</p>
                @if($copy['faqs_action_url'] && $copy['faqs_action_label'])<a href="{{ $copy['faqs_action_url'] }}" class="button-secondary mt-5">{{ $copy['faqs_action_label'] }}</a>@endif
            </div>
            <div class="flex flex-col gap-2">
                @foreach($faqs as $faq)
                    <details class="faq-item group rounded-lg border border-slate-200/80" name="homepage-faq" @if($loop->first) open @endif>
                        <summary class="flex cursor-pointer items-center justify-between gap-4 px-5 py-4 text-sm font-bold"><span>{{ $faq['question'] }}</span><span class="text-lg font-normal text-indigo-600 group-open:hidden" aria-hidden="true">+</span><span class="hidden text-lg font-normal text-indigo-600 group-open:inline" aria-hidden="true">−</span></summary>
                        <p class="px-5 pb-5 text-sm leading-6 text-slate-500">{{ $faq['answer'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>
@endif
