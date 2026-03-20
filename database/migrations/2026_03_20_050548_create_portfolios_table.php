<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('portfolios')) {
            return;
        }

        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->unique()->comment('중견/대기업, 학회/협회, 공공기관, 병원/의료, 대학/학원, 일반');
            $table->string('development_summary')->nullable()->comment('주요 개발 내용');
            $table->string('title');
            $table->json('keywords')->nullable()->comment('#태그 배열');
            $table->string('thumbnail_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_main_display')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('detail_summary')->nullable()->comment('상세 설명 요약');
            $table->longText('detail_editor')->nullable()->comment('에디터 본문');
            $table->string('site_url')->nullable();
            $table->string('problem_title')->nullable();
            $table->text('problem_content')->nullable();
            $table->string('solution_title')->nullable();
            $table->text('solution_content')->nullable();
            $table->string('solution_before_image')->nullable();
            $table->string('solution_after_image')->nullable();
            $table->string('feature_title')->nullable();
            $table->text('feature_content')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sort_order');
            $table->index('is_main_display');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolios');
    }
};
