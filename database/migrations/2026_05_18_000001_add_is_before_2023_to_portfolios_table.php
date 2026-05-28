<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            if (! Schema::hasColumn('portfolios', 'is_before_2023')) {
                $table->boolean('is_before_2023')
                    ->default(false)
                    ->after('sort_order')
                    ->comment('2023년 이전 포트폴리오 여부');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            if (Schema::hasColumn('portfolios', 'is_before_2023')) {
                $table->dropColumn('is_before_2023');
            }
        });
    }
};
