<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 출퇴근 보정 입력(기록일시/사유) 필드 추가
     */
    public function up(): void
    {
        Schema::table('staff_attendance_records', function (Blueprint $table) {
            $table->text('adjustment_reason')->nullable()->after('workplace');
        });
    }

    public function down(): void
    {
        Schema::table('staff_attendance_records', function (Blueprint $table) {
            $table->dropColumn('adjustment_reason');
        });
    }
};
