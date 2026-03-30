<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            if (! Schema::hasColumn('contacts', 'source_title')) {
                $table->string('source_title', 500)->nullable()->after('source_url')->comment('유입 화면 제목(예: 포트폴리오명)');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            if (Schema::hasColumn('contacts', 'source_title')) {
                $table->dropColumn('source_title');
            }
        });
    }
};
