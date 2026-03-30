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
            if (! Schema::hasColumn('contacts', 'source_type')) {
                $table->string('source_type', 50)->nullable()->after('budget')->comment('유입 유형(예: portfolio)');
            }
            if (! Schema::hasColumn('contacts', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->comment('유입 식별자(예: portfolio id)');
            }
            if (! Schema::hasColumn('contacts', 'source_url')) {
                $table->string('source_url', 2048)->nullable()->after('source_id')->comment('유입 URL');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('contacts')) {
            return;
        }

        Schema::table('contacts', function (Blueprint $table) {
            $dropColumns = [];
            if (Schema::hasColumn('contacts', 'source_url')) {
                $dropColumns[] = 'source_url';
            }
            if (Schema::hasColumn('contacts', 'source_id')) {
                $dropColumns[] = 'source_id';
            }
            if (Schema::hasColumn('contacts', 'source_type')) {
                $dropColumns[] = 'source_type';
            }
            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
