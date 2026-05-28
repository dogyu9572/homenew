<?php

namespace App\Services\Backoffice;

use App\Models\ApprovalDocument;
use App\Models\ApprovalLine;
use App\Models\Board;
use App\Models\DailyVisitorStat;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * 휴가계 본문 일수에 적용할 가중치 (반차 0.5, 반반차 0.25, 연차 1)
     *
     * @var array<string, float>
     */
    private const VACATION_LEAVE_DAY_WEIGHTS = [
        'vacation-half-day' => 0.5,
        'vacation-quarter-day' => 0.25,
        'vacation-annual' => 1.0,
    ];

    /**
     * 대시보드 전체 데이터 가져오기
     */
    public function getDashboardData(): array
    {
        $approvalSummary = $this->getApprovalSummaryData();
        $usedFromApprovals = $this->getUsedLeaveDaysThisYearForWriter();
        $manualAddon = $this->getManualUsedLeaveDaysForCurrentUser();
        $usedLeaveDaysThisYear = round($usedFromApprovals + $manualAddon, 4);

        return [
            'boards' => $this->getBoardsOrderedByMenu(),
            'totalBoards' => Board::where('is_single_page', false)->count(),
            'totalPosts' => $this->getTotalPostsCount(),
            'activeBanners' => $this->getActiveBannersCount(),
            'activePopups' => $this->getActivePopupsCount(),
            'visitorStats' => $this->getVisitorStats(),
            'approvalStats' => $approvalSummary['stats'],
            'boxSummaries' => $approvalSummary['boxSummaries'],
            'noticePreviewPosts' => $this->getNoticePreviewPosts(),
            'usedLeaveDaysThisYear' => $usedLeaveDaysThisYear,
            'usedLeaveDaysThisYearDisplay' => $this->formatLeaveDaysStatDisplay($usedLeaveDaysThisYear),
        ];
    }

    /**
     * 로그인 사용자의 연차 수동입력(일) — 관리자 수정 화면에서 저장
     * (세션의 Auth 사용자 모델은 로그인 시점 스냅샷이라 DB에서 매 요청 조회)
     */
    private function getManualUsedLeaveDaysForCurrentUser(): float
    {
        $userId = (int) (Auth::id() ?? 0);
        if ($userId <= 0) {
            return 0.0;
        }

        $value = User::query()
            ->whereKey($userId)
            ->value('manual_used_leave_days');

        return max(0.0, (float) ($value ?? 0));
    }

    /**
     * 통계 카드용: 소수부가 있을 때만 소수로 표시 (정수면 소수점 미표시)
     */
    private function formatLeaveDaysStatDisplay(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ','), '0'), '.');
    }

    /**
     * 통계 데이터 가져오기
     */
    public function getStatisticsData(): array
    {
        $today = now()->format('Y-m-d');
        
        return [
            'today_visitors' => DailyVisitorStat::where('visit_date', $today)
                ->value('visitor_count') ?? 0,
            'total_visitors' => DailyVisitorStat::sum('visitor_count'),
            'daily_stats' => $this->getDailyChartData(),
            'monthly_stats' => $this->getMonthlyChartData(),
        ];
    }

    /**
     * 방문객 통계 데이터 가져오기
     */
    public function getVisitorStats(): array
    {
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');

        $todayVisitors = (int) (DailyVisitorStat::where('visit_date', $today)
            ->value('visitor_count') ?? 0);
        $yesterdayVisitors = (int) (DailyVisitorStat::where('visit_date', $yesterday)
            ->value('visitor_count') ?? 0);
        $thisMonthVisitors = (int) DailyVisitorStat::query()
            ->whereYear('visit_date', now()->year)
            ->whereMonth('visit_date', now()->month)
            ->sum('visitor_count');
        $totalVisitors = (int) DailyVisitorStat::sum('visitor_count');

        return [
            'today' => $todayVisitors,
            'yesterday' => $yesterdayVisitors,
            'this_month' => $thisMonthVisitors,
            'total' => $totalVisitors,
            'today_visitors' => $todayVisitors,
            'total_visitors' => $totalVisitors,
            'daily_stats' => $this->getDailyChartData(),
            'monthly_stats' => $this->getMonthlyChartData(),
        ];
    }

    /**
     * 일별 차트 데이터 생성 (이번 달)
     */
    public function getDailyChartData(): array
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        
        $stats = DailyVisitorStat::whereBetween('visit_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->orderBy('visit_date')
            ->get()
            ->keyBy(function($item) {
                return $item->visit_date->format('Y-m-d');
            });
            
        $labels = [];
        $data = [];
        
        $current = $startOfMonth->copy();
        while ($current->lte($endOfMonth)) {
            $dateStr = $current->format('Y-m-d');
            $labels[] = $current->format('m/d');
            
            $stat = $stats->get($dateStr);
            $data[] = $stat ? $stat->visitor_count : 0;
            
            $current->addDay();
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * 월별 차트 데이터 생성 (이번 년도)
     */
    public function getMonthlyChartData(): array
    {
        $startOfYear = now()->startOfYear();
        $endOfYear = now()->endOfYear();
        
        $stats = DailyVisitorStat::whereBetween('visit_date', [$startOfYear->format('Y-m-d'), $endOfYear->format('Y-m-d')])
            ->get()
            ->groupBy(function($item) {
                return substr($item->visit_date, 0, 7);
            });
            
        $labels = [];
        $data = [];
        
        $current = $startOfYear->copy();
        while ($current->lte($endOfYear)) {
            $monthStr = $current->format('Y-m');
            $labels[] = $current->format('Y년 m월');
            
            $monthStats = $stats->get($monthStr, collect());
            $monthTotal = $monthStats->sum('visitor_count');
            $data[] = $monthTotal;
            
            $current->addMonth();
        }
        
        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * 전체 게시글 수 계산 (실시간 집계, 삭제된 글 제외)
     */
    public function getTotalPostsCount(): int
    {
        $boards = Board::where('is_active', true)
            ->where('is_single_page', false)
            ->get();
        
        $totalPosts = 0;
        
        foreach ($boards as $board) {
            $tableName = 'board_' . $board->slug;
            try {
                $count = DB::table($tableName)
                    ->whereNull('deleted_at')
                    ->count();
                $totalPosts += $count;
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $totalPosts;
    }

    /**
     * 활성 배너 수 계산
     */
    public function getActiveBannersCount(): int
    {
        try {
            return DB::table('banners')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->where(function($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * 활성 팝업 수 계산
     */
    public function getActivePopupsCount(): int
    {
        try {
            return DB::table('popups')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->where(function($query) {
                    $query->whereNull('start_date')
                          ->orWhere('start_date', '<=', now());
                })
                ->where(function($query) {
                    $query->whereNull('end_date')
                          ->orWhere('end_date', '>=', now());
                })
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * admin_menus의 1차 메뉴 순서대로 게시판 정렬
     */
    public function getBoardsOrderedByMenu()
    {
        try {
            $boards = Board::where('is_single_page', false)->get();
            
            $boardsWithMenu = $boards->map(function($board) {
                $adminMenu = $board->getAdminMenu();
                $board->adminMenu = $adminMenu;
                return $board;
            });
            
            $filteredBoards = $boardsWithMenu->filter(function($board) {
                return $board->adminMenu !== null;
            });
            
            $sortedBoards = $filteredBoards->sortBy(function($board) {
                return $board->adminMenu->parent->order;
            });
            
            return $sortedBoards->take(10)->values();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * 전자결재 요약 데이터
     */
    private function getApprovalSummaryData(): array
    {
        $userId = (int) (Auth::id() ?? 0);
        if ($userId <= 0) {
            return [
                'stats' => [
                    'personal_submitted' => 0,
                    'pending_approval' => 0,
                    'pending_cooperation' => 0,
                ],
                'boxSummaries' => $this->emptyApprovalBoxSummaries(),
            ];
        }

        $personalSubmittedCount = ApprovalDocument::query()
            ->where('writer_id', $userId)
            ->where('status', ApprovalDocument::STATUS_PENDING)
            ->count();
        $personalRejectedCount = ApprovalDocument::query()
            ->where('writer_id', $userId)
            ->where('status', ApprovalDocument::STATUS_REJECTED)
            ->count();
        $personalCompletedCount = ApprovalDocument::query()
            ->where('writer_id', $userId)
            ->where('status', ApprovalDocument::STATUS_COMPLETED)
            ->count();

        $pendingApprovalCount = ApprovalDocument::query()
            ->where('status', ApprovalDocument::STATUS_PENDING)
            ->whereHas('lines', function ($query) use ($userId) {
                $query->where('line_type', ApprovalLine::TYPE_APPROVAL)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_PENDING);
            })
            ->count();
        $completedApprovalCount = ApprovalDocument::query()
            ->whereHas('lines', function ($query) use ($userId) {
                $query->where('line_type', ApprovalLine::TYPE_APPROVAL)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_APPROVED);
            })
            ->count();

        $pendingCooperationCount = ApprovalDocument::query()
            ->whereHas('lines', function ($query) use ($userId) {
                $query->where('line_type', ApprovalLine::TYPE_COOPERATION)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_PENDING);
            })
            ->count();
        $completedCooperationCount = ApprovalDocument::query()
            ->whereHas('lines', function ($query) use ($userId) {
                $query->where('line_type', ApprovalLine::TYPE_COOPERATION)
                    ->where('user_id', $userId)
                    ->where('status', ApprovalLine::STATUS_CONFIRMED);
            })
            ->count();

        return [
            'stats' => [
                'personal_submitted' => $personalSubmittedCount,
                'pending_approval' => $pendingApprovalCount,
                'pending_cooperation' => $pendingCooperationCount,
            ],
            'boxSummaries' => [
                [
                    'title' => '개인 문서함',
                    'route' => route('backoffice.approvals.personal'),
                    'items' => [
                        ['label' => '상신문서', 'count' => $personalSubmittedCount],
                        ['label' => '반려문서', 'count' => $personalRejectedCount],
                        ['label' => '결재완료', 'count' => $personalCompletedCount],
                    ],
                ],
                [
                    'title' => '결재할 문서함',
                    'route' => route('backoffice.approvals.pending'),
                    'items' => [
                        ['label' => '미결재 문서', 'count' => $pendingApprovalCount],
                        ['label' => '결재완료', 'count' => $completedApprovalCount],
                    ],
                ],
                [
                    'title' => '협조 문서함',
                    'route' => route('backoffice.approvals.cooperation'),
                    'items' => [
                        ['label' => '미결재 문서', 'count' => $pendingCooperationCount],
                        ['label' => '결재완료', 'count' => $completedCooperationCount],
                    ],
                ],
            ],
        ];
    }

    /**
     * 로그인 정보가 없을 때 기본 전자결재 요약
     */
    private function emptyApprovalBoxSummaries(): array
    {
        return [
            [
                'title' => '개인 문서함',
                'route' => route('backoffice.approvals.personal'),
                'items' => [
                    ['label' => '상신문서', 'count' => 0],
                    ['label' => '반려문서', 'count' => 0],
                    ['label' => '결재완료', 'count' => 0],
                ],
            ],
            [
                'title' => '결재할 문서함',
                'route' => route('backoffice.approvals.pending'),
                'items' => [
                    ['label' => '미결재 문서', 'count' => 0],
                    ['label' => '결재완료', 'count' => 0],
                ],
            ],
            [
                'title' => '협조 문서함',
                'route' => route('backoffice.approvals.cooperation'),
                'items' => [
                    ['label' => '미결재 문서', 'count' => 0],
                    ['label' => '결재완료', 'count' => 0],
                ],
            ],
        ];
    }

    /**
     * 공지사항 게시판 미리보기
     */
    private function getNoticePreviewPosts(int $limit = 5)
    {
        try {
            $noticeBoard = Board::query()
                ->where('slug', 'notices')
                ->where('is_active', true)
                ->first();

            if (! $noticeBoard) {
                return collect();
            }

            return DB::table('board_notices')
                ->select(['id', 'title', 'is_notice', 'created_at'])
                ->orderBy('is_notice', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * 로그인 사용자 기준, 올해 결재완료된 반차계·반반차계·연차휴가계 사용 일수 합계
     * (문서 본문 content 일수 × 유형 가중치 합산)
     */
    private function getUsedLeaveDaysThisYearForWriter(): float
    {
        $userId = (int) (Auth::id() ?? 0);
        if ($userId <= 0) {
            return 0.0;
        }

        $year = (int) now()->year;
        $templateKeys = array_keys(self::VACATION_LEAVE_DAY_WEIGHTS);

        $documents = ApprovalDocument::query()
            ->select(['template_key', 'content'])
            ->where('writer_id', $userId)
            ->where('status', ApprovalDocument::STATUS_COMPLETED)
            ->whereIn('template_key', $templateKeys)
            ->whereYear('completed_at', $year)
            ->get();

        $total = 0.0;
        foreach ($documents as $document) {
            $weight = self::VACATION_LEAVE_DAY_WEIGHTS[$document->template_key] ?? 0.0;
            if ($weight <= 0.0) {
                continue;
            }
            $content = is_array($document->content) ? $document->content : [];
            $raw = $content['days'] ?? $content['leave_days'] ?? null;
            if (! is_numeric($raw)) {
                continue;
            }
            $days = max(0.0, (float) $raw);
            $total += $days * $weight;
        }

        return round($total, 4);
    }
}

