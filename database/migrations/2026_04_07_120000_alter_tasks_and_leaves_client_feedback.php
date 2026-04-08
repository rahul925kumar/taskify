<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
        });

        DB::statement('ALTER TABLE tasks MODIFY project_id BIGINT UNSIGNED NULL');

        Schema::table('tasks', function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
            $table->foreignId('originally_assigned_to')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
            $table->text('cancellation_reason')->nullable()->after('status');
        });

        foreach (DB::table('tasks')->whereNotNull('assigned_to')->cursor() as $row) {
            DB::table('tasks')->where('id', $row->id)->update([
                'originally_assigned_to' => $row->assigned_to,
            ]);
        }

        Schema::table('leaves', function (Blueprint $table) {
            $table->foreignId('delegated_to')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropForeign(['delegated_to']);
            $table->dropColumn('delegated_to');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['originally_assigned_to']);
            $table->dropColumn(['originally_assigned_to', 'cancellation_reason']);
        });
    }
};
