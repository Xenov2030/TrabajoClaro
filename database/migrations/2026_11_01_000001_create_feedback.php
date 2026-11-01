<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback_entries', function (Blueprint $t) {
            $t->id();
            $t->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_demo')->default(false);
            $t->string('screen', 50);
            $t->string('release', 80);
            $t->text('description');
            $t->string('contact', 190)->nullable();
            $t->timestamps();
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_entries');
    }
};
