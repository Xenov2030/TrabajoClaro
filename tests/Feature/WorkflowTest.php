<?php

namespace Tests\Feature;

use App\Models\ActivityEvent;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\Customer;
use App\Models\Job;
use App\Models\ProductEvent;
use App\Models\Quote;
use App\Models\QuoteVersion;
use App\Models\User;
use App\Services\Csv;
use App\Services\Demo;
use App\Services\Money;
use App\Services\Workflow;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $suffix = 'one'): array
    {
        $u = User::create(['name' => 'Dueño '.$suffix, 'email' => $suffix.'@example.test', 'password' => Hash::make('PasswordSeguro123')]);
        $b = Business::create(['name' => 'Negocio '.$suffix, 'industry' => 'Carpintería']);
        BusinessMembership::create(['user_id' => $u->id, 'business_id' => $b->id]);
        $c = Customer::create(['business_id' => $b->id, 'name' => 'Cliente '.$suffix, 'notes' => 'NOTA PRIVADA '.$suffix, 'email' => $suffix.'-cliente@example.test']);

        return [$u, $b, $c];
    }

    private function draft(array $overrides = []): array
    {
        return array_replace(['title' => 'Mueble a medida', 'scope' => 'Fabricación e instalación', 'exclusions' => 'No incluye pintura', 'issued_on' => now('America/Argentina/Buenos_Aires')->toDateString(), 'expires_on' => now('America/Argentina/Buenos_Aires')->addDays(15)->toDateString(), 'currency' => 'ARS', 'items' => [['description' => 'Mueble', 'quantity' => '1', 'unit_price' => '1000', 'category' => 'material']], 'discount' => '0', 'tax_rate' => '0', 'advance' => '100', 'conditions' => 'Anticipo y saldo contra entrega'], $overrides);
    }

    private function quote(Business $b, Customer $c, array $overrides = []): Quote
    {
        return app(Workflow::class)->createQuote($b->id, $c->id, $this->draft($overrides));
    }

    private function accepted(Quote $q): Job
    {
        $f = app(Workflow::class);
        [$v] = $f->publish($q);
        $f->decide($v, ['name' => 'Cliente', 'decision' => 'aceptado', 'confirmed' => true]);

        return $f->convert($q);
    }

    private function payment(array $overrides = []): array
    {
        return array_replace(['amount' => '200', 'currency' => 'ARS', 'paid_on' => now('America/Argentina/Buenos_Aires')->toDateString(), 'method' => 'transferencia', 'idempotency_key' => (string) Str::uuid()], $overrides);
    }

    private function invalid(callable $fn): void
    {
        try {
            $fn();
            $this->fail('Se esperaba rechazo de la operación.');
        } catch (ValidationException $e) {
            $this->assertNotEmpty($e->errors());
        }
    }

    public function test_full_http_journey_with_two_businesses_and_guest(): void
    {
        $this->post('/register', ['name' => 'Ana', 'email' => 'ana@example.test', 'password' => 'PasswordSeguro123', 'password_confirmation' => 'PasswordSeguro123', 'business_name' => 'Taller Ana', 'industry' => 'Carpintería', 'country' => 'AR', 'timezone' => 'America/Argentina/Buenos_Aires', 'currency' => 'ARS'])->assertRedirect('/app');
        $u = User::where('email', 'ana@example.test')->firstOrFail();
        $b = Business::firstOrFail();
        [$other,$ob,$oc] = $this->owner('other');
        $this->post('/app/customers', ['name' => 'Marina', 'phone' => '+54 9 (11) 2345-6789', 'notes' => 'Privado'])->assertRedirect();
        $c = Customer::where('business_id', $b->id)->firstOrFail();
        $this->assertSame('+5491123456789', $c->phone);
        $this->post('/app/quotes', $this->draft() + ['customer_id' => $c->id])->assertRedirect();
        $q = Quote::where('business_id', $b->id)->firstOrFail();
        $this->from('/app/quotes/'.$q->id)->post('/app/quotes/'.$q->id.'/publish')->assertRedirect()->assertSessionHas('share_url');
        $url = session('share_url');
        $this->post('/logout')->assertRedirect('/');
        $this->get($url)->assertOk()->assertSee('Fabricación e instalación')->assertDontSee('Privado')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertDatabaseCount('quote_decisions', 0);
        $this->post($url, ['name' => 'Marina', 'decision' => 'aceptado', 'confirmed' => '1'])->assertRedirect();
        $this->post('/login', ['email' => $u->email, 'password' => 'PasswordSeguro123'])->assertRedirect('/app');
        $this->post('/app/quotes/'.$q->id.'/convert')->assertRedirect();
        $j = Job::where('business_id', $b->id)->firstOrFail();
        $pay = $this->payment(['amount' => '250']);
        $this->from('/app/jobs/'.$j->id)->post('/app/jobs/'.$j->id.'/payments', $pay)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(75000, $j->balance());
        $this->get('/app/jobs/'.$j->id)->assertOk()->assertSee('ARS 750,00');
        $this->get('/app')->assertOk();
        $this->actingAs($other)->get('/app/jobs/'.$j->id)->assertNotFound();
        $this->get('/app/quotes/'.$q->id)->assertNotFound();
    }

    public function test_money_rounds_items_discount_then_tax_exactly(): void
    {
        $d = $this->draft(['items' => [['description' => 'A', 'quantity' => '0.005', 'unit_price' => '1'], ['description' => 'B', 'quantity' => '3', 'unit_price' => '0.10']], 'discount' => '0.01', 'tax_rate' => '5', 'advance' => '0']);
        $s = Money::quote($d);
        $this->assertSame(31, $s['subtotal_minor']);
        $this->assertSame(1, $s['discount_minor']);
        $this->assertSame(2, $s['tax_minor']);
        $this->assertSame(32, $s['total_minor']);
        $this->assertSame('ARS 1.234.567,89', Money::display(123456789));
    }

    public function test_invalid_quantities_prices_discounts_tax_and_advance_are_rejected(): void
    {
        foreach (['-1', '0', '1.0001', '1e2', 'abc', '1,5'] as $q) {
            $this->invalid(fn () => Money::quote($this->draft(['items' => [['description' => 'A', 'quantity' => $q, 'unit_price' => '1']]])));
        }
        foreach (['-1', '1.001', '1e2', '9999999999'] as $p) {
            $this->invalid(fn () => Money::quote($this->draft(['items' => [['description' => 'A', 'quantity' => '1', 'unit_price' => $p]]])));
        }
        foreach ([['discount' => '1000.01'], ['tax_rate' => '100.01'], ['advance' => '1000.01']] as $d) {
            $this->invalid(fn () => Money::quote($this->draft($d)));
        }
    }

    public function test_business_isolation_covers_ids_mutations_versions_payments_and_lists(): void
    {
        [$u,$b,$c] = $this->owner();
        [$u2,$b2,$c2] = $this->owner('two');
        $q = $this->quote($b2, $c2);
        $j = $this->accepted($q);
        $p = app(Workflow::class)->pay($j, $this->payment());
        $v = QuoteVersion::where('quote_id', $q->id)->firstOrFail();
        $this->actingAs($u);
        foreach (['/app/customers/'.$c2->id, '/app/quotes/'.$q->id, '/app/quotes/'.$q->id.'/edit', '/app/jobs/'.$j->id, '/app/jobs/'.$j->id.'/edit', '/app/versions/'.$v->id.'/print'] as $path) {
            $this->get($path)->assertNotFound();
        }
        foreach (['/app/customers/'.$c2->id, '/app/customers/'.$c2->id.'/archive', '/app/quotes/'.$q->id, '/app/quotes/'.$q->id.'/publish', '/app/quotes/'.$q->id.'/duplicate', '/app/quotes/'.$q->id.'/convert', '/app/versions/'.$v->id.'/revoke', '/app/jobs/'.$j->id, '/app/jobs/'.$j->id.'/payments', '/app/payments/'.$p->id.'/void'] as $path) {
            $this->post($path, [])->assertNotFound();
        }
        $this->get('/app/customers')->assertOk()->assertDontSee('Cliente two');
        $this->get('/app/quotes')->assertOk()->assertDontSee('Mueble a medida');
        $this->post('/app/quotes', $this->draft() + ['customer_id' => $c2->id])->assertNotFound();
        $this->post('/app/jobs', ['customer_id' => $c2->id, 'title' => 'X', 'scope' => 'X', 'amount' => '10', 'currency' => 'ARS', 'status' => 'pendiente'])->assertNotFound();
    }

    public function test_database_rejects_cross_business_customer_reference(): void
    {
        [, $b,$c] = $this->owner();
        [, $b2] = $this->owner('two');
        $this->expectException(QueryException::class);
        Quote::create(['business_id' => $b2->id, 'customer_id' => $c->id, 'number' => 1, 'draft' => $this->draft()]);
    }

    public function test_published_snapshot_is_immutable_when_customer_and_draft_change(): void
    {
        [$u,$b,$c] = $this->owner();
        $q = $this->quote($b, $c);
        [$v,$token] = app(Workflow::class)->publish($q);
        $snapshot = $v->fresh()->snapshot;
        $c->update(['name' => 'Otro nombre']);
        $q->update(['draft' => Money::quote($this->draft(['title' => 'Otra propuesta', 'items' => [['description' => 'B', 'quantity' => '1', 'unit_price' => '999']]]))]);
        $this->assertSame($snapshot, $v->fresh()->snapshot);
        $this->get('/p/'.$token)->assertOk()->assertSee('Cliente one')->assertDontSee('Otro nombre')->assertDontSee('Otra propuesta');
        $this->assertNotSame($token, $v->token_hash);
        $this->assertSame(hash('sha256', $token), $v->token_hash);
    }

    public function test_superseded_and_revoked_versions_cannot_be_decided(): void
    {
        [$u,$b,$c] = $this->owner();
        $q = $this->quote($b, $c);
        $f = app(Workflow::class);
        [$v,$t] = $f->publish($q);
        [$new] = $f->publish($q);
        $this->get('/p/'.$t)->assertNotFound();
        $this->invalid(fn () => $f->decide($v, ['name' => 'X', 'decision' => 'aceptado', 'confirmed' => true]));
        $new->update(['revoked_at' => now()]);
        $this->invalid(fn () => $f->decide($new, ['name' => 'X', 'decision' => 'aceptado', 'confirmed' => true]));
        $this->assertDatabaseCount('quote_decisions', 0);
    }

    public function test_expiry_is_evaluated_in_business_timezone_and_blocks_post(): void
    {
        [$u,$b,$c] = $this->owner();
        $q = $this->quote($b, $c);
        [$v,$t] = app(Workflow::class)->publish($q);
        $this->travel(16)->days();
        $this->get('/p/'.$t)->assertOk()->assertSee('vencido');
        $this->post('/p/'.$t, ['name' => 'X', 'decision' => 'aceptado', 'confirmed' => 1])->assertSessionHasErrors();
        $this->assertDatabaseCount('quote_decisions', 0);
        $this->travelBack();
    }

    public function test_invalid_and_duplicate_decisions_and_get_never_change_state(): void
    {
        [$u,$b,$c] = $this->owner();
        $q = $this->quote($b, $c);
        [$v,$t] = app(Workflow::class)->publish($q);
        $this->get('/p/'.$t.'?decision=aceptado')->assertOk();
        $this->assertDatabaseCount('quote_decisions', 0);
        foreach ([['name' => 'X', 'decision' => 'aceptado'], ['name' => '', 'decision' => 'aceptado', 'confirmed' => 1], ['name' => 'X', 'decision' => 'otro', 'confirmed' => 1]] as $d) {
            $this->post('/p/'.$t, $d)->assertSessionHasErrors();
        }
        $this->post('/p/'.$t, ['name' => 'X', 'decision' => 'aceptado', 'confirmed' => 1])->assertSessionHasNoErrors();
        $this->post('/p/'.$t, ['name' => 'X', 'decision' => 'rechazado', 'confirmed' => 1])->assertSessionHasErrors();
        $this->assertDatabaseCount('quote_decisions', 1);
    }

    public function test_rejection_and_modification_do_not_create_jobs(): void
    {
        [, $b,$c] = $this->owner();
        $f = app(Workflow::class);
        foreach (['rechazado', 'modificacion'] as $decision) {
            $q = $this->quote($b, $c);
            [$v] = $f->publish($q);
            $f->decide($v, ['name' => 'X', 'decision' => $decision, 'confirmed' => 1]);
            $this->invalid(fn () => $f->convert($q));
        }
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_accepted_conversion_is_idempotent_and_preserves_snapshot_amount(): void
    {
        [, $b,$c] = $this->owner();
        $q = $this->quote($b, $c);
        $j = $this->accepted($q);
        $again = app(Workflow::class)->convert($q);
        $this->assertSame($j->id, $again->id);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertSame(100000, (int) $j->amount_minor);
        $this->invalid(fn () => app(Workflow::class)->publish($q));
    }

    public function test_partial_payments_retry_void_and_balance(): void
    {
        [, $b,$c] = $this->owner();
        $j = $this->accepted($this->quote($b, $c));
        $f = app(Workflow::class);
        $d = $this->payment();
        $p = $f->pay($j, $d);
        $this->assertSame($p->id, $f->pay($j, $d)->id);
        $this->assertSame(80000, $j->balance());
        $this->invalid(fn () => $f->pay($j, $this->payment(['amount' => '801'])));
        $this->invalid(fn () => $f->pay($j, $this->payment(['amount' => '0'])));
        $this->invalid(fn () => $f->pay($j, array_replace($d, ['amount' => '201'])));
        $f->void($p, 'Error de carga');
        $f->void($p, 'Segundo reintento');
        $this->assertSame(100000, $j->balance());
        $this->assertDatabaseCount('payment_entries', 1);
        $this->assertSame(1, ActivityEvent::where('name', 'Cobro anulado')->count());
        $f->pay($j, $this->payment(['amount' => '1000']));
        $this->assertSame(0, $j->balance());
        $this->assertSame('pendiente', $j->fresh()->status);
    }

    public function test_currencies_cannot_mix_and_dashboard_keeps_totals_separate(): void
    {
        [$u,$b,$c] = $this->owner();
        $j = $this->accepted($this->quote($b, $c));
        $usd = $this->accepted($this->quote($b, $c, ['currency' => 'USD']));
        $f = app(Workflow::class);
        $this->invalid(fn () => $f->pay($j, $this->payment(['currency' => 'USD'])));
        $f->pay($j, $this->payment(['amount' => '100']));
        $f->pay($usd, $this->payment(['amount' => '20', 'currency' => 'USD']));
        $this->actingAs($u)->get('/app')->assertOk()->assertSee('ARS 100,00')->assertSee('USD 20,00');
        $this->assertSame(90000, $j->balance());
        $this->assertSame(98000, $usd->balance());
    }

    public function test_exports_are_authorized_scoped_and_formula_safe(): void
    {
        [$u,$b,$c] = $this->owner();
        [, $other,$otherC] = $this->owner('two');
        $c->update(['name' => '=HYPERLINK("https://bad.test")', 'notes' => " \t@SUM(1)"]);
        $j = $this->accepted($this->quote($b, $c));
        app(Workflow::class)->pay($j, $this->payment());
        $this->get('/app/export/customers')->assertRedirect('/login');
        foreach (['customers', 'quotes', 'jobs', 'payments'] as $type) {
            $response = $this->actingAs($u)->get('/app/export/'.$type)->assertOk();
            $csv = $response->streamedContent();
            $this->assertStringNotContainsString('DESARROLLADO POR NICOLAS AGUIRRES', $csv);
            $this->assertStringNotContainsString('https://nicolasaguirrescode.com/', $csv);
            $this->assertStringNotContainsString('Cliente two', $csv);
            $this->assertStringNotContainsString('NOTA PRIVADA two', $csv);
            $this->assertStringNotContainsString('token_hash', $csv);
        }
        $csv = $this->get('/app/export/customers')->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("' \t@SUM(1)", $csv);
        foreach (['=1', '+1', '-1', '@x', "\tSUM()", '  =1', "\xEF\xBB\xBF=1"] as $value) {
            $this->assertStringStartsWith("'", Csv::safe($value));
        }
    }

    public function test_archiving_keeps_history_and_blocks_new_quotes_for_archived_customer(): void
    {
        [$u,$b,$c] = $this->owner();
        $q = $this->quote($b, $c);
        $this->actingAs($u)->post('/app/customers/'.$c->id.'/archive')->assertRedirect();
        $this->get('/app/quotes/'.$q->id)->assertOk();
        $this->get('/app/customers/'.$c->id)->assertOk();
        $this->assertDatabaseHas('quotes', ['id' => $q->id]);
        $this->expectException(ModelNotFoundException::class);
        $this->quote($b, $c);
    }

    public function test_demo_visitors_are_isolated_and_excluded_from_metrics(): void
    {
        $d = app(Demo::class);
        $u = $d->create();
        $u2 = $d->create();
        $b = BusinessMembership::where('user_id', $u->id)->first()->business_id;
        $b2 = BusinessMembership::where('user_id', $u2->id)->first()->business_id;
        $this->assertNotEquals($b, $b2);
        $this->assertDatabaseCount('product_events', 0);
        $j = Job::where('business_id', $b)->first();
        $this->actingAs($u2)->get('/app/jobs/'.$j->id)->assertNotFound();
        $this->actingAs($u)->get('/app')->assertOk()->assertSee('Datos ficticios');
        $this->travel(25)->hours();
        $this->get('/app')->assertForbidden();
        $this->artisan('demo:prune')->assertSuccessful();
        $this->assertDatabaseMissing('businesses', ['id' => $b]);
        $this->assertDatabaseMissing('businesses', ['id' => $b2]);
        $this->travelBack();
    }

    public function test_amount_changes_require_owner_confirmation_reason_and_cannot_undercut_collected(): void
    {
        [$u,$b,$c] = $this->owner();
        $j = $this->accepted($this->quote($b, $c));
        $this->actingAs($u);
        $d = ['customer_id' => $c->id, 'title' => $j->title, 'scope' => $j->scope, 'amount' => '1200', 'currency' => 'ARS', 'status' => 'en curso'];
        $this->post('/app/jobs/'.$j->id, $d)->assertSessionHasErrors('change_reason');
        $this->post('/app/jobs/'.$j->id, $d + ['confirm_change' => 1, 'change_reason' => 'Se agrega instalación adicional'])->assertSessionHasNoErrors();
        $this->assertSame(120000, (int) $j->fresh()->amount_minor);
        app(Workflow::class)->pay($j, $this->payment(['amount' => '500']));
        $this->post('/app/jobs/'.$j->id, array_replace($d, ['amount' => '499', 'confirm_change' => 1, 'change_reason' => 'Corrección']))->assertSessionHasErrors('amount');
    }

    public function test_pilot_form_persists_and_honeypot_rejects_abuse(): void
    {
        $this->post('/pilot', ['name' => 'Piloto', 'email' => 'piloto@example.test', 'message' => 'Quiero probarlo con mi carpintería', 'website' => ''])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('pilot_requests', 1);
        $this->post('/pilot', ['name' => 'Bot', 'email' => 'bot@example.test', 'message' => 'Un mensaje suficientemente largo', 'website' => 'https://spam.test'])->assertSessionHasErrors();
        $this->assertDatabaseCount('pilot_requests', 1);
    }

    public function test_required_pages_render_for_owner(): void
    {
        [$u,$b,$c] = $this->owner();
        $q = $this->quote($b, $c);
        $j = $this->accepted($q);
        $this->actingAs($u);
        foreach (['/app', '/app/customers', '/app/customers/new', '/app/customers/'.$c->id, '/app/quotes', '/app/quotes/new', '/app/quotes/'.$q->id, '/app/quotes/'.$q->id.'/edit', '/app/jobs', '/app/jobs?view=board', '/app/jobs/new', '/app/jobs/'.$j->id, '/app/jobs/'.$j->id.'/edit', '/app/settings', '/app/versions/'.QuoteVersion::first()->id.'/print'] as $url) {
            $this->get($url)->assertOk()->assertSee('DESARROLLADO POR NICOLAS AGUIRRES');
        }
    }

    public function test_product_events_are_separate_and_first_milestones_only(): void
    {
        [, $b,$c] = $this->owner();
        $f = app(Workflow::class);
        $j = $this->accepted($this->quote($b, $c));
        $f->pay($j, $this->payment());
        $this->quote($b, $c);
        $this->assertSame(1, ProductEvent::where('business_id', $b->id)->where('name', 'first_quote_created')->count());
        $this->assertSame(5, ProductEvent::where('business_id', $b->id)->count());
        $this->assertGreaterThan(5, ActivityEvent::where('business_id', $b->id)->count());
    }

    public function test_csrf_is_required_and_valid_token_allows_post(): void
    {
        $this->app->instance(ValidateCsrfToken::class, new class($this->app, $this->app['encrypter']) extends ValidateCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
        $d = ['name' => 'Ana', 'email' => 'ana@example.test', 'message' => 'Necesito seguir mis trabajos.'];
        $this->post('/pilot', $d)->assertStatus(419);
        $this->withSession(['_token' => 'csrf-test-token'])->post('/pilot', $d + ['_token' => 'csrf-test-token'])->assertRedirect();
        $this->assertDatabaseCount('pilot_requests', 1);
    }

    public function test_customer_content_is_escaped_and_public_does_not_expose_contacts(): void
    {
        [$u,$b,$c] = $this->owner();
        $c->update(['name' => '<script>alert(1)</script>', 'phone' => '+5491123456789']);
        $q = $this->quote($b, $c);
        [$v,$token] = app(Workflow::class)->publish($q);
        $this->get('/p/'.$token)->assertOk()->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('+5491123456789')->assertDontSee('one-cliente@example.test')->assertDontSee('NOTA PRIVADA');
    }

    public function test_computed_totals_cannot_overflow_integer_storage(): void
    {
        $this->invalid(fn () => Money::quote($this->draft(['items' => [['description' => 'Grande', 'quantity' => '999999999', 'unit_price' => '999999999.99']]])));
    }

    public function test_auth_public_and_demo_limits_are_independent(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'inexistente@example.test', 'password' => 'incorrecta'])->assertSessionHasErrors();
        }
        $this->post('/login', ['email' => 'inexistente@example.test', 'password' => 'incorrecta'])->assertStatus(429);
        $this->post('/demo')->assertRedirect('/app');
    }

    public function test_unavailable_public_links_keep_privacy_headers_on_errors(): void
    {
        $this->get('/p/'.str_repeat('a', 64))->assertNotFound()->assertHeader('Referrer-Policy', 'no-referrer')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->get('/pagina-inexistente')->assertNotFound()->assertSee('No encontramos esta página');
    }

    public function test_two_separate_registrations_complete_the_http_flow_with_independent_balances(): void
    {
        $records = [];
        foreach (['alfa' => '250', 'beta' => '100'] as $name => $partial) {
            $email = $name.'@example.test';
            $this->post('/register', ['name' => $name, 'email' => $email, 'password' => 'PasswordSeguro123', 'password_confirmation' => 'PasswordSeguro123', 'business_name' => 'Taller '.$name, 'industry' => 'Herrería', 'country' => 'AR', 'timezone' => 'America/Argentina/Buenos_Aires', 'currency' => 'ARS'])->assertRedirect('/app');
            $u = User::where('email', $email)->firstOrFail();
            $b = Business::findOrFail(BusinessMembership::where('user_id', $u->id)->firstOrFail()->business_id);
            $this->post('/app/customers', ['name' => 'Cliente '.$name])->assertRedirect();
            $c = Customer::where('business_id', $b->id)->firstOrFail();
            $this->post('/app/quotes', $this->draft(['title' => 'Propuesta '.$name]) + ['customer_id' => $c->id])->assertSessionHasNoErrors()->assertRedirect();
            $q = Quote::where('business_id', $b->id)->firstOrFail();
            $this->from('/app/quotes/'.$q->id)->post('/app/quotes/'.$q->id.'/publish')->assertSessionHas('share_url');
            $url = session('share_url');
            $this->post('/logout')->assertRedirect('/');
            $this->get($url)->assertOk()->assertSee('Propuesta '.$name);
            $this->post($url, ['name' => 'Cliente '.$name, 'decision' => 'aceptado', 'confirmed' => 1])->assertSessionHasNoErrors()->assertRedirect();
            $this->post('/login', ['email' => $email, 'password' => 'PasswordSeguro123'])->assertRedirect('/app');
            $this->post('/app/quotes/'.$q->id.'/convert')->assertRedirect();
            $this->post('/app/quotes/'.$q->id.'/convert')->assertRedirect();
            $this->assertSame(1, Job::where('business_id', $b->id)->count());
            $j = Job::where('business_id', $b->id)->firstOrFail();
            $this->from('/app/jobs/'.$j->id)->post('/app/jobs/'.$j->id.'/payments', $this->payment(['amount' => $partial]))->assertSessionHasNoErrors()->assertRedirect();
            $this->assertSame(100000 - Money::minor($partial), $j->balance());
            $records[$name] = [$u, $j];
            $this->post('/logout');
        }
        $this->assertDatabaseCount('businesses', 2);
        $this->assertDatabaseCount('jobs', 2);
        $this->assertDatabaseCount('payment_entries', 2);
        $this->actingAs($records['alfa'][0])->get('/app/jobs/'.$records['alfa'][1]->id)->assertOk()->assertSee('ARS 750,00');
        $this->get('/app/jobs/'.$records['beta'][1]->id)->assertNotFound();
        $this->assertStringNotContainsString('Propuesta beta', $this->get('/app/export/jobs')->streamedContent());
    }
}
