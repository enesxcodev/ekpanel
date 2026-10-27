<?php

use App\Models\Forms\FormField;
use App\Models\Forms\SiteForm;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Modules\Careers\Models\JobPosting;
use Modules\Events\Models\Event;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;
use Modules\Products\Models\Product;
use Modules\Services\Models\Service;
use Modules\Slider\Models\Slider;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('home page hides empty sections and never invents module or contact content', function () {
    $this->get(route('home'))->assertOk()
        ->assertDontSee('id="hizmetler"', false)
        ->assertDontSee('id="urunler"', false)
        ->assertDontSee('id="kurumsal"', false)
        ->assertDontSee('id="sss"', false)
        ->assertDontSee('id="iletisim"', false)
        ->assertDontSee('info@ekagent.com')
        ->assertDontSee('+90 212 123 45 67');

    $this->assertDatabaseCount('services', 0);
    $this->assertDatabaseCount('sliders', 0);
    $this->assertDatabaseCount('products', 0);
    $this->assertDatabaseCount('site_forms', 0);
});

test('home page uses published service data in service order and escapes its titles', function () {
    Service::factory()->create(['title' => 'Son hizmet', 'sort_order' => 20]);
    Service::factory()->create(['title' => '<script>alert(1)</script>', 'sort_order' => 10]);
    Service::factory()->create(['title' => 'Gizli hizmet', 'is_active' => false]);

    $this->get(route('home'))->assertOk()
        ->assertSeeInOrder(['&lt;script&gt;alert(1)&lt;/script&gt;', 'Son hizmet'], false)
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('Gizli hizmet')
        ->assertDontSee('İş süreçlerinizi daha verimli hale getiren');
});

test('home page remains usable without installed or enabled content modules', function (bool $installed) {
    $disk = Storage::fake('homepage-modules');
    if ($installed) {
        $disk->put('Services/module.json', json_encode(['name' => 'Services', 'providers' => []], JSON_THROW_ON_ERROR));
    }
    $disk->put('statuses.json', json_encode(['Services' => false], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));

    $this->get(route('home'))->assertOk()
        ->assertDontSee('id="iletisim"', false)
        ->assertDontSee('İhtiyacınıza uygun çözümler')
        ->assertDontSee('Ürünlerimizi keşfedin')
        ->assertDontSee('İş ortaklarımız ne söylüyor?')
        ->assertDontSee('Yaklaşan Etkinlik');
})->with(['missing modules' => false, 'disabled module' => true]);

test('slider uses its existing fields without unsafe or unconfigured buttons', function () {
    Slider::factory()->create(['title' => 'Güvenli ana sayfa', 'is_active' => true,
        'extra_fields' => ['button_url' => 'javascript:alert(1)', 'secondary_button_url' => '//evil.example/path']]);

    $this->get(route('home'))->assertOk()->assertSee('Güvenli ana sayfa')
        ->assertDontSee('javascript:alert(1)', false)->assertDontSee('//evil.example/path', false)
        ->assertDontSee('href="#hizmetler"', false);
});

test('home page renders the active contact form and stores its submission using the form service', function () {
    $form = SiteForm::factory()->create(['key' => 'default-iletisim', 'is_active' => true, 'success_message' => 'Mesajınız alındı.']);
    FormField::factory()->for($form, 'form')->create(['key' => 'message', 'label' => 'Mesaj', 'required' => true]);

    $this->get(route('home'))->assertOk()->assertSee('data-form="default-iletisim"', false)
        ->assertSee('name="answers[message]"', false);
    $this->post(route('forms.submit', $form->key), ['answers' => ['message' => 'Bir görüşme planlamak istiyorum.']])
        ->assertRedirect(route('home').'#iletisim-formu')->assertSessionHas('form_success.'.$form->id, 'Mesajınız alındı.');
    $this->assertDatabaseHas('form_submissions', ['site_form_id' => $form->id, 'status' => 'new']);
    expect($form->submissions()->sole()->answers['message']['value'])->toBe('Bir görüşme planlamak istiyorum.');
});

test('contact form rejects missing required answers without saving a submission', function () {
    $form = SiteForm::factory()->create(['key' => 'default-iletisim', 'is_active' => true]);
    FormField::factory()->for($form, 'form')->create(['key' => 'message', 'required' => true]);

    $this->from(route('home'))->post(route('forms.submit', $form->key), ['answers' => []])
        ->assertRedirect(route('home'))->assertSessionHasErrors(['answers.message'], null, 'form_'.$form->id);
    $this->assertDatabaseCount('form_submissions', 0);
});

test('contact form rejects inactive forms and ignores honeypot submissions', function () {
    $inactive = SiteForm::factory()->create();
    $this->post(route('forms.submit', $inactive->key), ['answers' => []])->assertNotFound();
    $active = SiteForm::factory()->create(['is_active' => true]);
    FormField::factory()->for($active, 'form')->create();
    $this->post(route('forms.submit', $active->key), ['website' => 'spam.example', 'answers' => ['message' => 'spam']])->assertRedirect(route('home'));
    $this->assertDatabaseCount('form_submissions', 0);
});

test('home page reads fixed content and safe actions from panel settings', function () {
    $section = SettingSection::factory()->create(['key' => 'default_home']);
    foreach (['about_title' => 'Panel kurumsal başlığı', 'about_paragraph_1' => 'Panel açıklaması', 'value_1_title' => 'Panel değeri', 'about_action_label' => 'Güvensiz eylem', 'about_action_url' => 'javascript:alert(1)'] as $key => $value) {
        SettingDefinition::factory()->for($section, 'section')->create(['key' => 'default_home.'.$key, 'default_value' => $value]);
    }
    app(SettingsService::class)->forgetSection($section);

    $this->get(route('home'))->assertOk()->assertSee('Panel kurumsal başlığı')->assertSee('Panel açıklaması')->assertSee('Panel değeri')
        ->assertDontSee('Güvensiz eylem')->assertDontSee('javascript:alert(1)', false);
});

test('a single panel product is rendered without filling the grid with invented products', function () {
    Product::factory()->create(['title' => 'Tek gerçek ürün']);
    Product::factory()->create(['title' => 'Pasif ürün', 'is_active' => false]);

    $this->get(route('home'))->assertOk()->assertSee('Tek gerçek ürün')->assertDontSee('Pasif ürün')
        ->assertViewHas('products', fn ($products): bool => $products->count() === 1);
});

test('panel menu keys remain separate and links to empty home sections are hidden', function () {
    config(['menus.main_menu' => 'custom_main']);
    $main = Menu::factory()->create(['key' => 'custom_main']);
    $corporate = Menu::factory()->create(['key' => 'footer_corporate']);
    $explore = Menu::factory()->create(['key' => 'footer_explore']);
    MenuItem::factory()->for($main, 'menu')->create(['label' => 'Panel ana menüsü', 'target_type' => 'anchor', 'anchor' => 'anasayfa', 'url' => null]);
    MenuItem::factory()->for($main, 'menu')->create(['label' => 'Boş ürün bölümü', 'target_type' => 'anchor', 'anchor' => 'urunler', 'url' => null]);
    MenuItem::factory()->for($corporate, 'menu')->create(['label' => 'Panel kurumsal menüsü', 'url' => '/kurum']);
    MenuItem::factory()->for($explore, 'menu')->create(['label' => 'Panel keşif menüsü', 'url' => '/kesif']);

    $this->get(route('home'))->assertOk()->assertSee('Panel ana menüsü')->assertSee('Panel kurumsal menüsü')->assertSee('Panel keşif menüsü')
        ->assertDontSee('Boş ürün bölümü');
});

test('opportunity cards use active public records and omit expired jobs', function () {
    $this->freezeTime();
    Event::factory()->create(['title' => 'Panel etkinliği']);
    Event::factory()->create(['title' => 'Pasif etkinlik', 'is_active' => false]);
    JobPosting::factory()->create(['title' => 'Süresi geçmiş ilan', 'application_deadline' => today()->subDay()]);
    JobPosting::factory()->create(['title' => 'Panel açık pozisyonu', 'application_deadline' => today()->addDay()]);

    $this->get(route('home'))->assertOk()->assertSee('Panel etkinliği')->assertSee('Panel açık pozisyonu')
        ->assertDontSee('Pasif etkinlik')->assertDontSee('Süresi geçmiş ilan');
});
