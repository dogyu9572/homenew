<?php

use App\Http\Controllers\Backoffice\AccessStatisticsController;
use App\Http\Controllers\Backoffice\AdminController;
use App\Http\Controllers\Backoffice\AdminGroupController;
use App\Http\Controllers\Backoffice\AdminMenuController;
use App\Http\Controllers\Backoffice\ApprovalController;
use App\Http\Controllers\Backoffice\AuthController;
use App\Http\Controllers\Backoffice\BannerController;
use App\Http\Controllers\Backoffice\BlogPostController;
use App\Http\Controllers\Backoffice\BoardController;
use App\Http\Controllers\Backoffice\BoardPostController;
use App\Http\Controllers\Backoffice\BoardSkinController;
use App\Http\Controllers\Backoffice\BoardTemplateController;
use App\Http\Controllers\Backoffice\CategoryController;
use App\Http\Controllers\Backoffice\ContactController;
use App\Http\Controllers\Backoffice\IntraTaxController;
use App\Http\Controllers\Backoffice\LogController;
use App\Http\Controllers\Backoffice\PopupController;
use App\Http\Controllers\Backoffice\PortfolioController;
use App\Http\Controllers\Backoffice\ProjectManageController;
use App\Http\Controllers\Backoffice\SettingController;
use App\Http\Controllers\Backoffice\StaffAttendanceController;
use App\Http\Controllers\Backoffice\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =============================================================================
// 백오피스 인증 라우트
// =============================================================================
Route::prefix('backoffice')->name('backoffice.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

// =============================================================================
// 백오피스 라우트 (관리자 전용)
// =============================================================================

Route::prefix('backoffice')->middleware(['backoffice'])->group(function () {

    // 대시보드
    Route::get('/', [App\Http\Controllers\Backoffice\DashboardController::class, 'index'])
        ->name('backoffice.dashboard');

    // 대시보드 API
    Route::get('/api/statistics', [App\Http\Controllers\Backoffice\DashboardController::class, 'statistics'])
        ->name('backoffice.api.statistics');

    // -------------------------------------------------------------------------
    // 시스템 관리
    // -------------------------------------------------------------------------

    // 관리자 메뉴 관리
    Route::resource('admin-menus', AdminMenuController::class, [
        'names' => 'backoffice.admin-menus',
    ])->except(['show']);

    // 메뉴 순서 업데이트
    Route::post('admin-menus/update-order', [AdminMenuController::class, 'updateOrder'])
        ->name('backoffice.admin-menus.update-order');

    // 메뉴 부모 업데이트 (드래그로 메뉴 이동)
    Route::post('admin-menus/update-parent', [AdminMenuController::class, 'updateParent'])
        ->name('backoffice.admin-menus.update-parent');

    // 카테고리 관리
    // 카테고리 순서 업데이트 (resource 라우트보다 앞에 위치)
    Route::post('categories/update-order', [CategoryController::class, 'updateOrder'])
        ->name('backoffice.categories.update-order');

    // 활성 카테고리 조회 (AJAX - resource 라우트보다 앞에 위치)
    Route::get('categories/active/{group}', [CategoryController::class, 'getActiveCategories'])
        ->name('backoffice.categories.active');

    // 특정 그룹의 1차 카테고리 조회 (AJAX)
    Route::get('categories/get-by-group/{groupId}', [CategoryController::class, 'getByGroup'])
        ->name('backoffice.categories.get-by-group');

    // 카테고리 수정용 데이터 조회 (AJAX)
    Route::get('categories/{category}/edit-data', [CategoryController::class, 'getEditData'])
        ->name('backoffice.categories.edit-data');

    // 인라인 수정 (AJAX)
    Route::post('categories/{category}/update-inline', [CategoryController::class, 'updateInline'])
        ->name('backoffice.categories.update-inline');

    // 모달 등록 (AJAX)
    Route::post('categories/store-modal', [CategoryController::class, 'storeModal'])
        ->name('backoffice.categories.store-modal');

    // 모달 수정 (AJAX)
    Route::put('categories/update-modal', [CategoryController::class, 'updateModal'])
        ->name('backoffice.categories.update-modal');

    // 미리 생성될 코드 조회 (AJAX)
    Route::post('categories/generate-preview-code', [CategoryController::class, 'generatePreviewCode'])
        ->name('backoffice.categories.generate-preview-code');

    Route::resource('categories', CategoryController::class, [
        'names' => 'backoffice.categories',
    ])->except(['show']);

    // 기본설정 관리
    Route::get('setting', [SettingController::class, 'index'])
        ->name('backoffice.setting.index');
    Route::post('setting', [SettingController::class, 'update'])
        ->name('backoffice.setting.update');

    // 접속 로그 관리
    Route::get('logs/access', [LogController::class, 'access'])
        ->name('backoffice.logs.access');
    Route::get('user-access-logs', [LogController::class, 'userAccessLogs'])
        ->name('backoffice.user-access-logs');
    Route::get('admin-access-logs', [LogController::class, 'adminAccessLogs'])
        ->name('backoffice.admin-access-logs');

    // 통계 관리
    Route::get('access-statistics', [AccessStatisticsController::class, 'index'])
        ->name('backoffice.access-statistics');
    Route::get('access-statistics/get-statistics', [AccessStatisticsController::class, 'getStatistics'])
        ->name('backoffice.access-statistics.get-statistics');

    // 관리자 계정 관리
    Route::post('admins/bulk-destroy', [AdminController::class, 'bulkDestroy'])
        ->name('backoffice.admins.bulk-destroy');
    Route::post('admins/check-login-id', [AdminController::class, 'checkLoginId'])
        ->name('backoffice.admins.check-login-id');
    Route::resource('admins', AdminController::class, [
        'names' => 'backoffice.admins',
    ]);

    // 관리자 권한 그룹 관리
    Route::resource('admin-groups', AdminGroupController::class, [
        'names' => 'backoffice.admin-groups',
    ])->except(['show']);

    // 권한 그룹 권한 설정
    Route::get('admin-groups/{admin_group}/permissions', [AdminGroupController::class, 'editPermissions'])
        ->name('backoffice.admin-groups.permissions.edit');
    Route::post('admin-groups/{admin_group}/permissions', [AdminGroupController::class, 'updatePermissions'])
        ->name('backoffice.admin-groups.permissions.update');

    // 출퇴근
    Route::get('attendance', [StaffAttendanceController::class, 'index'])
        ->name('backoffice.attendance.index');
    Route::get('attendance/create', [StaffAttendanceController::class, 'create'])
        ->name('backoffice.attendance.create');
    Route::post('attendance/quick', [StaffAttendanceController::class, 'quickStore'])
        ->middleware('throttle:30,1')
        ->name('backoffice.attendance.quick-store');
    Route::post('attendance', [StaffAttendanceController::class, 'store'])
        ->name('backoffice.attendance.store');

    // 전자결재 URL 구조
    Route::get('approval-main', [ApprovalController::class, 'index'])
        ->name('backoffice.approvals.index');
    Route::get('approval-main/create', [ApprovalController::class, 'create'])
        ->name('backoffice.approvals.create');
    Route::post('approval-main/create', [ApprovalController::class, 'store'])
        ->name('backoffice.approvals.store');
    Route::get('approval-main/create/{templateKey}', [ApprovalController::class, 'createDraft'])
        ->name('backoffice.approvals.drafts.create');
    Route::get('approval-main/users', [ApprovalController::class, 'approverUsers'])
        ->name('backoffice.approvals.users');
    Route::post('approval-main/documents/{docNo}/comments', [ApprovalController::class, 'commentStore'])
        ->name('backoffice.approvals.comments.store');
    Route::delete('approval-main/documents/{docNo}/comments/{opinion}', [ApprovalController::class, 'commentDestroy'])
        ->name('backoffice.approvals.comments.destroy');
    Route::get('approval-personal', [ApprovalController::class, 'personal'])
        ->name('backoffice.approvals.personal');
    Route::get('approval-pending', [ApprovalController::class, 'pending'])
        ->name('backoffice.approvals.pending');
    Route::get('approval-cooperation', [ApprovalController::class, 'cooperation'])
        ->name('backoffice.approvals.cooperation');
    Route::get('approval-personal/documents/{docNo}', [ApprovalController::class, 'showPersonal'])
        ->name('backoffice.approvals.personal.show');
    Route::put('approval-personal/documents/{docNo}', [ApprovalController::class, 'updatePersonalDraft'])
        ->name('backoffice.approvals.personal.update');
    Route::post('approval-personal/documents/{docNo}/approve', [ApprovalController::class, 'approve'])
        ->name('backoffice.approvals.personal.approve');
    Route::post('approval-personal/documents/{docNo}/delegate', [ApprovalController::class, 'delegate'])
        ->name('backoffice.approvals.personal.delegate');
    Route::post('approval-personal/documents/{docNo}/hold', [ApprovalController::class, 'hold'])
        ->name('backoffice.approvals.personal.hold');
    Route::post('approval-personal/documents/{docNo}/reject', [ApprovalController::class, 'reject'])
        ->name('backoffice.approvals.personal.reject');

    Route::get('approval-pending/documents/{docNo}', [ApprovalController::class, 'showPending'])
        ->name('backoffice.approvals.pending.show');
    Route::post('approval-pending/documents/{docNo}/approve', [ApprovalController::class, 'approve'])
        ->name('backoffice.approvals.pending.approve');
    Route::post('approval-pending/documents/{docNo}/delegate', [ApprovalController::class, 'delegate'])
        ->name('backoffice.approvals.pending.delegate');
    Route::post('approval-pending/documents/{docNo}/hold', [ApprovalController::class, 'hold'])
        ->name('backoffice.approvals.pending.hold');
    Route::post('approval-pending/documents/{docNo}/reject', [ApprovalController::class, 'reject'])
        ->name('backoffice.approvals.pending.reject');

    Route::get('approval-cooperation/documents/{docNo}', [ApprovalController::class, 'showCooperation'])
        ->name('backoffice.approvals.cooperation.show');
    Route::post('approval-cooperation/documents/{docNo}/confirm', [ApprovalController::class, 'confirm'])
        ->name('backoffice.approvals.cooperation.confirm');
    Route::post('approval-cooperation/documents/{docNo}/delegate', [ApprovalController::class, 'delegate'])
        ->name('backoffice.approvals.cooperation.delegate');
    Route::post('approval-cooperation/documents/{docNo}/hold', [ApprovalController::class, 'hold'])
        ->name('backoffice.approvals.cooperation.hold');
    Route::post('approval-cooperation/documents/{docNo}/reject', [ApprovalController::class, 'rejectCooperation'])
        ->name('backoffice.approvals.cooperation.reject');

    // 구 URL 호환 리다이렉트
    Route::redirect('approval-create', 'approval-main/create', 301);
    Route::get('approval-drafts/create/{templateKey}', function (string $templateKey) {
        return redirect()->to('/backoffice/approval-main/create/'.$templateKey, 301);
    });
    Route::get('approval-main/personal', function () {
        return redirect()->route('backoffice.approvals.personal', request()->query(), 301);
    });
    Route::get('approval-main/pending', function () {
        return redirect()->route('backoffice.approvals.pending', request()->query(), 301);
    });
    Route::get('approval-main/cooperation', function () {
        return redirect()->route('backoffice.approvals.cooperation', request()->query(), 301);
    });
    Route::get('approval-main/documents/{docNo}', function (string $docNo) {
        $box = (string) request()->query('box', 'pending');
        $routeName = match ($box) {
            'personal' => 'backoffice.approvals.personal.show',
            'cooperation' => 'backoffice.approvals.cooperation.show',
            default => 'backoffice.approvals.pending.show',
        };
        $params = ['docNo' => $docNo, 'tab' => request()->query('tab')];

        return redirect()->route($routeName, array_filter($params, fn ($v) => $v !== null && $v !== ''), 301);
    });

    // -------------------------------------------------------------------------
    // 콘텐츠 관리
    // -------------------------------------------------------------------------

    // 이미지 업로드
    Route::post('upload-image', function (Request $request) {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('uploads/editor', 'public');

            return response()->json([
                'uploaded' => true,
                'url' => asset('storage/'.$path),
            ]);
        }

        return response()->json([
            'uploaded' => false,
            'error' => ['message' => '이미지 업로드에 실패했습니다.'],
        ]);
    });

    // 정렬 순서 업데이트
    Route::post('board-posts/update-sort-order', [BoardPostController::class, 'updateSortOrder'])->name('backoffice.board-posts.update-sort-order');

    // 게시글 관리 (특정 게시판)
    Route::prefix('board-posts/{slug}')->name('backoffice.board-posts.')->group(function () {
        Route::get('/', [BoardPostController::class, 'index'])->name('index');
        Route::get('/create', [BoardPostController::class, 'create'])->name('create');
        Route::post('/', [BoardPostController::class, 'store'])->name('store');
        Route::post('/{post}/comments', [BoardPostController::class, 'commentStore'])->name('comments.store');
        Route::delete('/{post}/comments/{comment}', [BoardPostController::class, 'commentDestroy'])->name('comments.destroy');
        Route::get('/{post}', [BoardPostController::class, 'show'])->name('show');
        Route::get('/{post}/edit', [BoardPostController::class, 'edit'])->name('edit');
        Route::put('/{post}', [BoardPostController::class, 'update'])->name('update');
        Route::delete('/{post}', [BoardPostController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-destroy', [BoardPostController::class, 'bulkDestroy'])->name('bulk_destroy');
    });

    // 게시판 관리
    Route::resource('boards', BoardController::class, [
        'names' => 'backoffice.boards',
    ])->except(['show']); // show는 제외 (게시글 목록과 충돌)

    // 게시판 템플릿 관리
    Route::resource('board-templates', BoardTemplateController::class, [
        'names' => 'backoffice.board-templates',
        'parameters' => ['board-templates' => 'boardTemplate'],
    ]);

    // 게시판 템플릿 추가 기능
    Route::post('board-templates/{boardTemplate}/duplicate', [BoardTemplateController::class, 'duplicate'])
        ->name('backoffice.board-templates.duplicate');
    Route::get('board-templates/{boardTemplate}/data', [BoardTemplateController::class, 'getTemplateData'])
        ->name('backoffice.board-templates.data');

    // 게시판 스킨 관리
    Route::resource('board-skins', BoardSkinController::class, [
        'names' => 'backoffice.board-skins',
        'parameters' => ['board-skins' => 'boardSkin'],
    ]);

    // 게시판 스킨 템플릿 편집
    Route::prefix('board-skins/{boardSkin}')->name('backoffice.board-skins.')->group(function () {
        Route::get('template', [BoardSkinController::class, 'editTemplate'])
            ->name('edit_template');
        Route::post('template', [BoardSkinController::class, 'updateTemplate'])
            ->name('update_template');
    });

    // 게시글 관리
    Route::resource('posts', BoardPostController::class, [
        'names' => 'backoffice.posts',
    ]);

    // 회원 관리
    Route::resource('users', UserController::class, [
        'names' => 'backoffice.users',
    ]);

    // 배너 관리
    Route::resource('banners', BannerController::class, [
        'names' => 'backoffice.banners',
    ]);
    Route::post('banners/update-order', [BannerController::class, 'updateOrder'])->name('backoffice.banners.update-order');

    // 팝업 관리
    Route::resource('popups', PopupController::class, [
        'names' => 'backoffice.popups',
    ]);
    Route::post('popups/update-order', [PopupController::class, 'updateOrder'])->name('backoffice.popups.update-order');

    // 문의(Contact) 접수 관리
    Route::get('contacts/{contact}/attachments/{index}', [ContactController::class, 'downloadAttachment'])
        ->whereNumber('index')
        ->name('backoffice.contacts.attachments.download');

    Route::resource('contacts', ContactController::class, [
        'names' => 'backoffice.contacts',
    ])->only(['index', 'edit', 'update']);
    Route::post('contacts/delete-multiple', [ContactController::class, 'deleteMultiple'])
        ->name('backoffice.contacts.delete-multiple');

    // 포트폴리오 관리
    Route::resource('portfolio', PortfolioController::class, [
        'names' => 'backoffice.portfolio',
    ])->except(['show']);
    Route::post('portfolio/update-order', [PortfolioController::class, 'updateOrder'])
        ->name('backoffice.portfolio.update-order');
    Route::post('portfolio/delete-multiple', [PortfolioController::class, 'deleteMultiple'])
        ->name('backoffice.portfolio.delete-multiple');

    // -------------------------------------------------------------------------
    // 레거시 이관 메뉴 (intraTax / 프로젝트 관리)
    // -------------------------------------------------------------------------
    Route::get('intra-tax', [IntraTaxController::class, 'index'])->name('backoffice.intra-tax.index');
    Route::get('intra-tax/create', [IntraTaxController::class, 'create'])->name('backoffice.intra-tax.create');
    Route::post('intra-tax', [IntraTaxController::class, 'store'])->name('backoffice.intra-tax.store');
    Route::get('intra-tax/{idx}', [IntraTaxController::class, 'edit'])->name('backoffice.intra-tax.edit');
    Route::put('intra-tax/{idx}', [IntraTaxController::class, 'update'])->name('backoffice.intra-tax.update');
    Route::delete('intra-tax/{idx}', [IntraTaxController::class, 'destroy'])->name('backoffice.intra-tax.destroy');
    Route::post('intra-tax/delete-multiple', [IntraTaxController::class, 'destroyMultiple'])->name('backoffice.intra-tax.delete-multiple');
    Route::post('intra-tax/{idx}/unlock', [IntraTaxController::class, 'unlock'])->name('backoffice.intra-tax.unlock');
    Route::get('intra-tax/{idx}/files/{fileIdx}/download', [IntraTaxController::class, 'downloadFile'])->name('backoffice.intra-tax.files.download');
    Route::post('intra-tax/{idx}/comments', [IntraTaxController::class, 'commentStore'])->name('backoffice.intra-tax.comments.store');
    Route::delete('intra-tax/{idx}/comments/{commentIdx}', [IntraTaxController::class, 'commentDestroy'])->name('backoffice.intra-tax.comments.destroy');

    Route::get('project-manages', [ProjectManageController::class, 'index'])->name('backoffice.project-manages.index');
    Route::get('project-manages/create', [ProjectManageController::class, 'create'])->name('backoffice.project-manages.create');
    Route::post('project-manages', [ProjectManageController::class, 'store'])->name('backoffice.project-manages.store');
    Route::get('project-manages/{idx}', [ProjectManageController::class, 'show'])->name('backoffice.project-manages.show');
    Route::get('project-manages/{idx}/edit', [ProjectManageController::class, 'edit'])->name('backoffice.project-manages.edit');
    Route::put('project-manages/{idx}', [ProjectManageController::class, 'update'])->name('backoffice.project-manages.update');
    Route::delete('project-manages/{idx}', [ProjectManageController::class, 'destroy'])->name('backoffice.project-manages.destroy');
    Route::get('project-manages/{idx}/attachments/{attachmentIdx}/download', [ProjectManageController::class, 'downloadAttachment'])->name('backoffice.project-manages.attachments.download');
    Route::post('project-manages/delete-multiple', [ProjectManageController::class, 'destroyMultiple'])->name('backoffice.project-manages.delete-multiple');
    Route::get('project-manages/export', [ProjectManageController::class, 'export'])->name('backoffice.project-manages.export');

    // 세션 연장
    Route::post('session/extend', [App\Http\Controllers\Backoffice\SessionController::class, 'extend'])
        ->name('backoffice.session.extend');

    // 블로그 관리
    Route::post('blog-posts/{blogPost}/event', [BlogPostController::class, 'recordEvent'])
        ->name('backoffice.blog-posts.event');
    Route::post('blog-posts/{blogPost}/like', [BlogPostController::class, 'like'])
        ->name('backoffice.blog-posts.like');
    Route::resource('blog-posts', BlogPostController::class, [
        'names' => 'backoffice.blog-posts',
    ])->except(['show']);
});
