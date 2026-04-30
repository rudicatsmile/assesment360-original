<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            // Drop the old unique constraint that only covers (questionnaire_id, user_id)
            $table->dropUnique(['questionnaire_id', 'user_id']);

            // Add new composite unique that includes target_department_id
            // This allows the same user to respond to the same questionnaire for different departments
            $table->unique(['questionnaire_id', 'user_id', 'target_department_id'], 'responses_questionnaire_user_department_unique');
        });
    }

    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropUnique('responses_questionnaire_user_department_unique');
            $table->unique(['questionnaire_id', 'user_id']);
        });
    }
};
