<?php

namespace App\Services\Backoffice;

use App\Models\ProjectProvision;
use Illuminate\Support\Facades\Process;
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
            require_once base_path('scripts/ia-list-screen.php');
            $boards = $this->screens($rows);
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
     * @param  list<array{depth1: string, depth2: string, depth3: string, style: string, access: string, description: string}>  $rows
     * @return list<array{parent: string, name: string, list: array<string, mixed>|null, form: list<array<string, mixed>>, single: bool, save_label: string}>
     */
    private function screens(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $depth1 = trim((string) ($row['depth1'] ?? ''));
            $depth2 = trim((string) ($row['depth2'] ?? ''));
            $depth3 = trim((string) ($row['depth3'] ?? ''));
            $description = trim((string) ($row['description'] ?? ''));
            if ($description === '' || in_array($depth1, ['로그인', '대시보드'], true) || in_array($depth2, ['로그인', '대시보드'], true)) {
                continue;
            }
            if (in_array($depth2, ['삭제', '삭제/선택삭제', '삭제 / 선택삭제'], true) || in_array($depth3, ['삭제', '삭제/선택삭제', '삭제 / 선택삭제'], true)) {
                continue;
            }

            $name = ia_row_label($depth1, $depth2);
            if ($name === '' || in_array($name, ['로그인', '대시보드', 'Register', 'Announcements'], true)) {
                continue;
            }

            $parent = $depth1 !== '' ? $depth1 : '콘텐츠';
            $key = $parent."\0".$name;
            $groups[$key]['parent'] = $parent;
            $groups[$key]['name'] = $name;
            $groups[$key]['rows'][] = $row;
        }

        $screens = [];
        foreach ($groups as $group) {
            $listText = '';
            $formText = '';
            foreach ($group['rows'] as $row) {
                $description = trim((string) ($row['description'] ?? ''));
                $marker = (string) ($row['depth2'] ?? '').(string) ($row['depth3'] ?? '');
                $isList = ($row['depth3'] ?? '') === '리스트' || str_contains($description, '리스트:');
                if ($isList && $listText === '') {
                    $listText = $description;
                } elseif (! $isList && ! preg_match('/환불/u', $marker)) {
                    $formText .= "\n".$description;
                }
            }

            $spec = $listText !== '' ? ia_list_spec($listText) : null;
            $items = ia_description_items($formText);
            $hasInputs = ia_description_has_inputs($items);
            $calendar = ia_is_calendar_list($listText."\n".$formText);
            if ($spec === null && ! $hasInputs && ! $calendar) {
                continue;
            }

            $form = [];
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $kind = (string) ($item['kind'] ?? '');
                if ($kind === '') {
                    continue;
                }
                if ($kind === 'heading' && preg_match('/(강사|강의)\s*추가/u', (string) ($item['label'] ?? ''))) {
                    $kind = 'popup';
                    $item['name'] = str_contains((string) $item['label'], '강의') ? 'lecture' : 'instructor';
                    $item['wide'] = false;
                }
                $form[] = [
                    'kind' => $kind,
                    'label' => (string) ($item['label'] ?? ''),
                    'name' => (string) ($item['name'] ?? ''),
                    'options' => array_values(array_map('strval', (array) ($item['options'] ?? []))),
                    'hint' => (string) ($item['hint'] ?? ''),
                    'start_name' => (string) ($item['start_name'] ?? ''),
                    'end_name' => (string) ($item['end_name'] ?? ''),
                    'start_label' => (string) ($item['start_label'] ?? ''),
                    'end_label' => (string) ($item['end_label'] ?? ''),
                    'wide' => $kind === 'popup' ? false : ia_field_is_wide($item),
                    'width' => ia_input_max_width($item),
                ];
            }

            if ($form === [] && is_array($spec)) {
                foreach ($spec['columns'] as $column) {
                    if (! is_array($column)) {
                        continue;
                    }
                    $item = [
                        'kind' => 'text',
                        'label' => (string) ($column['label'] ?? ''),
                        'name' => (string) ($column['name'] ?? ''),
                        'options' => [],
                    ];
                    $form[] = [
                        'kind' => 'text',
                        'label' => $item['label'],
                        'name' => $item['name'],
                        'options' => [],
                        'hint' => '',
                        'start_name' => '',
                        'end_name' => '',
                        'start_label' => '',
                        'end_label' => '',
                        'wide' => ia_field_is_wide($item),
                        'width' => ia_input_max_width($item),
                    ];
                }
            }

            if ($spec === null && $form !== [] && $calendar) {
                $columns = [];
                foreach ($form as $field) {
                    if ($field['kind'] !== 'text' || count($columns) >= 6) {
                        continue;
                    }
                    $columns[] = ['label' => $field['label'], 'name' => $field['name'], 'source' => 'custom'];
                }
                $spec = [
                    'columns' => $columns,
                    'actions' => ['수정', '삭제'],
                    'toolbar' => ['bulk_delete', 'create'],
                    'keyword_options' => [],
                    'selects' => [],
                    'date_filters' => [],
                    'checkbox' => true,
                    'show_keyword' => true,
                    'show_per_page' => false,
                    'show_number' => false,
                    'date_label' => '',
                    'date_field' => '',
                    'status_field' => 'status',
                ];
            }

            if (is_array($spec)) {
                foreach ($spec['columns'] as $index => $column) {
                    if (! is_array($column)) {
                        continue;
                    }
                    foreach ($form as $field) {
                        if ($this->plain((string) $field['label']) === $this->plain((string) ($column['label'] ?? ''))) {
                            $spec['columns'][$index]['name'] = $field['name'];
                        }
                    }
                }
            }

            $screens[] = [
                'parent' => $group['parent'],
                'name' => $group['name'],
                'list' => $spec,
                'form' => $form,
                'single' => $spec === null && ! $calendar,
                'save_label' => str_contains($formText, '확인') ? '확인' : '저장',
            ];
        }

        return $screens;
    }

    private function plain(string $value): string
    {
        return preg_replace('/\s+/u', '', $value) ?? $value;
    }

    /**
     * @param  list<array{parent: string, name: string, list: array<string, mixed>|null, form: list<array<string, mixed>>, single: bool, save_label: string}>  $boards
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
        $screens = [];
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
                $this->insertBoard($pdo, $bbsId, $board['name'], []);
                $this->insertCode($pdo, 'COM002', $menuCode, $this->cut($board['name'], 60), '/admin/bbs-post/'.$bbsId, $childSeq);
                $this->grant($pdo, $parentCode);
                $this->grant($pdo, $menuCode);
                $screens[$bbsId] = [
                    'name' => $board['name'],
                    'parent' => $parentName,
                    'single' => $board['single'],
                    'save_label' => $board['save_label'],
                    'list' => $board['list'],
                    'form' => $board['form'],
                ];
                if ($board['name'] === '회원목록') {
                    $this->seedMember($pdo, $bbsId, $board['form']);
                }
                $created[] = $parentName.' > '.$board['name'];
            }
        }

        $this->writeScreenFile($provision, $screens);

        return $created;
    }

    /**
     * @param  array<string, array<string, mixed>>  $screens
     */
    private function writeScreenFile(ProjectProvision $provision, array $screens): void
    {
        $name = (string) $provision->project_name;
        if (! preg_match('/^[a-z][a-z0-9-]{1,30}$/', $name)) {
            throw new RuntimeException('퍼블 화면 파일을 만들지 못했습니다.');
        }

        $json = json_encode(['screens' => $screens], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $temp = tempnam(sys_get_temp_dir(), 'iascreen');
        if ($temp === false || file_put_contents($temp, $json) === false) {
            throw new RuntimeException('퍼블 화면 파일을 만들지 못했습니다.');
        }

        $target = rtrim((string) config('provision.base_path'), '/').'/pub'.$name.'/www-adm/ia-screens.json';
        $owner = 'pub'.(string) $provision->ftp_username;
        $copy = Process::timeout(30)->run(['sudo', '-n', 'cp', $temp, $target]);
        @unlink($temp);
        if (! $copy->successful()) {
            throw new RuntimeException('퍼블 화면 파일을 복사하지 못했습니다.');
        }
        Process::timeout(30)->run(['sudo', '-n', 'chown', $owner.':www-data', $target]);
        Process::timeout(30)->run(['sudo', '-n', 'chmod', '664', $target]);
    }

    /**
     * @param  list<array<string, mixed>>  $form
     */
    private function seedMember(PDO $pdo, string $bbsId, array $form): void
    {
        $samples = [
            '이름(국문)' => '김도현',
            '이름(영문)' => 'Kim Dohyun',
            '연락처' => '010-1001-2007',
            '이메일' => 'kimdohyun1@example.com',
            '가입일' => '2026-03-02',
            '아이디' => 'kimdohyun1',
            '병원명' => '신교동물병원',
            '병원연락처' => '02-000-0000',
            '최근 참가 강의' => '종양 연수',
            '누적 참가 횟수' => '3',
            '승인 일시' => '2026-03-02 10:00',
        ];
        $values = [];
        foreach ($form as $field) {
            $label = (string) ($field['label'] ?? '');
            $name = (string) ($field['name'] ?? '');
            if ($name !== '' && isset($samples[$label])) {
                $values[$name] = $samples[$label];
            }
        }
        if ($values === []) {
            return;
        }

        $postId = 'IA'.substr(md5($bbsId.'member'), 0, 10);
        $stmt = $pdo->prepare(
            'INSERT INTO BBS_POST (BBS_ID, POST_ID, NTT_SJ, NTT_CN, NTT_NM, NTT_ID, NTT_SEQ, IS_NOTICE, IS_TOP, IS_LOCK, NTT_LINK, NTT_REGDT, USE_YN, RDCNT, REGDT, REGID, MODDT, MODID)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, 0, NOW(), ?, NOW(), ?)'
        );
        $stmt->execute([
            $bbsId,
            $postId,
            '김도현',
            json_encode(['values' => $values], JSON_UNESCAPED_UNICODE),
            '관리자',
            'homepage',
            'N',
            'N',
            'N',
            '',
            '',
            'Y',
            'ia',
            'ia',
        ]);
    }

    private function pdo(ProjectProvision $provision): PDO
    {
        $database = 'pub'.(string) $provision->db_database;
        $user = (string) $provision->db_username;
        if (! preg_match('/^[a-z][a-z0-9-]{1,40}$/', $database) || ! preg_match('/^[a-z][a-z0-9-]{1,30}$/', $user)) {
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
