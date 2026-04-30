<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_evaluable_departments', function (Blueprint $table) {
            $table->timestamp('filling_started_at')->nullable()->after('department_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_evaluable_departments', function (Blueprint $table) {
            $table->dropColumn('filling_started_at');
        });
    }
};
