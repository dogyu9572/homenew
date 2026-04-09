<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            // soft delete 데이터는 slug 재사용을 허용하기 위해 전역 unique 해제
            $table->dropUnique('portfolios_slug_unique');
            $table->index('slug', 'portfolios_slug_index');
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            $table->dropIndex('portfolios_slug_index');
            $table->unique('slug', 'portfolios_slug_unique');
        });
    }
};
