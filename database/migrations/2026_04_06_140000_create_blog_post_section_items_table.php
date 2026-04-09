<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 소제목·본문을 blog_post_section_items로 분리 (목차는 blog_post_sections에 유지)
     */
    public function up(): void
    {
        if (Schema::hasTable('blog_post_section_items')) {
            return;
        }

        Schema::create('blog_post_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_section_id')->constrained('blog_post_sections')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->string('subheading', 255)->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->index(['blog_post_section_id', 'sort_order']);
        });

        if (Schema::hasColumn('blog_post_sections', 'subheading') || Schema::hasColumn('blog_post_sections', 'content')) {
            $sections = DB::table('blog_post_sections')->orderBy('id')->get();
            foreach ($sections as $section) {
                DB::table('blog_post_section_items')->insert([
                    'blog_post_section_id' => $section->id,
                    'sort_order' => 0,
                    'subheading' => $section->subheading ?? null,
                    'content' => $section->content ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('blog_post_sections', function (Blueprint $table) {
            if (Schema::hasColumn('blog_post_sections', 'subheading')) {
                $table->dropColumn('subheading');
            }
            if (Schema::hasColumn('blog_post_sections', 'content')) {
                $table->dropColumn('content');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blog_post_sections', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_post_sections', 'subheading')) {
                $table->string('subheading', 255)->nullable()->after('subtitle');
            }
            if (! Schema::hasColumn('blog_post_sections', 'content')) {
                $table->longText('content')->nullable()->after('subheading');
            }
        });

        if (Schema::hasTable('blog_post_section_items')) {
            $items = DB::table('blog_post_section_items')
                ->orderBy('blog_post_section_id')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('blog_post_section_id');

            foreach ($items as $sectionId => $group) {
                $first = $group->first();
                DB::table('blog_post_sections')
                    ->where('id', $sectionId)
                    ->update([
                        'subheading' => $first->subheading ?? null,
                        'content' => $first->content ?? null,
                    ]);
            }

            Schema::dropIfExists('blog_post_section_items');
        }
    }
};
