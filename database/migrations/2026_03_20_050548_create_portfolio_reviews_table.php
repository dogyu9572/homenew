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
        if (Schema::hasTable('portfolio_reviews')) {
            return;
        }

        Schema::create('portfolio_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('portfolio_id');
            $table->string('title')->nullable();
            $table->string('manager_name')->nullable();
            $table->longText('content')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['portfolio_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_reviews');
    }
};
