<?php

namespace App\Services\Backoffice;

use App\Models\ProjectProvision;
use App\Services\AnthropicIaPlanner;
use Illuminate\Support\Facades\Storage;
use PDO;
use RuntimeException;
use Throwable;

class JavaIaApplyService
{
    public function apply(ProjectProvision $provision): void
    {
        $path = Storage::disk('local')->path((string) $provision->ia_file_path);
        if (! is_file($path)) {
            $this->finish($provision, 'IA 파일을 찾지 못했습니다.');

            return;
        }

        try {
            $rows = (new IaWorkbookReader())->rows($path);
        } catch (RuntimeException $exception) {
            $this->finish($provision, $exception->getMessage());

            return;
        }

        if ($rows === []) {
            $this->finish($provision, 'IA에서 적용할 화면을 찾지 못했습니다.');

            return;
        }

        try {
            $items = (new AnthropicIaPlanner())->plan($rows);
            $boards = $this->boards($items);
            if ($boards === []) {
                $this->finish($provision, '적용할 화면이 없습니다.');

                return;
            }

            $created = $this->writeBoards($provision, $boards);
            $this->finish($provision, $created === [] ? '적용할 화면이 없습니다.' : implode("\n", $created));
        } catch (Throwable $exception) {
            throw $exception instanceof RuntimeException
                ? $exception
                : new RuntimeException('자바 관리자에 IA를 넣지 못했습니다.');
        }
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array{parent: string, name: string, fields: list<array{label: string, type: string}>}>
     */
    private function boards(array $items): array
    {
        $boards = [];
        $seen = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $depth1 = trim((string) ($item['depth1'] ?? ''));
            $depth2 = trim((string) ($item['depth2'] ?? ''));
            if (in_array($depth1, ['대시보드', '로그인'], true) || in_array($depth2, ['대시보드', '로그인'], true)) {
                continue;
            }
            if ($depth1 !== '' && $depth2 !== '' && $depth2 !== $depth1 && preg_match('/등록|상세|환불/u', $depth2)) {
                continue;
            }
            if (in_array($depth2, ['삭제', '삭제/선택삭제', '삭제 / 선택삭제'], true)) {
                continue;
            }

            $name = trim((string) ($item['name'] ?? ''));
            if ($name === '') {
                $name = $this->label($depth1, $depth2);
            }
            if ($name === '' || isset($seen[$name]) || $name === '대시보드' || $name === '로그인') {
                continue;
            }
            $seen[$name] = true;

            $fields = [];
            foreach ((array) ($item['fields'] ?? []) as $field) {
                if (! is_array($field)) {
                    continue;
                }
                $fieldLabel = trim((string) ($field['label'] ?? ''));
                if ($fieldLabel === '' || in_array($fieldLabel, ['제목', '내용', '본문'], true)) {
                    continue;
                }
                $type = (string) ($field['type'] ?? 'text');
                if (str_contains($fieldLabel, '썸네일') || str_contains($fieldLabel, '파일') || str_contains($fieldLabel, '첨부')) {
                    $type = 'file';
                }
                $fields[] = ['label' => $fieldLabel, 'type' => $type];
            }

            $boards[] = [
                'parent' => $depth1 !== '' ? $depth1 : '콘텐츠',
                'name' => $name,
                'fields' => $fields,
            ];
        }

        return $boards;
    }

    private function label(string $depth1, string $depth2): string
    {
        $types = ['리스트', '목록', '상세', '등록', '수정', '삭제', '등록 / 수정', '등록/수정', '삭제/선택삭제'];
        if ($depth2 !== '' && ! in_array($depth2, $types, true)) {
            return $depth2;
        }
        if ($depth2 === '' || in_array($depth2, ['리스트', '목록'], true)) {
            return $depth1;
        }

        return trim($depth1.' '.$depth2);
    }

    /**
     * @param  list<array{parent: string, name: string, fields: list<array{label: string, type: string}>}>  $boards
     * @return list<string>
     */
    private function writeBoards(ProjectProvision $provision, array $boards): array
    {
        $pdo = $this->pdo($provision);
        $this->clearGenerated($pdo);

        $groups = [];
        foreach ($boards as $board) {
            $groups[$board['parent']][] = $board;
        }

        $usedParents = $this->usedParentNumbers($pdo);
        $usedChildren = array_fill_keys($pdo->query("SELECT CODE FROM CODE_DT WHERE CODE_ID = 'COM002'")->fetchAll(PDO::FETCH_COLUMN), true);
        $created = [];
        $parentSeq = 0;
        foreach ($groups as $parentName => $children) {
            $parentNumber = $this->nextParentNumber($usedParents);
            $usedParents[$parentNumber] = true;
            $parentCode = sprintf('%02d', $parentNumber);
            $parentSeq++;
            $this->insertCode($pdo, 'COM001', $parentCode, $this->cut($parentName, 60), '', $parentSeq);

            $childSeq = 0;
            foreach ($children as $board) {
                do {
                    $childSeq++;
                    $menuCode = sprintf('%02d%02d', $parentNumber, $childSeq);
                } while ($childSeq <= 99 && isset($usedChildren[$menuCode]));
                if ($childSeq > 99) {
                    $created[] = $parentName.': 하위 메뉴는 99개까지만 넣었습니다.';
                    break;
                }
                $usedChildren[$menuCode] = true;
                $bbsId = $this->bbsId($pdo);
                $this->insertBoard($pdo, $bbsId, $board['name'], $board['fields']);
                $this->insertCode($pdo, 'COM002', $menuCode, $this->cut($board['name'], 60), '/admin/bbs-post/'.$bbsId, $childSeq);
                $this->grant($pdo, $parentCode);
                $this->grant($pdo, $menuCode);
                $created[] = $parentName.' > '.$board['name'];
                if (count($board['fields']) > 5) {
                    $created[] = $board['name'].': 추가 필드는 5개까지만 게시판에 넣었습니다.';
                }
            }
        }

        return $created;
    }

    private function pdo(ProjectProvision $provision): PDO
    {
        $database = (string) $provision->db_database;
        $user = (string) $provision->db_username;
        if (! preg_match('/^[a-z][a-z0-9-]{1,30}$/', $database) || ! preg_match('/^[a-z][a-z0-9-]{1,30}$/', $user)) {
            throw new RuntimeException('데이터베이스 정보가 올바르지 않습니다.');
        }

        try {
            return new PDO(
                'mysql:host=127.0.0.1;dbname='.$database.';charset=utf8mb4',
                $user,
                (string) $provision->db_password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (Throwable) {
            throw new RuntimeException('자바 데이터베이스에 연결하지 못했습니다.');
        }
    }

    private function clearGenerated(PDO $pdo): void
    {
        $codes = $pdo->query("SELECT CODE FROM CODE_DT WHERE CODE_ETC1 = 'ia'")->fetchAll(PDO::FETCH_COLUMN);
        if ($codes !== []) {
            $marks = implode(',', array_fill(0, count($codes), '?'));
            $pdo->prepare('DELETE FROM AUTH_INFO WHERE MENU_CODE IN ('.$marks.')')->execute($codes);
            $pdo->prepare("DELETE FROM CODE_DT WHERE CODE_ETC1 = 'ia'")->execute();
        }

        $ids = $pdo->query("SELECT BBS_ID FROM BBS_MASTER WHERE REGID = 'ia'")->fetchAll(PDO::FETCH_COLUMN);
        if ($ids === []) {
            return;
        }
        $marks = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare('DELETE FROM BBS_POST WHERE BBS_ID IN ('.$marks.')')->execute($ids);
        $pdo->prepare("DELETE FROM BBS_MASTER WHERE REGID = 'ia'")->execute();
    }

    /**
     * @return array<int, bool>
     */
    private function usedParentNumbers(PDO $pdo): array
    {
        $used = [];
        $codes = $pdo->query("SELECT CODE FROM CODE_DT WHERE CODE_ID = 'COM001'")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($codes as $code) {
            if (preg_match('/^[0-9]{2}$/', (string) $code)) {
                $used[(int) $code] = true;
            }
        }

        return $used;
    }

    /**
     * @param  array<int, bool>  $used
     */
    private function nextParentNumber(array $used): int
    {
        for ($number = 15; $number <= 99; $number++) {
            if (! isset($used[$number])) {
                return $number;
            }
        }

        throw new RuntimeException('관리자 메뉴를 더 추가할 수 없습니다.');
    }

    private function insertCode(PDO $pdo, string $codeId, string $code, string $name, string $url, int $seq): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO CODE_DT (CODE_ID, CODE, CODE_NM, CODE_DC, SEQ, USE_YN, CODE_ETC1, CODE_ETC2, CODE_ETC3, CODE_FILE, REGDT, REGEMP)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)'
        );
        $stmt->execute([$codeId, $code, $name, $url, $seq, 'Y', 'ia', '', '', '', 'ia']);
    }

    /**
     * @param  list<array{label: string, type: string}>  $fields
     */
    private function insertBoard(PDO $pdo, string $bbsId, string $name, array $fields): void
    {
        $extras = [];
        $file = false;
        $thumb = false;
        foreach ($fields as $field) {
            if ($field['type'] === 'file') {
                $file = true;
                if (str_contains($field['label'], '썸네일')) {
                    $thumb = true;
                }
                continue;
            }
            if (count($extras) < 5) {
                $extras[] = $field;
            }
        }

        $columns = [
            'BBS_ID' => $bbsId,
            'BBS_NM' => $this->cut($name, 100),
            'BBS_CN' => '',
            'BBS_SKIN' => 'LIST_BASIC',
            'BBS_IMG' => '',
            'PG_UNIT' => 10,
            'FILE_YN' => $file ? 'Y' : 'N',
            'FILE_CNT' => $file ? 5 : 0,
            'FILE_SIZE' => $file ? 10 : 0,
            'USE_YN' => 'Y',
            'REP_YN' => 'N',
            'CMT_YN' => 'N',
            'SEQ_YN' => 'N',
            'MAIN_YN' => 'N',
            'TOP_YN' => 'N',
            'THUM_YN' => $thumb ? 'Y' : 'N',
            'LINK_YN' => 'N',
            'HIDE_YN' => 'N',
            'LOCK_YN' => 'N',
            'NEW_YN' => 'N',
            'NEW_NO' => 0,
            'HOT_YN' => 'N',
            'HOT_NO' => 0,
            'CATE_YN' => 'N',
            'CATE_CD' => '',
        ];
        for ($index = 1; $index <= 5; $index++) {
            $field = $extras[$index - 1] ?? null;
            $columns['ETC'.$index.'_YN'] = $field ? 'Y' : 'N';
            $columns['ETC'.$index.'_NM'] = $field ? $this->cut($field['label'], 50) : '';
            $columns['ETC'.$index.'_TP'] = 'input';
            $columns['ETC'.$index.'_CD'] = '';
        }
        $columns['POSBL_LIST'] = 'Y';
        $columns['POSBL_VIEW'] = 'Y';
        $columns['POSBL_WRITE'] = 'Y';
        $columns['REGID'] = 'ia';
        $columns['MODID'] = 'ia';

        $names = array_keys($columns);
        $stmt = $pdo->prepare(
            'INSERT INTO BBS_MASTER ('.implode(', ', $names).', REGDT, MODDT) VALUES ('.implode(', ', array_fill(0, count($names), '?')).', NOW(), NOW())'
        );
        $stmt->execute(array_values($columns));
    }

    private function grant(PDO $pdo, string $menuCode): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO AUTH_INFO (GROUP_ID, MENU_CODE, REGEMP, REGDT)
             SELECT ?, ?, ?, NOW() FROM DUAL
             WHERE NOT EXISTS (SELECT 1 FROM AUTH_INFO WHERE GROUP_ID = ? AND MENU_CODE = ?)'
        );
        foreach (['SUPER', 'MANAGER'] as $group) {
            $stmt->execute([$group, $menuCode, 'ia', $group, $menuCode]);
        }
    }

    private function bbsId(PDO $pdo): string
    {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM BBS_MASTER WHERE BBS_ID = ?');
        for ($try = 0; $try < 20; $try++) {
            $id = '';
            for ($index = 0; $index < 5; $index++) {
                $id .= chr(random_int(65, 90));
            }
            $stmt->execute([$id]);
            if ((int) $stmt->fetchColumn() === 0) {
                return $id;
            }
        }

        throw new RuntimeException('게시판 아이디를 만들지 못했습니다.');
    }

    private function cut(string $value, int $length): string
    {
        return mb_substr($value, 0, $length);
    }

    private function finish(ProjectProvision $provision, string $message): void
    {
        if (strlen($message) > 8000) {
            $message = substr($message, 0, 8000);
        }

        $provision->ia_apply_note = $message;
        $provision->log = trim((string) $provision->log."\n".$message);
        $provision->save();
    }
}
