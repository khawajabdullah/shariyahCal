<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholars', function (Blueprint $table) {
            $table->text('cal_api_key')->nullable()->after('email');
            $table->timestamp('event_types_synced_at')->nullable()->after('last_synced_at');
        });

        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholar_id')->constrained('scholars')->cascadeOnDelete();
            $table->unsignedBigInteger('cal_event_type_id');
            $table->string('title');
            $table->string('slug');
            $table->unsignedSmallInteger('length_in_minutes');
            $table->text('description')->nullable();
            $table->string('booking_url')->nullable();
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedInteger('minimum_booking_notice')->nullable();
            $table->json('locations')->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->boolean('is_active')->default(true);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 8)->default('usd');
            $table->json('raw_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['scholar_id', 'cal_event_type_id']);
            $table->index(['scholar_id', 'is_active', 'is_hidden']);
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('event_type_id')->nullable()->after('scholar_id')->constrained('event_types')->nullOnDelete();
            $table->unsignedBigInteger('cal_booking_id')->nullable()->after('cal_booking_uid');
            $table->unsignedBigInteger('cal_event_type_id')->nullable()->after('cal_booking_id');
            $table->string('attendee_phone', 40)->nullable()->after('attendee_email');
            $table->string('attendee_timezone', 64)->nullable()->after('attendee_phone');
            $table->string('attendee_language', 16)->nullable()->after('attendee_timezone');
            $table->text('notes')->nullable()->after('attendee_language');
            $table->json('guests')->nullable()->after('notes');
            $table->string('payment_status', 32)->default('pending')->after('currency');
            $table->string('title')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('event_type_id');
            $table->dropColumn([
                'cal_booking_id',
                'cal_event_type_id',
                'attendee_phone',
                'attendee_timezone',
                'attendee_language',
                'notes',
                'guests',
                'payment_status',
                'title',
            ]);
        });

        Schema::dropIfExists('event_types');

        Schema::table('scholars', function (Blueprint $table) {
            $table->dropColumn(['cal_api_key', 'event_types_synced_at']);
        });
    }
};
