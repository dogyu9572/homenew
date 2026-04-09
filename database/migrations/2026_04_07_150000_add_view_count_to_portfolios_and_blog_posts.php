<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portfolios') && ! Schema::hasColumn('portfolios', 'view_count')) {
            Schema::table('portfolios', function (Blueprint $table) {
                $table->unsignedBigInteger('view_count')->default(0)->after('is_active')->comment('상세 페이지 조회수');
            });
        }

        if (Schema::hasTable('blog_posts') && ! Schema::hasColumn('blog_posts', 'view_count')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->unsignedBigInteger('view_count')->default(0)->after('score_30d')->comment('상세 페이지 조회수');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('blog_posts') && Schema::hasColumn('blog_posts', 'view_count')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->dropColumn('view_count');
            });
        }

        if (Schema::hasTable('portfolios') && Schema::hasColumn('portfolios', 'view_count')) {
            Schema::table('portfolios', function (Blueprint $table) {
                $table->dropColumn('view_count');
            });
        }
    }
};
