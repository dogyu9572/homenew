<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('admin_menus')
            ->where('name', '개인문서함')
            ->whereIn('url', ['/backoffice/approval-main/personal', '/backoffice/approval-personal'])
            ->update(['url' => '/backoffice/approval-personal']);

        DB::table('admin_menus')
            ->where('name', '결재할 문서함')
            ->whereIn('url', ['/backoffice/approval-main/pending', '/backoffice/approval-pending'])
            ->update(['url' => '/backoffice/approval-pending']);

        DB::table('admin_menus')
            ->where('name', '협조 문서함')
            ->whereIn('url', ['/backoffice/approval-main/cooperation', '/backoffice/approval-cooperation'])
            ->update(['url' => '/backoffice/approval-cooperation']);
    }

    public function down(): void
    {
        DB::table('admin_menus')
            ->where('name', '개인문서함')
            ->where('url', '/backoffice/approval-personal')
            ->update(['url' => '/backoffice/approval-main/personal']);

        DB::table('admin_menus')
            ->where('name', '결재할 문서함')
            ->where('url', '/backoffice/approval-pending')
            ->update(['url' => '/backoffice/approval-main/pending']);

        DB::table('admin_menus')
            ->where('name', '협조 문서함')
            ->where('url', '/backoffice/approval-cooperation')
            ->update(['url' => '/backoffice/approval-main/cooperation']);
    }
};
