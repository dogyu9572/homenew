<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 대시보드 연차(일) 표시에 합산할 관리자별 수동 일수
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('manual_used_leave_days', 8, 2)
                ->default(0)
                ->after('contact')
                ->comment('연차 수동입력 일수(전자결재 집계에 가산)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('manual_used_leave_days');
        });
    }
};
