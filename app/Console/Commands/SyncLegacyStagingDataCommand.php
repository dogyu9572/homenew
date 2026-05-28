<?php

namespace App\Console\Commands;

use App\Support\LegacyAttachmentFileNames;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 동일 DB에 적재된 레거시 스테이징 테이블(ECMS_*, Project*)에서
 * Laravel 현행 테이블(intra_tax_*, project_manage_*)로 데이터를 이관한다.
 *
 * 첨부 DB 행은 이관하되, 실제 파일 복사는 하지 않는다.
 */
class SyncLegacyStagingDataCommand extends Command
{
    protected $signature = 'legacy:sync-staging-data
                            {--dry-run : INSERT/UPDATE를 실행하지 않고 예상 건수만 출력}
                            {--since= : 등록일 기준 이후 행만 (예: 2026-04-09). 게시·댓글·첨부·결제·로그는 각 등록일 컬럼 사용. 프로젝트 부모는 Regdate 이후이거나, 해당일 이후 결제/첨부/로그가 있는 경우 포함}
                            {--board=intraTax : ECMS_Board.B_Board 값}
                            {--only=all : all|intra-tax|project-manages 중 하나}
                            {--reset-project-manages : project_manage_* 4개 테이블을 비우고 project-manages 전체 재이관}
                            {--confirm-reset= : reset 실행 확인 문자열(RESET_PROJECT_MANAGES 입력 필요)}';

    protected $description = '레거시 스테이징 테이블 → intra-tax / project-manages 현행 테이블 이관';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $sinceRaw = $this->option('since');
        $since = is_string($sinceRaw) && $sinceRaw !== '' ? Carbon::parse($sinceRaw)->startOfDay() : null;
        $board = (string) $this->option('board');
        $only = strtolower((string) $this->option('only'));
        $resetProjectManages = (bool) $this->option('reset-project-manages');
        $confirmReset = (string) ($this->option('confirm-reset') ?? '');

        if (! in_array($only, ['all', 'intra-tax', 'project-manages'], true)) {
            $this->error('--only 값은 all, intra-tax, project-manages 중 하나여야 합니다.');

            return self::FAILURE;
        }
        if ($resetProjectManages) {
            if ($only === 'intra-tax') {
                $this->error('--reset-project-manages 는 --only=intra-tax 와 함께 사용할 수 없습니다.');

                return self::FAILURE;
            }
            if ($dryRun) {
                $this->error('--reset-project-manages 는 --dry-run 과 함께 사용할 수 없습니다.');

                return self::FAILURE;
            }
            if ($confirmReset !== 'RESET_PROJECT_MANAGES') {
                $this->error('--reset-project-manages 사용 시 --confirm-reset=RESET_PROJECT_MANAGES 를 함께 입력해 주세요.');

                return self::FAILURE;
            }
        }

        $this->info($dryRun ? '[DRY-RUN] DML은 실행하지 않습니다.' : '이관을 실행합니다.');
        if ($since !== null) {
            $this->info('등록일 컷오프: '.$since->toDateString().' 00:00:00 이상');
        }

        try {
            if ($resetProjectManages) {
                $this->resetProjectManageTables();
            }
            if ($only === 'all' || $only === 'intra-tax') {
                $this->syncIntraTax($board, $since, $dryRun);
            }
            if ($only === 'all' || $only === 'project-manages') {
                $this->syncProjectManages($since, $dryRun);
            }
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('완료');

        return self::SUCCESS;
    }

    private function syncIntraTax(string $board, ?Carbon $since, bool $dryRun): void
    {
        $this->assertTable('ECMS_Board');
        $this->assertTable('intra_tax_posts');

        $postsQuery = DB::table('ECMS_Board as e')
            ->where('e.B_Board', $board)
            ->whereNotExists(function ($q) use ($board) {
                $q->selectRaw('1')
                    ->from('intra_tax_posts as t')
                    ->whereColumn('t.legacy_b_idx', 'e.B_idx')
                    ->where('t.board_code', $board);
            });
        if ($since !== null) {
            $postsQuery->where(function ($q) use ($since) {
                $q->where('e.B_InpDate', '>=', $since)
                    ->orWhere('e.B_ModDate', '>=', $since);
            });
        }

        $postRows = $postsQuery->orderBy('e.B_idx')->get();
        $this->line('intra_tax_posts 예상 삽입: '.$postRows->count());

        if (! $dryRun) {
            foreach ($postRows as $row) {
                $now = now();
                $cat = $this->legacyCategoryValue($row);
                $payload = [
                    'legacy_b_idx' => (int) $row->B_idx,
                    'board_code' => (string) $row->B_Board,
                    'member_id' => $row->M_ID,
                    'author_name' => $row->B_Name ?? '관리자',
                    'password' => $row->B_Password,
                    'title' => (string) $row->B_Title,
                    'content' => (string) ($row->B_Content ?? ''),
                    'has_file' => ($row->B_File ?? 'N') === 'Y' ? 'Y' : 'N',
                    'is_secret' => ($row->B_Secret ?? 'N') === 'Y' ? 'Y' : 'N',
                    'hit' => (int) ($row->B_Hit ?? 0),
                    'ip' => $row->B_IP,
                    'thread_ref' => (int) ($row->B_Ref ?? 0),
                    'thread_step' => (int) ($row->B_Step ?? 0),
                    'thread_level' => (int) ($row->B_Level ?? 0),
                    'comment_count' => (int) ($row->B_Comment ?? 0),
                    'work_state' => (string) ($row->B_State ?? 'R'),
                    'is_notice' => ($row->B_Notice ?? 'N') === 'Y' ? 'Y' : 'N',
                    'email' => $row->B_Email,
                    'category' => $cat,
                    'etc' => $cat,
                    'posted_at' => $this->parseDateTime($row->B_InpDate) ?? $now,
                    'modified_at' => $this->parseDateTime($row->B_ModDate) ?? $this->parseDateTime($row->B_InpDate) ?? $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                foreach (['design', 'program', 'flash'] as $opt) {
                    $legacyCol = 'B_'.ucfirst($opt);
                    if (Schema::hasColumn('ECMS_Board', $legacyCol) && Schema::hasColumn('intra_tax_posts', $opt)) {
                        $payload[$opt] = $row->{$legacyCol} ?? null;
                    }
                }
                $payload = $this->filterToTableColumns('intra_tax_posts', $payload);
                DB::table('intra_tax_posts')->insert($payload);
            }
        }

        if ($this->assertTable('ECMS_Board_Comment', false) && $this->assertTable('intra_tax_post_comments', false)) {
            $this->syncIntraComments($board, $since, $dryRun);
        }

        if ($this->assertTable('ECMS_Board_File', false) && $this->assertTable('intra_tax_post_files', false)) {
            $this->syncIntraFiles($board, $since, $dryRun);
        }

        if (! $dryRun) {
            $this->refreshIntraTaxAggregates($board);
        }
    }

    private function syncIntraComments(string $board, ?Carbon $since, bool $dryRun): void
    {
        $q = DB::table('ECMS_Board_Comment as c')
            ->join('intra_tax_posts as p', function ($join) use ($board) {
                $join->on('p.legacy_b_idx', '=', 'c.B_Idx')
                    ->where('p.board_code', '=', $board)
                    ->where('c.B_Board', '=', $board);
            })
            ->where('c.B_Board', $board)
            ->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('intra_tax_post_comments as x')
                    ->whereColumn('x.intra_tax_post_id', 'p.id')
                    ->whereColumn('x.legacy_c_idx', 'c.C_Idx');
            });

        if ($since !== null) {
            $q->where(function ($w) use ($since) {
                $w->where('c.C_Inpdate', '>=', $since)
                    ->orWhere('p.posted_at', '>=', $since);
            });
        }

        $count = (clone $q)->count();
        $this->line('intra_tax_post_comments 예상 삽입: '.$count);

        if ($dryRun) {
            return;
        }

        $lastIdx = 0;
        while (true) {
            $batch = (clone $q)
                ->where('c.C_Idx', '>', $lastIdx)
                ->orderBy('c.C_Idx')
                ->limit(500)
                ->select(['c.*', 'p.id as intra_tax_post_id'])
                ->get();
            if ($batch->isEmpty()) {
                break;
            }
            $now = now();
            foreach ($batch as $c) {
                DB::table('intra_tax_post_comments')->insert([
                    'intra_tax_post_id' => (int) $c->intra_tax_post_id,
                    'legacy_c_idx' => (int) $c->C_Idx,
                    'member_id' => $c->M_ID,
                    'author_name' => $c->C_Name ?? '관리자',
                    'password' => $c->C_Passwd,
                    'body' => (string) ($c->C_Comment ?? ''),
                    'ip' => $c->C_IP,
                    'posted_at' => $this->parseDateTime($c->C_Inpdate) ?? $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $lastIdx = (int) ($this->legacyRowKeyMap($batch->last())['c_idx'] ?? 0);
        }
    }

    private function syncIntraFiles(string $board, ?Carbon $since, bool $dryRun): void
    {
        $q = DB::table('ECMS_Board_File as f')
            ->join('intra_tax_posts as p', function ($join) use ($board) {
                $join->on('p.legacy_b_idx', '=', 'f.B_Idx')
                    ->where('p.board_code', '=', $board)
                    ->where('f.B_Board', '=', $board);
            })
            ->where('f.B_Board', $board)
            ->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('intra_tax_post_files as x')
                    ->whereColumn('x.intra_tax_post_id', 'p.id')
                    ->whereColumn('x.legacy_f_idx', 'f.F_Idx');
            });

        if ($since !== null) {
            $q->where(function ($w) use ($since) {
                $w->where('f.F_InpDate', '>=', $since)
                    ->orWhere('p.posted_at', '>=', $since);
            });
        }

        $count = (clone $q)->count();
        $this->line('intra_tax_post_files 예상 삽입: '.$count);

        if ($dryRun) {
            return;
        }

        $lastIdx = 0;
        while (true) {
            $batch = (clone $q)
                ->where('f.F_Idx', '>', $lastIdx)
                ->orderBy('f.F_Idx')
                ->limit(500)
                ->select(['f.*', 'p.id as intra_tax_post_id'])
                ->get();
            if ($batch->isEmpty()) {
                break;
            }
            $now = now();
            foreach ($batch as $f) {
                $name = trim((string) ($f->F_Name ?? ''));
                if ($name === '') {
                    continue;
                }
                DB::table('intra_tax_post_files')->insert([
                    'intra_tax_post_id' => (int) $f->intra_tax_post_id,
                    'legacy_f_idx' => (int) $f->F_Idx,
                    'original_name' => $name,
                    'registered_at' => $this->parseDateTime($f->F_InpDate) ?? $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
            $lastIdx = (int) ($this->legacyRowKeyMap($batch->last())['f_idx'] ?? 0);
        }
    }

    private function refreshIntraTaxAggregates(string $board): void
    {
        DB::statement(
            'UPDATE intra_tax_posts p
             LEFT JOIN (
               SELECT intra_tax_post_id, COUNT(*) AS cnt
               FROM intra_tax_post_comments
               GROUP BY intra_tax_post_id
             ) c ON c.intra_tax_post_id = p.id
             LEFT JOIN (
               SELECT intra_tax_post_id, COUNT(*) AS fcnt
               FROM intra_tax_post_files
               GROUP BY intra_tax_post_id
             ) f ON f.intra_tax_post_id = p.id
             SET
               p.comment_count = IFNULL(c.cnt, 0),
               p.has_file = IF(IFNULL(f.fcnt, 0) > 0, \'Y\', p.has_file),
               p.updated_at = ?
             WHERE p.board_code = ?',
            [now(), $board]
        );
        $this->line('intra_tax_posts 집계(comment_count / has_file) 갱신 완료');
    }

    /**
     * 프로젝트 부모: Regdate가 컷오프 이후이거나,
     * 등록일은 옛날이어도 컷오프 이후에 결제/첨부/수정로그가 있으면 이관 대상에 포함한다.
     *
     * @param  \Illuminate\Database\Query\Builder  $q  ProjectManageList as l
     */
    private function applySinceToLegacyProjectManageListQuery($q, Carbon $since): void
    {
        $q->where(function ($w) use ($since) {
            $w->whereRaw('COALESCE(l.Regdate, l.regdate) >= ?', [$since]);
            if (Schema::hasTable('ProjectManageMoneyList')) {
                $w->orWhereExists(function ($sub) use ($since) {
                    $sub->selectRaw('1')
                        ->from('ProjectManageMoneyList as m')
                        ->whereColumn('m.ProjectIdx', 'l.idx')
                        ->whereRaw('COALESCE(m.RegDate, m.regdate) >= ?', [$since]);
                });
            }
            if (Schema::hasTable('ProjectAttachFileList')) {
                $w->orWhereExists(function ($sub) use ($since) {
                    $sub->selectRaw('1')
                        ->from('ProjectAttachFileList as a')
                        ->whereColumn('a.ProjectIdx', 'l.idx')
                        ->whereRaw('COALESCE(a.regdate, a.Regdate) >= ?', [$since]);
                });
            }
            if (Schema::hasTable('ProjectManageModifyLogList')) {
                $w->orWhereExists(function ($sub) use ($since) {
                    $sub->selectRaw('1')
                        ->from('ProjectManageModifyLogList as log')
                        ->whereColumn('log.ProjectIdx', 'l.idx')
                        ->whereRaw('COALESCE(log.Regdate, log.regdate) >= ?', [$since]);
                });
            }
        });
    }

    private function syncProjectManages(?Carbon $since, bool $dryRun): void
    {
        $this->assertTable('ProjectManageList');
        $this->assertTable('project_manages');

        $targetCols = Schema::getColumnListing('project_manages');

        $q = DB::table('ProjectManageList as l')
            ->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('project_manages as p')
                    ->whereColumn('p.id', 'l.idx');
            });

        if ($since !== null) {
            $this->applySinceToLegacyProjectManageListQuery($q, $since);
        }

        $rows = $q->orderBy('l.idx')->get();
        $this->line('project_manages 예상 삽입: '.$rows->count());

        $newProjectIdx = $rows->map(fn ($legacy) => (int) ($this->legacyRowKeyMap($legacy)['idx'] ?? 0))->filter(static fn (int $v) => $v > 0)->values();
        $eligibleChildProjects = $this->eligibleProjectIdsForChildRows($newProjectIdx);

        if ($dryRun) {
            $this->dryRunProjectChildren($since, $eligibleChildProjects);

            return;
        }

        $now = now();
        foreach ($rows as $legacy) {
            $legacyVars = $this->legacyRowKeyMap($legacy);
            $payload = [];
            foreach ($targetCols as $col) {
                if (in_array($col, ['id', 'created_at', 'updated_at', 'legacy_idx'], true)) {
                    continue;
                }
                $lk = strtolower($col);
                if (! array_key_exists($lk, $legacyVars)) {
                    continue;
                }
                $payload[$col] = $legacyVars[$lk];
            }
            $payload['id'] = (int) ($legacyVars['idx'] ?? 0);
            $payload['created_at'] = $now;
            $payload['updated_at'] = $now;
            if (Schema::hasColumn('project_manages', 'legacy_idx')) {
                $payload['legacy_idx'] = (int) ($legacyVars['idx'] ?? 0);
            }
            $payload = $this->filterToTableColumns('project_manages', $payload);
            DB::table('project_manages')->insert($payload);
        }
        if (! $dryRun) {
            $this->line('project_manages 신규 삽입: '.$rows->count().'건');
        }

        $this->syncProjectPayments($since, $dryRun);
        $this->syncProjectModifyLogs($since, $dryRun);
        $this->syncProjectAttachFiles($since, $dryRun);
    }

    /**
     * 자식 행(결제·로그·첨부)은 부모 프로젝트가 현행에 있어야 하므로,
     * 이미 존재하는 project_manages.id ∪ 이번에 넣을 레거시 idx 를 후보로 둔다.
     *
     * @param  Collection<int, int>  $newProjectIdx
     * @return array<int, int>
     */
    private function eligibleProjectIdsForChildRows(Collection $newProjectIdx): array
    {
        $existing = DB::table('project_manages')->pluck('id')->map(static fn ($v) => (int) $v);

        return $existing->merge($newProjectIdx)->unique()->values()->all();
    }

    private function dryRunProjectChildren(?Carbon $since, array $eligibleProjectIds): void
    {
        if ($eligibleProjectIds === []) {
            return;
        }
        if ($this->assertTable('ProjectManageMoneyList', false) && $this->assertTable('project_manage_payments', false)) {
            $q = DB::table('ProjectManageMoneyList as m')
                ->whereIn('m.ProjectIdx', $eligibleProjectIds)
                ->whereNotExists(fn ($s) => $this->paymentExistsSubquery($s, 'm'));
            if ($since !== null) {
                $q->whereRaw('COALESCE(m.RegDate, m.regdate) >= ?', [$since]);
            }
            $this->line('project_manage_payments 예상 삽입: '.$q->count());
        }
        if ($this->assertTable('ProjectManageModifyLogList', false) && $this->assertTable('project_manage_modify_logs', false)) {
            $q = DB::table('ProjectManageModifyLogList as l')
                ->whereIn('l.ProjectIdx', $eligibleProjectIds)
                ->whereNotExists(fn ($s) => $this->modifyLogExistsSubquery($s, 'l'));
            if ($since !== null) {
                $q->whereRaw('COALESCE(l.Regdate, l.regdate) >= ?', [$since]);
            }
            $this->line('project_manage_modify_logs 예상 삽입: '.$q->count());
        }
        if ($this->assertTable('ProjectAttachFileList', false) && $this->assertTable('project_manage_files', false)) {
            $q = DB::table('ProjectAttachFileList as a')
                ->whereIn('a.ProjectIdx', $eligibleProjectIds)
                ->whereNotExists(fn ($s) => $this->attachExistsSubquery($s, 'a'));
            if ($since !== null) {
                $q->whereRaw('COALESCE(a.regdate, a.Regdate) >= ?', [$since]);
            }
            $this->line('project_manage_files 예상 삽입: '.$q->count());
        }
    }

    private function syncProjectPayments(?Carbon $since, bool $dryRun): void
    {
        if (! $this->assertTable('ProjectManageMoneyList', false) || ! $this->assertTable('project_manage_payments', false)) {
            return;
        }
        if ($dryRun) {
            return;
        }

        $q = DB::table('ProjectManageMoneyList as m')
            ->join('project_manages as p', 'p.id', '=', 'm.ProjectIdx')
            ->select('m.*')
            ->whereNotExists(fn ($s) => $this->paymentExistsSubquery($s, 'm'));
        if ($since !== null) {
            $q->whereRaw('COALESCE(m.RegDate, m.regdate) >= ?', [$since]);
        }

        $now = now();
        $hasLegacyMoney = Schema::hasColumn('project_manage_payments', 'legacy_money_idx');
        $paymentCols = Schema::getColumnListing('project_manage_payments');
        $skipFillFromLegacy = ['id', 'legacy_idx', 'created_at', 'updated_at'];

        $lastIdx = 0;
        $inserted = 0;
        while (true) {
            $chunk = (clone $q)
                ->where('m.idx', '>', $lastIdx)
                ->orderBy('m.idx')
                ->limit(300)
                ->get();
            if ($chunk->isEmpty()) {
                break;
            }
            foreach ($chunk as $m) {
                $mv = $this->legacyRowKeyMap($m);
                $pid = (int) ($mv['projectidx'] ?? 0);
                if ($pid < 1) {
                    continue;
                }
                $reg = $this->parseDateTime($mv['regdate'] ?? null) ?? $now;
                $row = [
                    'project_manage_id' => $pid,
                    'Money' => $mv['money'] ?? null,
                    'PaymentDate' => $mv['paymentdate'] ?? null,
                    'Memo' => $mv['memo'] ?? null,
                    'RegDate' => $reg,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if ($hasLegacyMoney) {
                    $row['legacy_money_idx'] = (int) ($mv['idx'] ?? 0);
                }
                foreach ($paymentCols as $col) {
                    if (array_key_exists($col, $row) || in_array($col, $skipFillFromLegacy, true)) {
                        continue;
                    }
                    $lk = strtolower($col);
                    if (array_key_exists($lk, $mv)) {
                        $row[$col] = $mv[$lk];
                    }
                }
                $row = $this->filterToTableColumns('project_manage_payments', $row);
                DB::table('project_manage_payments')->insert($row);
                $inserted++;
            }
            $lastIdx = (int) ($this->legacyRowKeyMap($chunk->last())['idx'] ?? 0);
        }
        $this->line('project_manage_payments 신규 삽입: '.$inserted.'건');
    }

    private function paymentExistsSubquery($sub, string $alias): void
    {
        $sub->from('project_manage_payments as t')
            ->selectRaw('1')
            ->whereColumn('t.project_manage_id', $alias.'.ProjectIdx');
        if (Schema::hasColumn('project_manage_payments', 'legacy_money_idx')) {
            $sub->whereColumn('t.legacy_money_idx', $alias.'.idx');

            return;
        }
        $eq = static fn (string $c): string => '`'.$alias.'`.`'.$c.'`';
        $sub->whereRaw('t.`RegDate` <=> '.$eq('RegDate'))
            ->whereRaw('t.`Money` <=> '.$eq('Money'))
            ->whereRaw('t.`PaymentDate` <=> '.$eq('PaymentDate'));
    }

    private function syncProjectModifyLogs(?Carbon $since, bool $dryRun): void
    {
        if (! $this->assertTable('ProjectManageModifyLogList', false) || ! $this->assertTable('project_manage_modify_logs', false)) {
            return;
        }
        if ($dryRun) {
            return;
        }

        $q = DB::table('ProjectManageModifyLogList as l')
            ->join('project_manages as p', 'p.id', '=', 'l.ProjectIdx')
            ->select('l.*')
            ->whereNotExists(fn ($s) => $this->modifyLogExistsSubquery($s, 'l'));
        if ($since !== null) {
            $q->whereRaw('COALESCE(l.Regdate, l.regdate) >= ?', [$since]);
        }

        $now = now();
        $lastIdx = 0;
        $inserted = 0;
        while (true) {
            $chunk = (clone $q)
                ->where('l.idx', '>', $lastIdx)
                ->orderBy('l.idx')
                ->limit(300)
                ->get();
            if ($chunk->isEmpty()) {
                break;
            }
            foreach ($chunk as $l) {
                $lv = $this->legacyRowKeyMap($l);
                $projectIdx = (int) ($lv['projectidx'] ?? 0);
                $proj = DB::table('project_manages')->where('id', $projectIdx)->first();
                if ($proj === null) {
                    continue;
                }
                $legacyIdxVal = property_exists($proj, 'legacy_idx') ? (int) $proj->legacy_idx : $projectIdx;
                $row = [
                    'project_manage_id' => $projectIdx,
                    'legacy_project_idx' => $legacyIdxVal,
                    'project_name' => $lv['projectname'] ?? ($proj->ProjectName ?? null),
                    'company_name' => $proj !== null ? ($proj->CompanyName ?? null) : null,
                    'user_name' => (string) ($lv['username'] ?? '관리자'),
                    'memo' => (string) ($lv['memo'] ?? ''),
                    'regdate' => $this->parseDateTime($lv['regdate'] ?? null) ?? $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                if (Schema::hasColumn('project_manage_modify_logs', 'legacy_modify_idx')) {
                    $row['legacy_modify_idx'] = (int) ($lv['idx'] ?? 0);
                }
                $row = $this->filterToTableColumns('project_manage_modify_logs', $row);
                DB::table('project_manage_modify_logs')->insert($row);
                $inserted++;
            }
            $lastIdx = (int) ($this->legacyRowKeyMap($chunk->last())['idx'] ?? 0);
        }
        $this->line('project_manage_modify_logs 신규 삽입: '.$inserted.'건');
    }

    private function modifyLogExistsSubquery($sub, string $alias): void
    {
        $sub->from('project_manage_modify_logs as t')
            ->selectRaw('1')
            ->whereColumn('t.project_manage_id', $alias.'.ProjectIdx');
        if (Schema::hasColumn('project_manage_modify_logs', 'legacy_modify_idx')) {
            $sub->whereColumn('t.legacy_modify_idx', $alias.'.idx');

            return;
        }
        $sub->where(function ($w) use ($alias) {
            $w->whereColumn('t.regdate', $alias.'.Regdate')
                ->orWhere(function ($w2) use ($alias) {
                    $w2->whereNull('t.regdate')->whereNull($alias.'.Regdate');
                });
        })
            ->whereColumn('t.user_name', $alias.'.UserName')
            ->whereColumn('t.memo', $alias.'.Memo');
    }

    private function syncProjectAttachFiles(?Carbon $since, bool $dryRun): void
    {
        if (! $this->assertTable('ProjectAttachFileList', false) || ! $this->assertTable('project_manage_files', false)) {
            return;
        }
        if ($dryRun) {
            return;
        }

        $q = DB::table('ProjectAttachFileList as a')
            ->join('project_manages as p', 'p.id', '=', 'a.ProjectIdx')
            ->select('a.*')
            ->whereNotExists(fn ($s) => $this->attachExistsSubquery($s, 'a'));
        if ($since !== null) {
            $q->whereRaw('COALESCE(a.regdate, a.Regdate) >= ?', [$since]);
        }

        $now = now();
        $lastIdx = 0;
        $inserted = 0;
        while (true) {
            $chunk = (clone $q)
                ->where('a.idx', '>', $lastIdx)
                ->orderBy('a.idx')
                ->limit(300)
                ->get();
            if ($chunk->isEmpty()) {
                break;
            }
            foreach ($chunk as $a) {
                $av = $this->legacyRowKeyMap($a);
                $names = LegacyAttachmentFileNames::split((string) ($av['filename'] ?? ''));
                if ($names === []) {
                    continue;
                }

                foreach ($names as $name) {
                    $stored = 'project-manages/'.$name;
                    $row = [
                        'project_manage_id' => (int) ($av['projectidx'] ?? 0),
                        'stored_path' => $stored,
                        'original_name' => $name,
                        'size_bytes' => null,
                        'regdate' => $this->parseDateTime($av['regdate'] ?? null) ?? $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    if (Schema::hasColumn('project_manage_files', 'legacy_idx')) {
                        $row['legacy_idx'] = (int) ($av['idx'] ?? 0);
                    }
                    if (Schema::hasColumn('project_manage_files', 'legacy_project_idx')) {
                        $row['legacy_project_idx'] = (int) ($av['projectidx'] ?? 0);
                    }
                    if (Schema::hasColumn('project_manage_files', 'legacy_attach_idx')) {
                        $row['legacy_attach_idx'] = (int) ($av['idx'] ?? 0);
                    }
                    $row = $this->filterToTableColumns('project_manage_files', $row);
                    DB::table('project_manage_files')->insert($row);
                    $inserted++;
                }
            }
            $lastIdx = (int) ($this->legacyRowKeyMap($chunk->last())['idx'] ?? 0);
        }
        $this->line('project_manage_files 신규 삽입: '.$inserted.'건');
    }

    private function attachExistsSubquery($sub, string $alias): void
    {
        $sub->from('project_manage_files as t')
            ->selectRaw('1')
            ->whereColumn('t.project_manage_id', $alias.'.ProjectIdx')
            ->where(function ($w) use ($alias) {
                $w->whereColumn('t.original_name', $alias.'.FileName');
                if (Schema::hasColumn('project_manage_files', 'legacy_idx')) {
                    $w->orWhereColumn('t.legacy_idx', $alias.'.idx');
                }
                if (Schema::hasColumn('project_manage_files', 'legacy_attach_idx')) {
                    $w->orWhereColumn('t.legacy_attach_idx', $alias.'.idx');
                }
            });
    }

    /**
     * PDO stdClass 키 대소문자 차이를 흡수한다.
     *
     * @return array<string, mixed>
     */
    private function legacyRowKeyMap(object $row): array
    {
        $out = [];
        foreach (get_object_vars($row) as $k => $v) {
            $out[strtolower((string) $k)] = $v;
        }

        return $out;
    }

    private function legacyCategoryValue(object $row): ?string
    {
        foreach (['B_Etc', 'B_Category', 'B_Class', 'B_Type', 'B_Program'] as $col) {
            if (property_exists($row, $col) && trim((string) ($row->{$col} ?? '')) !== '') {
                return trim((string) $row->{$col});
            }
        }

        return null;
    }

    private function parseDateTime(mixed $v): ?Carbon
    {
        if ($v === null || $v === '') {
            return null;
        }
        try {
            return Carbon::parse($v);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function filterToTableColumns(string $table, array $payload): array
    {
        $cols = array_flip(Schema::getColumnListing($table));
        $out = [];
        foreach ($payload as $k => $v) {
            if (isset($cols[$k])) {
                $out[$k] = $v;
            }
        }

        return $out;
    }

    private function assertTable(string $table, bool $throw = true): bool
    {
        if (Schema::hasTable($table)) {
            return true;
        }
        if ($throw) {
            throw new \RuntimeException("필수 테이블이 없습니다: {$table}");
        }

        return false;
    }

    /**
     * project-manages 재이관 전 기존 현행 데이터를 모두 비운다.
     * FK 제약이 있을 수 있어 자식 -> 부모 순서로 TRUNCATE 한다.
     */
    private function resetProjectManageTables(): void
    {
        $targets = [
            'project_manage_files',
            'project_manage_payments',
            'project_manage_modify_logs',
            'project_manages',
        ];

        $this->warn('[주의] project-manages 현행 테이블 전량 삭제를 시작합니다.');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($targets as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                DB::table($table)->truncate();
                $this->line(" - {$table} 비움 완료");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        $this->warn('project-manages 테이블 초기화 완료');
    }
}
