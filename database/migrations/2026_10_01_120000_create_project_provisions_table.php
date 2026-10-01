<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_provisions', function (Blueprint $table) {
            $table->id();
            $table->string('project_name', 32)->unique();
            $table->string('stack', 20)->default('laravel');
            $table->text('root_password');
            $table->string('ftp_username', 32);
            $table->text('ftp_password');
            $table->string('db_username', 32);
            $table->text('db_password');
            $table->string('db_database', 64);
            $table->string('status', 20)->default('queued');
            $table->string('step', 40)->nullable();
            $table->longText('log')->nullable();
            $table->string('site_url')->nullable();
            $table->string('git_url')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('ftp_username');
            $table->unique('db_username');
        });

        if (DB::table('admin_menus')->where('url', '/backoffice/project-provisions')->exists()) {
            return;
        }

        $parentId = DB::table('admin_menus')
            ->whereNull('parent_id')
            ->where('name', '시스템 관리')
            ->value('id');

        $orderQuery = DB::table('admin_menus');
        if ($parentId) {
            $orderQuery->where('parent_id', $parentId);
        } else {
            $orderQuery->whereNull('parent_id');
        }

        DB::table('admin_menus')->insert([
            'parent_id' => $parentId,
            'name' => '프로젝트 생성',
            'url' => '/backoffice/project-provisions',
            'icon' => $parentId ? null : 'fa-server',
            'order' => ((int) $orderQuery->max('order')) + 1,
            'is_active' => 1,
            'permission_key' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('admin_menus')->where('url', '/backoffice/project-provisions')->delete();
        Schema::dropIfExists('project_provisions');
    }
};
