<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portfolio_feature_developments', function (Blueprint $table) {
            if (! Schema::hasColumn('portfolio_feature_developments', 'background_text')) {
                $table->string('background_text')->nullable()->after('content');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portfolio_feature_developments', function (Blueprint $table) {
            if (Schema::hasColumn('portfolio_feature_developments', 'background_text')) {
                $table->dropColumn('background_text');
            }
        });
    }
};
