<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholars', function (Blueprint $table) {
            $table->unsignedBigInteger('cal_membership_id')->nullable()->unique()->after('id');
            $table->string('email')->nullable()->unique()->after('cal_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('scholars', function (Blueprint $table) {
            $table->dropUnique(['cal_membership_id']);
            $table->dropUnique(['email']);
            $table->dropColumn(['cal_membership_id', 'email']);
        });
    }
};
