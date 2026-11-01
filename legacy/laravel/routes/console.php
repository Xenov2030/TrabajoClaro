<?php

use App\Models\Business;
use App\Models\BusinessMembership;
use App\Models\FeedbackEntry;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('demo:prune', function () {
    $ids = Business::where('is_demo', true)->where('expires_at', '<', now())->pluck('id');
    foreach ($ids as $id) {
        DB::transaction(function () use ($id) {
            $business = Business::lockForUpdate()->findOrFail($id);
            $users = BusinessMembership::where('business_id', $id)->pluck('user_id');
            foreach (['payment_entries', 'jobs', 'quote_decisions', 'quote_versions', 'quotes', 'customers', 'activity_events', 'product_events', 'business_memberships'] as $table) {
                DB::table($table)->where('business_id', $id)->delete();
            }
            $business->delete();
            User::whereIn('id', $users)->delete();
        });
    }$this->info('Demos vencidas eliminadas: '.$ids->count());
})->purpose('Eliminar demos privadas vencidas y sus datos ficticios');
Schedule::command('demo:prune')->hourly();

Artisan::command('feedback:prune', function () {
    $count = FeedbackEntry::where('created_at', '<', now()->subDays(config('beta.feedback_retention_days')))->delete();
    $this->info('Feedback vencido eliminado: '.$count);
})->purpose('Eliminar feedback con más de 90 días');
Schedule::command('feedback:prune')->daily();
