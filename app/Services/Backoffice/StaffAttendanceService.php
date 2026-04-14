<?php

namespace App\Services\Backoffice;

use App\Models\StaffAttendanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffAttendanceService
{
    /**
     * 출퇴근 1건 기록 (당일 출근/퇴근 순서 검증 포함)
     */
    public function recordAttendance(User $user, string $kind, string $workplace, Request $request): StaffAttendanceRecord
    {
        $this->assertEnumKind($kind);
        $this->assertEnumWorkplace($workplace);
        $this->assertFlowAllows($user, $kind);

        return DB::transaction(function () use ($user, $kind, $workplace, $request) {
            return StaffAttendanceRecord::query()->create([
                'user_id' => $user->id,
                'kind' => $kind,
                'workplace' => $workplace,
                'recorded_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $this->truncateUserAgent($request->userAgent()),
            ]);
        });
    }

    /**
     * 전 직원(관리자 역할) 출퇴근 목록
     */
    public function paginatedForAdmins(int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        return StaffAttendanceRecord::query()
            ->with([
                'user' => static function ($query) {
                    $query->select('id', 'name', 'login_id', 'department', 'position', 'role');
                },
            ])
            ->whereHas('user', static function ($query) {
                $query->whereIn('role', ['super_admin', 'admin']);
            })
            ->orderByDesc('recorded_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    private function assertEnumKind(string $kind): void
    {
        if (! in_array($kind, [StaffAttendanceRecord::KIND_CLOCK_IN, StaffAttendanceRecord::KIND_CLOCK_OUT], true)) {
            throw ValidationException::withMessages([
                'kind' => '출근 또는 퇴만 선택할 수 있습니다.',
            ]);
        }
    }

    private function assertEnumWorkplace(string $workplace): void
    {
        if (! in_array($workplace, [StaffAttendanceRecord::WORKPLACE_OFFICE, StaffAttendanceRecord::WORKPLACE_REMOTE], true)) {
            throw ValidationException::withMessages([
                'workplace' => '사무실 또는 재택만 선택할 수 있습니다.',
            ]);
        }
    }

    /**
     * 당일 마지막 기록 기준 출근→퇴근 교대만 허용
     */
    private function assertFlowAllows(User $user, string $kind): void
    {
        $last = $this->lastTodayRecord($user);

        if ($kind === StaffAttendanceRecord::KIND_CLOCK_IN) {
            if ($last !== null && $last->kind === StaffAttendanceRecord::KIND_CLOCK_IN) {
                throw ValidationException::withMessages([
                    'kind' => '오늘 이미 출근 처리되었습니다. 퇴근 후 다시 출근할 수 있습니다.',
                ]);
            }

            return;
        }

        if ($last === null) {
            throw ValidationException::withMessages([
                'kind' => '오늘 출근 기록이 없어 퇴근할 수 없습니다.',
            ]);
        }

        if ($last->kind === StaffAttendanceRecord::KIND_CLOCK_OUT) {
            throw ValidationException::withMessages([
                'kind' => '이미 퇴근 처리되었습니다. 출근 후 퇴근할 수 있습니다.',
            ]);
        }
    }

    private function lastTodayRecord(User $user): ?StaffAttendanceRecord
    {
        return StaffAttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereDate('recorded_at', now()->toDateString())
            ->orderByDesc('recorded_at')
            ->orderByDesc('id')
            ->first();
    }

    private function truncateUserAgent(?string $ua): ?string
    {
        if ($ua === null || $ua === '') {
            return null;
        }

        return mb_substr($ua, 0, 2000);
    }
}
