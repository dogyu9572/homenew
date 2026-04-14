<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffAttendanceRequest;
use App\Models\StaffAttendanceRecord;
use App\Services\Backoffice\StaffAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffAttendanceController extends Controller
{
    public function __construct(
        private readonly StaffAttendanceService $staffAttendanceService
    ) {}

    /**
     * 출퇴근 전 직원 목록
     */
    public function index(Request $request): View
    {
        $perPage = (int) $request->input('per_page', 20);
        $records = $this->staffAttendanceService->paginatedForAdmins($perPage);

        return view('backoffice.attendance.index', compact('records'));
    }

    /**
     * 출퇴근 수동 등록 폼
     */
    public function create(): View
    {
        return view('backoffice.attendance.create');
    }

    /**
     * 출퇴근 수동 등록 저장
     */
    public function store(StoreStaffAttendanceRequest $request): RedirectResponse
    {
        $this->staffAttendanceService->recordAttendance(
            $request->user(),
            $request->validated('kind'),
            $request->validated('workplace'),
            $request
        );

        return redirect()->route('backoffice.attendance.index')
            ->with('success', '출퇴근이 등록되었습니다.');
    }

    /**
     * 헤더 빠른 출퇴근 (JSON)
     */
    public function quickStore(StoreStaffAttendanceRequest $request): JsonResponse
    {
        try {
            $record = $this->staffAttendanceService->recordAttendance(
                $request->user(),
                $request->validated('kind'),
                $request->validated('workplace'),
                $request
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? '저장할 수 없습니다.',
                'errors' => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $record->kind === StaffAttendanceRecord::KIND_CLOCK_IN
                ? '출근이 등록되었습니다.'
                : '퇴근이 등록되었습니다.',
            'record' => [
                'recorded_at' => $record->recorded_at->toIso8601String(),
                'kind' => $record->kind,
                'workplace' => $record->workplace,
            ],
        ]);
    }
}
