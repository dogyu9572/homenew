<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 관리자 접속 로그에 요청 경로 저장 (어느 백오피스 화면 접근인지 조회용)
     */
    public function up(): void
    {
        Schema::table('admin_access_logs', function (Blueprint $table) {
            $table->string('access_path', 500)->nullable()->after('referer')->comment('요청 경로');
            $table->string('http_method', 10)->nullable()->after('access_path')->comment('HTTP 메서드');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin_access_logs', function (Blueprint $table) {
            $table->dropColumn(['access_path', 'http_method']);
        });
    }
};
