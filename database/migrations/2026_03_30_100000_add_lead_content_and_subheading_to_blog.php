<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 블로그 상세 상단 리드(lead_content), 구간별 소제목(subheading) 추가
     */
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_posts', 'lead_content')) {
                $table->longText('lead_content')->nullable()->after('title');
            }
        });

        Schema::table('blog_post_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_post_sections', 'subheading')) {
                $table->string('subheading', 255)->nullable()->after('subtitle');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (Schema::hasColumn('blog_posts', 'lead_content')) {
                $table->dropColumn('lead_content');
            }
        });

        Schema::table('blog_post_sections', function (Blueprint $table) {
            if (Schema::hasColumn('blog_post_sections', 'subheading')) {
                $table->dropColumn('subheading');
            }
        });
    }
};
