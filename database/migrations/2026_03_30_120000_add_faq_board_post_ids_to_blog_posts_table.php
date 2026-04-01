<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 블로그 상세 하단에 노출할 board_faq 게시글 id 목록 (순서 유지)
     */
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_posts', 'faq_board_post_ids')) {
                $table->json('faq_board_post_ids')->nullable()->after('lead_content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            if (Schema::hasColumn('blog_posts', 'faq_board_post_ids')) {
                $table->dropColumn('faq_board_post_ids');
            }
        });
    }
};
