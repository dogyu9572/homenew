<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            if (! Schema::hasColumn('portfolios', 'is_direct_site_link')) {
                $table->boolean('is_direct_site_link')->default(false)->after('site_url')->comment('목록·카드 클릭 시 상세 없이 site_url 새 창');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolios', function (Blueprint $table) {
            if (Schema::hasColumn('portfolios', 'is_direct_site_link')) {
                $table->dropColumn('is_direct_site_link');
            }
        });
    }
};
