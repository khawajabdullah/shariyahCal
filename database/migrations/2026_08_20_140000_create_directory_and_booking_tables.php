<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('madhahib', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 12)->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('scholars', function (Blueprint $table) {
            $table->id();
            $table->string('cal_username')->unique();
            $table->unsignedBigInteger('cal_user_id')->nullable()->unique();
            $table->string('name');
            $table->string('initials', 8)->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('bio')->nullable();
            $table->json('credentials')->nullable();
            $table->string('country')->nullable();
            $table->string('flag', 8)->nullable();
            $table->foreignId('madhhab_id')->nullable()->constrained('madhahib')->nullOnDelete();
            $table->string('tier', 32)->default('standard');
            $table->json('specialties')->nullable();
            $table->json('schedule')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('language_scholar', function (Blueprint $table) {
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->foreignId('scholar_id')->constrained('scholars')->cascadeOnDelete();
            $table->primary(['language_id', 'scholar_id']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('cal_booking_uid')->unique();
            $table->foreignId('scholar_id')->nullable()->constrained('scholars')->nullOnDelete();
            $table->string('attendee_name')->nullable();
            $table->string('attendee_email')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('status', 40)->default('unknown');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 8)->nullable();
            $table->string('meeting_url')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'starts_at']);
            $table->index('attendee_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('language_scholar');
        Schema::dropIfExists('scholars');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('madhahib');
    }
};
