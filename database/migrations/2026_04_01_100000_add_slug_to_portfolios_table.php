<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * 루트 1세그먼트 URL과 충돌하지 않도록 포트폴리오 slug 백필 시 제외한다.
     *
     * @var list<string>
     */
    private const RESERVED_ROOT_SLUGS = [
        'about',
        'service',
        'industries',
        'portfolio',
        'blog',
        'contact',
        'terms',
        'popup',
        'auth',
        'backoffice',
        'team-story',
        'website-insights',
        'website-trends',
        'success-stories',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('portfolios')) {
            return;
        }

        Schema::table('portfolios', function (Blueprint $table) {
            if (! Schema::hasColumn('portfolios', 'slug')) {
                $table->string('slug', 255)->nullable()->after('title');
            }
        });

        $this->backfillSlugs();

        Schema::table('portfolios', function (Blueprint $table) {
            $table->unique('slug');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement('ALTER TABLE portfolios MODIFY slug VARCHAR(255) NOT NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('portfolios')) {
            return;
        }

        Schema::table('portfolios', function (Blueprint $table) {
            if (Schema::hasColumn('portfolios', 'slug')) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            }
        });
    }

    private function backfillSlugs(): void
    {
        $rows = DB::table('portfolios')->orderBy('id')->get(['id', 'title']);
        $used = [];

        foreach ($rows as $row) {
            $base = Str::slug((string) $row->title);
            if ($base === '') {
                $base = 'portfolio';
            }
            if (in_array($base, self::RESERVED_ROOT_SLUGS, true)) {
                $base = $base.'-case';
            }

            $slug = $base;
            $n = 2;
            while (in_array($slug, $used, true)
                || in_array($slug, self::RESERVED_ROOT_SLUGS, true)) {
                $slug = $base.'-'.$n;
                $n++;
            }
            $used[] = $slug;

            DB::table('portfolios')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }
};
