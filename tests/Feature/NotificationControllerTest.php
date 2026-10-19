<?php

use App\Models\Admin\Admin;
use App\Models\User;
use App\Services\NotifyService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

uses(LazilyRefreshDatabase::class);

test('the core service persists one notification per recipient and rolls back with the business transaction', function () {
    $this->freezeSecond();
    $first = Admin::factory()->create();
    $second = Admin::factory()->create();
    $service = app(NotifyService::class);
    $service->send($first, 'Yeni başvuru', 'Başvuru alındı.', route('admin.forms.submissions.index'));
    $service->sendMany([$first, $second, $first], 'Ortak duyuru', 'Bakım planlandı.');

    expect($first->notifications()->count())->toBe(2)
        ->and($second->notifications()->count())->toBe(1);
    $item = $first->notifications()->where('data->title', 'Yeni başvuru')->firstOrFail();
    expect($item->data)->toBe(['title' => 'Yeni başvuru', 'content' => 'Başvuru alındı.', 'link' => parse_url(route('admin.forms.submissions.index'), PHP_URL_PATH)])
        ->and($item->read_at)->toBeNull()
        ->and($item->archived_at)->toBeNull()
        ->and($item->created_at->equalTo(now()))->toBeTrue();
    expect($first->notifications()->where('data->title', 'Ortak duyuru')->firstOrFail()->id)
        ->not->toBe($second->notifications()->firstOrFail()->id);

    try {
        DB::transaction(function () use ($service, $first): void {
            $service->send($first, 'Geri alınacak', 'Kaydedilmemeli.');
            throw new RuntimeException('Rollback');
        });
    } catch (RuntimeException) {
    }
    expect($first->notifications()->count())->toBe(2);
});

test('a failed recipient rolls back the whole bulk send', function () {
    $admin = Admin::factory()->create();
    expect(fn () => app(NotifyService::class)->sendMany([$admin, new Admin], 'Duyuru', 'İçerik'))
        ->toThrow(ValidationException::class);
    expect($admin->notifications()->count())->toBe(0);
});

test('the service rejects unsafe application links without persisting records', function (string $link) {
    $admin = Admin::factory()->create();
    expect(fn () => app(NotifyService::class)->send($admin, 'Duyuru', 'İçerik', $link))->toThrow(ValidationException::class);
    expect($admin->notifications()->count())->toBe(0);
})->with([
    'external' => 'https://evil.example/admin',
    'protocol relative' => '//evil.example/admin',
    'script' => 'javascript:alert(1)',
    'backslash' => '/\\evil.example',
    'encoded slash' => '/%2fevil.example',
    'double encoding' => '/%252fevil.example',
    'encoded backslash' => '/%5cevil.example',
    'traversal' => '/admin/../elsewhere',
    'control character' => "/admin\r\nLocation:https://evil.example",
]);

test('the service validates required text and size limits', function (string $title, string $content) {
    $admin = Admin::factory()->create();
    expect(fn () => app(NotifyService::class)->send($admin, $title, $content))->toThrow(ValidationException::class);
    expect($admin->notifications()->count())->toBe(0);
})->with([
    ['', 'İçerik'],
    ['Başlık', ''],
    [str_repeat('a', 256), 'İçerik'],
    ['Başlık', str_repeat('a', 10001)],
]);

test('only the active admin guard can access personal endpoints', function (string $identity) {
    $owner = Admin::factory()->create();
    app(NotifyService::class)->send($owner, 'Kişisel', 'Gizli içerik');
    $item = $owner->notifications()->firstOrFail();
    if ($identity === 'web') {
        $this->actingAs(User::factory()->create(), 'web');
    } elseif ($identity === 'inactive') {
        $this->actingAs(Admin::factory()->inactive()->create(), 'admin');
    }

    $this->get(route('admin.notifications.index'))->assertRedirectToRoute('admin.login');
    $this->get(route('admin.notifications.header'))->assertRedirectToRoute('admin.login');
    $this->get(route('admin.notifications.data'))->assertRedirectToRoute('admin.login');
    $this->get(route('admin.notifications.show', $item->id))->assertRedirectToRoute('admin.login');
    $this->post(route('admin.notifications.open', $item->id))->assertRedirectToRoute('admin.login');
    $this->patch(route('admin.notifications.read', $item->id))->assertRedirectToRoute('admin.login');
    $this->patch(route('admin.notifications.update'), ['scope' => 'all', 'action' => 'archive'])->assertRedirectToRoute('admin.login');
    expect($item->fresh()->read_at)->toBeNull()->and($item->fresh()->archived_at)->toBeNull();
})->with(['guest', 'web', 'inactive']);

test('even a super admin cannot read or mutate another recipients notification', function () {
    $owner = Admin::factory()->create();
    $other = Admin::factory()->superAdmin()->create();
    app(NotifyService::class)->send($owner, 'Gizli başlık', 'Gizli içerik');
    app(NotifyService::class)->send($other, 'Kendi bildirimi', 'İçerik');
    $private = $owner->notifications()->firstOrFail();
    $own = $other->notifications()->firstOrFail();
    $this->actingAs($other, 'admin');

    $this->get(route('admin.notifications.show', $private->id))->assertNotFound();
    $this->post(route('admin.notifications.open', $private->id))->assertNotFound();
    $this->patchJson(route('admin.notifications.read', $private->id))->assertNotFound();
    $this->patchJson(route('admin.notifications.update'), ['scope' => 'selected', 'action' => 'archive', 'ids' => [$own->id, $private->id]])->assertNotFound();
    $this->getJson(route('admin.notifications.data'))->assertOk()->assertJsonPath('recordsTotal', 1)->assertJsonPath('data.0.id', $own->id);
    $this->getJson(route('admin.notifications.header'))->assertOk()->assertJsonPath('count', 1)->assertDontSee('Gizli başlık');
    expect($private->fresh()->read_at)->toBeNull()
        ->and($private->fresh()->archived_at)->toBeNull()
        ->and($own->fresh()->archived_at)->toBeNull();
});

test('filters exclude the archive except for the archive filter which includes both read states', function () {
    $admin = Admin::factory()->create();
    $service = app(NotifyService::class);
    foreach (['Unread', 'Read', 'Archived unread', 'Archived read'] as $title) {
        $service->send($admin, $title, 'İçerik');
    }
    $items = $admin->notifications()->get()->keyBy(fn ($item) => $item->data['title']);
    $items['Read']->update(['read_at' => now()]);
    $items['Archived unread']->update(['archived_at' => now()]);
    $items['Archived read']->update(['read_at' => now(), 'archived_at' => now()]);
    $this->actingAs($admin, 'admin');

    $this->get(route('admin.notifications.index'))->assertOk()->assertSee('Yalnız bu sayfadaki');
    foreach (['all' => ['Unread', 'Read'], 'unread' => ['Unread'], 'read' => ['Read'], 'archive' => ['Archived unread', 'Archived read']] as $filter => $titles) {
        $response = $this->getJson(route('admin.notifications.data', ['filter' => $filter]))->assertOk()->assertJsonPath('recordsTotal', count($titles));
        expect(collect($response->json('data'))->pluck('data.title')->sort()->values()->all())->toBe(collect($titles)->sort()->values()->all());
    }
    expect($items['Unread']->fresh()->read_at)->toBeNull()->and($items['Archived unread']->fresh()->read_at)->toBeNull();
});

test('opening marks read with POST and redirects to the original authorized destination', function () {
    $this->freezeSecond();
    $admin = Admin::factory()->create();
    app(NotifyService::class)->send($admin, 'Başvuru', 'İçerik', route('admin.forms.submissions.index'));
    $item = $admin->notifications()->firstOrFail();
    $this->actingAs($admin, 'admin');
    $this->get(route('admin.notifications.show', $item->id))->assertOk()->assertSee('İlgili sayfaya git');
    expect($item->fresh()->read_at)->toBeNull();
    $this->get(route('admin.notifications.open', $item->id))->assertNotFound();
    $this->post(route('admin.notifications.open', $item->id))->assertRedirect(route('admin.forms.submissions.index'));
    expect($item->fresh()->read_at->equalTo(now()))->toBeTrue();
    $this->get(route('admin.forms.submissions.index'))->assertForbidden();
});

test('a direct detail page marks read through its CSRF protected PATCH endpoint', function () {
    $this->freezeTime();
    $admin = Admin::factory()->create();
    app(NotifyService::class)->send($admin, 'Başlık', 'İçerik');
    $item = $admin->notifications()->firstOrFail();
    $this->actingAs($admin, 'admin')->get(route('admin.notifications.show', $item->id))->assertOk()->assertSee('data-notification-read-url', false);
    expect($item->fresh()->read_at)->toBeNull();
    $this->patchJson(route('admin.notifications.read', $item->id))->assertOk();
    $readAt = $item->fresh()->read_at;
    $this->travel(1)->hours();
    $this->patchJson(route('admin.notifications.read', $item->id))->assertOk();
    expect($item->fresh()->read_at->equalTo($readAt))->toBeTrue();
});

test('missing routes deleted records and unsafe stored targets fall back to the readable detail', function (string $target) {
    $admin = Admin::factory()->create();
    app(NotifyService::class)->send($admin, 'Geçmiş olay', 'Korunan içerik');
    $item = $admin->notifications()->firstOrFail();
    $link = match ($target) {
        'deleted record' => route('admin.forms.submissions.show', 999999),
        'unsafe stored target' => 'https://evil.example',
        'missing module' => '/removed-module/records',
        default => null,
    };
    $item->update(['data' => ['title' => 'Geçmiş olay', 'content' => 'Korunan içerik', 'link' => $link]]);
    $this->actingAs($admin, 'admin')->post(route('admin.notifications.open', $item->id))->assertRedirectToRoute('admin.notifications.show', $item->id);
    $response = $this->get(route('admin.notifications.show', $item->id))->assertOk()->assertSee('Korunan içerik')->assertDontSee('İlgili sayfaya git');
    if ($link !== null) {
        $response->assertSee('artık mevcut değil');
    }
})->with(['no target', 'deleted record', 'missing module', 'unsafe stored target']);

test('a database failure when marking read does not block the real target', function () {
    $admin = Admin::factory()->create();
    app(NotifyService::class)->send($admin, 'Başlık', 'İçerik', route('admin.dashboard'));
    $item = $admin->notifications()->firstOrFail();
    Exceptions::fake();
    DB::unprepared("CREATE TRIGGER reject_notification_read BEFORE UPDATE OF read_at ON notifications BEGIN SELECT RAISE(ABORT, 'read failure'); END");
    $this->actingAs($admin, 'admin')->post(route('admin.notifications.open', $item->id))->assertRedirectToRoute('admin.dashboard');
    expect($item->fresh()->read_at)->toBeNull();
    Exceptions::assertReported(QueryException::class);
});

test('selected archive and restore preserve read states and never affect unselected records', function () {
    $admin = Admin::factory()->create();
    $other = Admin::factory()->create();
    $service = app(NotifyService::class);
    $service->sendMany([$admin, $other], 'Birinci', 'İçerik');
    $service->send($admin, 'İkinci', 'İçerik');
    $items = $admin->notifications()->get()->keyBy(fn ($item) => $item->data['title']);
    $this->actingAs($admin, 'admin')->patchJson(route('admin.notifications.update'), ['scope' => 'selected', 'action' => 'archive', 'ids' => [$items['Birinci']->id]])
        ->assertOk()->assertJsonPath('updated', 1);
    expect($items['Birinci']->fresh()->archived_at)->not->toBeNull()
        ->and($items['Birinci']->fresh()->read_at)->toBeNull()
        ->and($items['İkinci']->fresh()->archived_at)->toBeNull()
        ->and($other->notifications()->firstOrFail()->archived_at)->toBeNull();
    $this->patchJson(route('admin.notifications.update'), ['scope' => 'selected', 'action' => 'restore', 'ids' => [$items['Birinci']->id]])->assertOk()->assertJsonPath('updated', 1);
    expect($items['Birinci']->fresh()->archived_at)->toBeNull()->and($items['Birinci']->fresh()->read_at)->toBeNull();
    $this->patchJson(route('admin.notifications.update'), ['scope' => 'selected', 'action' => 'read', 'ids' => [$items['Birinci']->id]])->assertOk();
    expect($items['Birinci']->fresh()->read_at)->not->toBeNull()->and($items['İkinci']->fresh()->read_at)->toBeNull();
});

test('header bulk actions affect every active personal notification beyond the ten item preview', function () {
    $admin = Admin::factory()->create();
    $other = Admin::factory()->create();
    $service = app(NotifyService::class);
    foreach (range(1, 12) as $number) {
        $service->send($admin, 'Bildirim '.$number, 'İçerik');
    }
    $service->send($other, 'Diğer', 'İçerik');
    $archived = $admin->notifications()->firstOrFail();
    $archived->update(['archived_at' => now()]);
    $this->actingAs($admin, 'admin')->patchJson(route('admin.notifications.update'), ['scope' => 'all', 'action' => 'read'])->assertOk()->assertJsonPath('updated', 11);
    expect($archived->fresh()->read_at)->toBeNull()
        ->and($other->notifications()->firstOrFail()->read_at)->toBeNull();
    $this->patchJson(route('admin.notifications.update'), ['scope' => 'all', 'action' => 'archive'])->assertOk()->assertJsonPath('updated', 11);
    expect($admin->notifications()->whereNull('archived_at')->count())->toBe(0)
        ->and($other->notifications()->firstOrFail()->archived_at)->toBeNull()
        ->and($archived->fresh()->read_at)->toBeNull();
});

test('header renders the latest ten active records and the unread active count on page load and refresh', function () {
    $this->freezeTime();
    $admin = Admin::factory()->create();
    $service = app(NotifyService::class);
    foreach (range(1, 12) as $number) {
        $service->send($admin, 'Başlık-'.$number.'-son', 'İçerik');
        $this->travel(1)->seconds();
    }
    $items = $admin->notifications()->get()->keyBy(fn ($item) => $item->data['title']);
    $items['Başlık-12-son']->update(['archived_at' => now()]);
    $items['Başlık-11-son']->update(['read_at' => now()]);
    $this->actingAs($admin, 'admin');
    $this->get(route('admin.dashboard'))->assertOk()->assertSee('Başlık-11-son')->assertDontSee('Başlık-12-son')->assertDontSee('Başlık-1-son');
    $response = $this->getJson(route('admin.notifications.header'))->assertOk()->assertJsonPath('count', 10);
    $html = $response->json('html');
    expect(substr_count($html, 'data-notification-open'))->toBe(10)
        ->and(strpos($html, 'Başlık-11-son'))->toBeLessThan(strpos($html, 'Başlık-10-son'));
    $service->send($admin, 'Gerçek yeni bildirim', 'Yeni içerik');
    $refreshed = $this->getJson(route('admin.notifications.header'))->assertOk()->assertJsonPath('count', 11);
    expect($refreshed->json('html'))->toContain('Gerçek yeni bildirim');
});

test('notification content is escaped in the dropdown table and detail', function () {
    $admin = Admin::factory()->create();
    $title = '<script>alert(1)</script>';
    $content = '<img src=x onerror=alert(2)>';
    app(NotifyService::class)->send($admin, $title, $content);
    $item = $admin->notifications()->firstOrFail();
    $this->actingAs($admin, 'admin')->get(route('admin.notifications.show', $item->id))
        ->assertOk()->assertSee($title)->assertSee($content)->assertDontSee($title, false)->assertDontSee($content, false);
    $header = $this->getJson(route('admin.notifications.header'))->assertOk()->json('html');
    $message = $this->getJson(route('admin.notifications.data'))->assertOk()->json('data.0.message');
    foreach ([$header, $message] as $html) {
        expect($html)->toContain(e($title))->toContain(e($content))->not->toContain($title)->not->toContain($content);
    }
});

test('bulk requests reject invalid input and never silently expand a selection', function (array $input, string $field) {
    $admin = Admin::factory()->create();
    app(NotifyService::class)->send($admin, 'Başlık', 'İçerik');
    $item = $admin->notifications()->firstOrFail();
    $this->actingAs($admin, 'admin')->patchJson(route('admin.notifications.update'), $input)->assertUnprocessable()->assertJsonValidationErrors($field);
    expect($item->fresh()->read_at)->toBeNull()->and($item->fresh()->archived_at)->toBeNull();
})->with([
    'no selection' => [['scope' => 'selected', 'action' => 'read'], 'ids'],
    'invalid action' => [['scope' => 'all', 'action' => 'delete'], 'action'],
    'invalid scope' => [['scope' => 'everyone', 'action' => 'archive'], 'scope'],
    'invalid uuid' => [['scope' => 'selected', 'action' => 'read', 'ids' => ['bad']], 'ids.0'],
    'too many' => [['scope' => 'selected', 'action' => 'archive', 'ids' => array_map(fn () => (string) Str::uuid(), range(1, 101))], 'ids'],
    'duplicate ids' => [['scope' => 'selected', 'action' => 'read', 'ids' => ['00000000-0000-4000-8000-000000000001', '00000000-0000-4000-8000-000000000001']], 'ids.0'],
    'all with ids' => [['scope' => 'all', 'action' => 'archive', 'ids' => ['00000000-0000-4000-8000-000000000001']], 'ids'],
]);

test('filter and pagination bounds reject invalid requests with 422', function (array $query, string $field) {
    $admin = Admin::factory()->create();
    $this->actingAs($admin, 'admin')->getJson(route('admin.notifications.data', $query))->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    [['filter' => 'everything'], 'filter'],
    [['length' => -1], 'length'],
    [['length' => 101], 'length'],
    [['start' => -1], 'start'],
]);

test('notification mutation endpoints enforce CSRF protection', function () {
    $admin = Admin::factory()->create();
    app(NotifyService::class)->send($admin, 'Başlık', 'İçerik');
    $item = $admin->notifications()->firstOrFail();
    $this->actingAs($admin, 'admin');
    $this->app['env'] = 'local';
    $this->post(route('admin.notifications.open', $item->id))->assertStatus(419);
    $this->patchJson(route('admin.notifications.read', $item->id))->assertStatus(419);
    $this->patchJson(route('admin.notifications.update'), ['scope' => 'all', 'action' => 'archive'])->assertStatus(419);
    expect($item->fresh()->read_at)->toBeNull()->and($item->fresh()->archived_at)->toBeNull();
});

test('notification table pagination is bounded and sorts chronologically in either direction', function () {
    $this->freezeSecond();
    $admin = Admin::factory()->create();
    $service = app(NotifyService::class);
    foreach (range(1, 12) as $number) {
        $service->send($admin, 'Kayıt '.$number, 'İçerik');
        $this->travel(1)->seconds();
    }
    $this->actingAs($admin, 'admin');
    $this->getJson(route('admin.notifications.data', ['length' => 10, 'start' => 0]))
        ->assertOk()->assertJsonCount(10, 'data')->assertJsonPath('data.0.data.title', 'Kayıt 12');
    $this->getJson(route('admin.notifications.data', ['length' => 10, 'start' => 10]))
        ->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('data.0.data.title', 'Kayıt 2');
    $this->getJson(route('admin.notifications.data', ['length' => 10, 'order' => [['dir' => 'asc']]]))
        ->assertOk()->assertJsonPath('data.0.data.title', 'Kayıt 1');
    $this->patchJson(route('admin.notifications.update'), ['scope' => 'all', 'action' => 'restore'])
        ->assertUnprocessable()->assertJsonValidationErrors('action');
    expect($admin->notifications()->whereNotNull('archived_at')->count())->toBe(0);
});

test('a target whose module table has been removed still has a readable notification fallback', function () {
    $admin = Admin::factory()->create();
    app(NotifyService::class)->send($admin, 'Eski başvuru', 'Saklanan içerik', route('admin.forms.submissions.show', 999999));
    $item = $admin->notifications()->firstOrFail();
    Exceptions::fake();
    Schema::drop('form_submissions');
    $this->actingAs($admin, 'admin')->get(route('admin.notifications.show', $item->id))
        ->assertOk()->assertSee('Saklanan içerik')->assertSee('artık mevcut değil');
    $this->post(route('admin.notifications.open', $item->id))->assertRedirectToRoute('admin.notifications.show', $item->id);
    Exceptions::assertReported(QueryException::class);
});
