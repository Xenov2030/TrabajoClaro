<?php

namespace Tests\Feature;

use App\Logging\SafeLog;
use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\FeedbackEntry;
use App\Services\Demo;
use App\Services\Screen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Tests\TestCase;

class BetaFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_feedback_saves_screen_release_description_and_optional_contact(): void
    {
        $this->get('/feedback?screen=quote-editor')->assertOk()->assertSee(config('beta.version'));
        $this->post('/feedback', ['screen' => 'quote-editor', 'description' => 'No entiendo cómo agregar un ítem.', 'contact' => 'ana@example.test', 'release' => 'version-falsa'])->assertRedirect('/feedback?screen=quote-editor')->assertSessionHas('notice');
        $entry = FeedbackEntry::firstOrFail();
        $this->assertSame('quote-editor', $entry->screen);
        $this->assertSame(config('beta.version'), $entry->release);
        $this->assertSame('ana@example.test', $entry->contact);
        $this->assertNull($entry->business_id);
        $this->get('/feedback?screen=landing')->assertOk()->assertSee('Guardamos tu feedback');
        $this->post('/feedback', ['screen' => 'landing', 'description' => 'El recorrido me resultó claro.'])->assertSessionHasNoErrors();
        $this->assertNull(FeedbackEntry::latest('id')->first()->contact);
    }

    public function test_feedback_validates_abuse_and_never_uses_client_supplied_ownership(): void
    {
        $u = app(Demo::class)->create();
        $own = BusinessMembership::where('user_id', $u->id)->value('business_id');
        $other = app(Demo::class)->create();
        $otherBusiness = BusinessMembership::where('user_id', $other->id)->value('business_id');
        $this->actingAs($u)->post('/feedback', ['screen' => 'jobs', 'description' => 'Me gustaría entender mejor el saldo.', 'business_id' => $otherBusiness, 'user_id' => $other->id])->assertSessionHasNoErrors();
        $entry = FeedbackEntry::firstOrFail();
        $this->assertSame($own, $entry->business_id);
        $this->assertSame($u->id, $entry->user_id);
        $this->assertTrue($entry->is_demo);
        $this->post('/feedback', ['screen' => 'https://bad.test/p/token', 'description' => 'Comentario largo'])->assertSessionHasErrors('screen');
        $this->post('/feedback', ['screen' => 'jobs', 'description' => 'Comentario largo', 'website' => 'https://spam.test'])->assertSessionHasErrors('website');
        $this->assertDatabaseCount('feedback_entries', 1);
    }

    public function test_screen_mapping_does_not_record_private_tokens_or_ids(): void
    {
        $token = str_repeat('a', 64);
        $this->assertSame('public-quote', Screen::fromPath('p/'.$token));
        $this->assertSame('quote-editor', Screen::fromPath('app/quotes/456/edit'));
        $this->assertSame('jobs', Screen::fromPath('app/jobs/789'));
        $this->get('/feedback?screen=p/'.$token)->assertOk()->assertDontSee($token);
    }

    public function test_retention_deletes_old_feedback_but_keeps_registered_businesses(): void
    {
        $b = Business::create(['name' => 'Negocio persistente', 'industry' => 'Herrería']);
        FeedbackEntry::create(['business_id' => $b->id, 'screen' => 'jobs', 'release' => 'v1', 'description' => 'Feedback antiguo', 'created_at' => now()->subDays(91)]);
        FeedbackEntry::create(['business_id' => $b->id, 'screen' => 'jobs', 'release' => 'v2', 'description' => 'Feedback reciente']);
        $this->artisan('feedback:prune')->assertSuccessful();
        $this->assertDatabaseCount('feedback_entries', 1);
        $this->artisan('demo:prune')->assertSuccessful();
        $this->assertDatabaseHas('businesses', ['id' => $b->id]);
    }

    public function test_demo_cleanup_does_not_break_feedback_retention(): void
    {
        $u = app(Demo::class)->create();
        $this->actingAs($u)->post('/feedback', ['screen' => 'dashboard', 'description' => 'Quiero poder revisar las acciones.'])->assertSessionHasNoErrors();
        $this->travel(25)->hours();
        $this->artisan('demo:prune')->assertSuccessful();
        $e = FeedbackEntry::firstOrFail();
        $this->assertNull($e->business_id);
        $this->assertNull($e->user_id);
        $this->assertTrue($e->is_demo);
        $this->travelBack();
    }

    public function test_error_logging_omits_exception_values_and_redacts_tokens(): void
    {
        $handler = new TestHandler;
        $logger = new Logger('test', [$handler]);
        (new SafeLog)($logger);
        $logger->error('consulta con clave-secreta', ['exception' => new \RuntimeException('clave-secreta y datos de cliente')]);
        $logger->warning('Error en /p/'.str_repeat('a', 64), ['password' => 'secreto', 'nested' => ['authorization' => 'Bearer secreto']]);
        $serialized = json_encode($handler->getRecords());
        $this->assertStringNotContainsString('clave-secreta', $serialized);
        $this->assertStringNotContainsString('Bearer secreto', $serialized);
        $this->assertStringNotContainsString(str_repeat('a', 64), $serialized);
        $this->assertStringContainsString('[oculto]', $serialized);
    }

    public function test_beta_surfaces_feedback_version_and_test_account_without_credentials(): void
    {
        $this->get('/')->assertOk()->assertSee('Crear cuenta de prueba')->assertSee('Enviar feedback')->assertSee(config('beta.version'))->assertDontSee('SoloEjemploLocal2026!')->assertDontSee('ejemplo@example.test');
        $this->get('/beta')->assertOk()->assertSee('7 días');
    }
}
