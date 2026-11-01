<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('industry');
            $t->string('country', 2)->default('AR');
            $t->string('timezone')->default('America/Argentina/Buenos_Aires');
            $t->string('currency', 3)->default('ARS');
            $t->boolean('is_demo')->default(false);
            $t->timestamp('expires_at')->nullable();
            $t->unsignedInteger('next_quote')->default(1);
            $t->timestamps();
        });
        Schema::create('business_memberships', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained();
            $t->foreignId('user_id')->constrained();
            $t->string('role')->default('owner');
            $t->unique('user_id');
            $t->timestamps();
        });
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->text('address')->nullable();
            $t->text('notes')->nullable();
            $t->timestamp('archived_at')->nullable();
            $t->unique(['business_id', 'id']);
            $t->timestamps();
        });
        Schema::create('quotes', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained();
            $t->unsignedBigInteger('customer_id');
            $t->unsignedInteger('number');
            $t->json('draft');
            $t->string('status')->default('borrador');
            $t->unique(['business_id', 'number']);
            $t->unique(['business_id', 'id']);
            $t->foreign(['business_id', 'customer_id'])->references(['business_id', 'id'])->on('customers');
            $t->timestamps();
        });
        Schema::create('quote_versions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained();
            $t->unsignedBigInteger('quote_id');
            $t->unsignedInteger('version');
            $t->json('snapshot');
            $t->string('token_hash', 64)->unique();
            $t->date('expires_on');
            $t->timestamp('revoked_at')->nullable();
            $t->string('status')->default('pendiente');
            $t->unique(['quote_id', 'version']);
            $t->unique(['business_id', 'id']);
            $t->foreign(['business_id', 'quote_id'])->references(['business_id', 'id'])->on('quotes');
            $t->timestamps();
        });
        Schema::create('quote_decisions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained();
            $t->unsignedBigInteger('quote_version_id')->unique();
            $t->string('decision');
            $t->string('name');
            $t->text('message')->nullable();
            $t->foreign(['business_id', 'quote_version_id'])->references(['business_id', 'id'])->on('quote_versions');
            $t->timestamps();
        });
        Schema::create('jobs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained();
            $t->unsignedBigInteger('customer_id');
            $t->unsignedBigInteger('quote_id')->nullable()->unique();
            $t->string('title');
            $t->text('scope');
            $t->unsignedBigInteger('amount_minor');
            $t->string('currency', 3);
            $t->string('status')->default('pendiente');
            $t->date('starts_on')->nullable();
            $t->date('due_on')->nullable();
            $t->text('next_action')->nullable();
            $t->date('action_on')->nullable();
            $t->unique(['business_id', 'id']);
            $t->foreign(['business_id', 'customer_id'])->references(['business_id', 'id'])->on('customers');
            $t->foreign(['business_id', 'quote_id'])->references(['business_id', 'id'])->on('quotes');
            $t->timestamps();
        });
        Schema::create('payment_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->constrained();
            $t->unsignedBigInteger('job_id');
            $t->unsignedBigInteger('customer_id');
            $t->unsignedBigInteger('amount_minor');
            $t->string('currency', 3);
            $t->date('paid_on');
            $t->string('method');
            $t->string('reference')->nullable();
            $t->string('idempotency_key', 64);
            $t->timestamp('voided_at')->nullable();
            $t->text('void_reason')->nullable();
            $t->unique(['business_id', 'idempotency_key']);
            $t->foreign(['business_id', 'job_id'])->references(['business_id', 'id'])->on('jobs');
            $t->foreign(['business_id', 'customer_id'])->references(['business_id', 'id'])->on('customers');
            $t->timestamps();
        });
        foreach (['activity_events', 'product_events'] as $table) {
            Schema::create($table, function (Blueprint $t) use ($table) {
                $t->id();
                $t->foreignId('business_id')->constrained();
                if ($table === 'product_events') {
                    $t->string('dedupe_key')->nullable();
                    $t->unique(['business_id', 'dedupe_key']);
                }$t->string('name');
                $t->string('subject_type')->nullable();
                $t->unsignedBigInteger('subject_id')->nullable();
                $t->json('details')->nullable();
                $t->timestamps();
            });
        }
        Schema::create('pilot_requests', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email');
            $t->text('message');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['pilot_requests', 'product_events', 'activity_events', 'payment_entries', 'jobs', 'quote_decisions', 'quote_versions', 'quotes', 'customers', 'business_memberships', 'businesses'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
