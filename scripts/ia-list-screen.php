<?php

/**
 * IA 목록 설명으로 참고 화면과 같은 검색, 버튼, 컬럼을 만듭니다.
 *
 * @return array<string, mixed>|null
 */
function ia_list_spec(string $text): ?array
{
    $text = ia_text($text);
    if ($text === '' || ! preg_match('/리스트\s*:/u', $text)) {
        return null;
    }

    $list = $text;
    if (preg_match('/리스트\s*:\s*(.*)$/us', $text, $matched)) {
        $list = $matched[1];
    }

    $actionText = '';
    if (preg_match('/관리\s*[\(（](.*)$/us', $list, $matched)) {
        $actionText = $matched[1];
        $list = preg_replace('/관리\s*[\(（].*$/us', '', $list) ?? $list;
    }

    $columns = [];
    foreach (preg_split('/[,\/\r\n]+/u', $list) ?: [] as $piece) {
        $label = trim((string) $piece);
        $plain = preg_replace('/\s+/u', '', $label) ?? $label;
        if ($label === '' || preg_match('/check\s*box|checkbox|선택$/iu', $plain) || in_array($plain, ['No', 'NO', '번호'], true) || preg_match('/^\d+$/', $plain)) {
            continue;
        }
        if (preg_match('/Button|Select Box|목록개수/iu', $label)) {
            continue;
        }
        $columns[] = ia_column($label, count($columns) + 1);
    }

    if ($columns === []) {
        return null;
    }

    $actions = [];
    foreach (['복구', '삭제', '수정', '상세', '환불', '정지', '승인', '거절'] as $action) {
        $position = mb_strpos($actionText, $action);
        if ($position === false) {
            continue;
        }
        $actions[$position] = $action === '상세' ? '수정' : $action;
    }
    ksort($actions);
    $actions = array_values(array_unique($actions));
    if ($actions === []) {
        $actions = ['수정', '삭제'];
    }

    $toolbar = [];
    if (str_contains($text, '엑셀')) {
        $toolbar[] = 'excel';
    }
    if (str_contains($text, '일괄 복구') || str_contains($text, '일괄복구')) {
        $toolbar[] = 'bulk_restore';
    }
    if (str_contains($text, '일괄 승인') || str_contains($text, '일괄승인')) {
        $toolbar[] = 'bulk_approve';
    }
    if (str_contains($text, '선택삭제') || str_contains($text, '선택 삭제')) {
        $toolbar[] = 'bulk_delete';
    }
    if (preg_match('/신규\s*등록|강의\s*등록|수강\s*후기\s*등록/u', $text)) {
        $toolbar[] = 'create';
    }
    if ((bool) preg_match('/check\s*box|선택\s*\(\s*CheckBox/iu', $text) && in_array('삭제', $actions, true) && ! in_array('bulk_delete', $toolbar, true)) {
        $toolbar[] = 'bulk_delete';
    }
    if (! in_array('bulk_delete', $toolbar, true)) {
        $toolbar[] = 'bulk_delete';
    }
    if (! in_array('create', $toolbar, true)) {
        $toolbar[] = 'create';
    }

    $keywordOptions = [];
    $showKeyword = str_contains($text, '검색어') || preg_match('/필터|검색구분/u', $text) === 1;
    if ($showKeyword && preg_match('/(?:필터|검색구분)\s*[:：]?\s*\(([^)]+)\)/u', $text, $matched)) {
        foreach (explode(',', str_replace('아아디', '아이디', $matched[1])) as $option) {
            $option = trim($option);
            if ($option === '') {
                continue;
            }
            $keywordOptions[] = [
                'label' => $option,
                'value' => ia_keyword_value($option, $columns),
            ];
        }
    }
    $keywordOptions = array_values(array_filter(
        $keywordOptions,
        static fn (array $option): bool => $option['label'] !== ''
    ));

    $selects = [];
    if (preg_match_all('/([가-힣A-Za-z]{2,12})\s*(?:\(Text\))?\s*[:：]?\s*[\{\(]\s*(전체[^}\)]+)[\}\)]/u', $text, $matched, PREG_SET_ORDER)) {
        foreach ($matched as $item) {
            $label = trim($item[1]);
            if (in_array($label, ['필터', '검색구분', '목록개수'], true)) {
                continue;
            }
            $options = [];
            foreach (explode(',', $item[2]) as $option) {
                $option = trim(preg_replace('/\([^)]*\)?/u', '', $option) ?? $option);
                if ($option !== '') {
                    $options[] = $option;
                }
            }
            $selects[] = [
                'label' => $label,
                'name' => ia_keyword_value($label, $columns),
                'options' => $options,
            ];
        }
    }

    $dateFilters = ia_date_filters($text, $columns);
    $dateLabel = $dateFilters[0]['label'] ?? '';
    $dateField = $dateFilters[0]['field'] ?? '';

    $statusField = 'status';
    foreach ($columns as $column) {
        if ($column['label'] === '상태') {
            $statusField = $column['name'];
        }
    }

    return [
        'columns' => $columns,
        'actions' => $actions,
        'toolbar' => $toolbar,
        'keyword_options' => $keywordOptions,
        'selects' => $selects,
        'date_filters' => $dateFilters,
        'date_label' => $dateLabel,
        'date_field' => $dateField,
        'checkbox' => (bool) preg_match('/check\s*box|선택\s*\(\s*CheckBox/iu', $text) || array_intersect($toolbar, ['bulk_restore', 'bulk_approve', 'bulk_delete']) !== [],
        'status_field' => $statusField,
        'show_keyword' => $showKeyword,
        'show_per_page' => str_contains($text, '목록개수'),
        'show_number' => (bool) preg_match('/번호|(?:^|[,\s])No(?:$|[,\s])/iu', $list),
    ];
}

/**
 * 검색 설명에 적힌 날짜 이름을 그대로 씁니다. 목록 컬럼 이름으로 바꾸지 않습니다.
 *
 * @param  list<array{label: string, source: string, name: string}>  $columns
 * @return list<array{label: string, field: string}>
 */
function ia_date_filters(string $text, array $columns): array
{
    $search = preg_split('/리스트\s*:/u', $text, 2)[0] ?? $text;
    $filters = [];
    $pattern = '/([가-힣A-Za-z][가-힣A-Za-z0-9\s]{0,18})\s*:?\s*(?:\[[^\]]*\]|\((?:Calender|Calendar|Callender)\))?\s*~\s*(?:\[[^\]]*\]|\((?:Calender|Calendar|Callender)\)|([가-힣A-Za-z][가-힣A-Za-z0-9\s]{0,18}))(?:\s*\((?:Calender|Calendar|Callender)\))?/iu';
    if (preg_match_all($pattern, $search, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $left = ia_clean_date_label($match[1]);
            $right = ia_clean_date_label($match[2] ?? '');
            if ($left === '' || ! preg_match('/Calender|Calendar|Callender|체크인|체크아웃|일시|등록일|가입/iu', $match[0])) {
                continue;
            }
            $leftBase = ia_date_base($left);
            $rightBase = $right === '' ? '' : ia_date_base($right);
            $label = $right === '' || $leftBase === $rightBase ? $leftBase : $left.' ~ '.$right;
            $filters[] = ['label' => $label, 'span' => $match[0]];
        }
    }

    $rest = $search;
    foreach ($filters as $filter) {
        $rest = str_replace($filter['span'], ' ', $rest);
    }
    if (preg_match_all('/([가-힣A-Za-z][가-힣A-Za-z0-9]{1,16})\s*\((?:Calender|Calendar|Callender)\)/iu', $rest, $singles, PREG_SET_ORDER)) {
        foreach ($singles as $single) {
            $filters[] = ['label' => trim($single[1]), 'span' => $single[0]];
        }
    }

    $unique = [];
    foreach ($filters as $filter) {
        if ($filter['label'] === '' || isset($unique[$filter['label']])) {
            continue;
        }
        $unique[$filter['label']] = [
            'label' => $filter['label'],
            'field' => ia_date_field_for($filter['label'], $columns),
        ];
    }

    return array_values($unique);
}

function ia_clean_date_label(string $label): string
{
    $label = trim(preg_replace('/\((?:Calender|Calendar|Callender)\)|\[[^\]]*\]/iu', '', $label) ?? $label);

    return trim($label, " :/");
}

function ia_date_base(string $label): string
{
    return trim(preg_replace('/(시작|끝)$/u', '', trim($label)) ?? $label);
}

/**
 * @param  list<array{label: string, source: string, name: string}>  $columns
 */
function ia_date_field_for(string $label, array $columns): string
{
    $plain = preg_replace('/\s+/u', '', $label) ?? $label;
    foreach ($columns as $column) {
        $columnPlain = preg_replace('/\s+/u', '', $column['label']) ?? $column['label'];
        if ($columnPlain !== '' && ($columnPlain === $plain || str_contains($plain, $columnPlain))) {
            return $column['source'] === 'created_at' ? '' : (string) $column['name'];
        }
    }

    $known = [
        '체크인' => 'check_in',
        '체크아웃' => 'check_out',
        '탈퇴일시' => 'withdrawn_at',
        '가입일시' => 'joined_at',
        '가입일' => 'joined_at',
        '결제일시' => 'paid_at',
        '강의일시' => 'lecture_at',
    ];
    foreach ($known as $key => $name) {
        if (str_contains($plain, $key)) {
            return $name;
        }
    }

    return '';
}

function ia_is_calendar_list(string $text): bool
{
    $text = trim($text);
    if ($text === '' || preg_match('/리스트\s*:/u', $text)) {
        return false;
    }

    return preg_match('/캘린더|Calender|Calendar/iu', $text) === 1
        && preg_match('/형태|전환|일정/u', $text) === 1;
}

/**
 * @param  list<array{label: string, source: string, name: string}>  $columns
 */
function ia_keyword_value(string $option, array $columns): string
{
    $option = preg_replace('/\s+/u', '', $option) ?? $option;
    if (in_array($option, ['전체', 'all'], true)) {
        return 'all';
    }
    if ($option === '제목') {
        return 'title';
    }
    if ($option === '내용') {
        return 'content';
    }

    foreach ($columns as $column) {
        $label = preg_replace('/\s+/u', '', $column['label']) ?? $column['label'];
        if ($label === $option) {
            return $column['name'];
        }
    }
    foreach ($columns as $column) {
        $label = preg_replace('/\s+/u', '', $column['label']) ?? $column['label'];
        if (str_starts_with($label, $option)) {
            return $column['name'];
        }
    }

    return ia_column($option, 1)['name'];
}

/**
 * @return array{label: string, source: string, name: string}
 */
function ia_column(string $label, int $index): array
{
    $plain = preg_replace('/\s+/u', '', $label) ?? $label;
    $known = [
        '이름(국문)' => 'name_ko',
        '이름(영문)' => 'name_en',
        '이름' => 'name_ko',
        '연락처' => 'phone',
        '이메일' => 'email',
        '아이디' => 'login_id',
        '병원명' => 'hospital',
        '병원연락처' => 'hospital_phone',
        '누적참여횟수' => 'join_count',
        '누적참가회수' => 'join_count',
        '누적참가횟수' => 'join_count',
        '상태' => 'status',
        '성별' => 'gender',
        '소속병원' => 'hospital',
        '강의명' => 'lecture',
        '숙소예약대행' => 'stay',
        '숙소여부' => 'stay',
        '결제일시' => 'paid_at',
        '주문번호' => 'order_no',
        '결제수단' => 'pay_method',
        '결제상태' => 'pay_status',
        '결제금액' => 'amount',
        '강사' => 'instructor',
        '강사명' => 'instructor',
        '가격' => 'price',
        '강의일시' => 'lecture_at',
        '강의썸네일' => 'thumbnail',
        '캘린더등록유무' => 'calendar_flag',
        '수의사자격증' => 'license',
        '최근참가강의' => 'recent_lecture',
        '탈퇴일시' => 'withdrawn_at',
        '가입일' => 'joined_at',
        '가입일시' => 'joined_at',
        '메일명' => 'mail_name',
        '주소록' => 'book_name',
        '주소록명' => 'book_name',
        '등록인원' => 'member_count',
        '사용여부' => 'is_used',
        '노출여부' => 'is_visible',
        '진행여부' => 'progress',
    ];

    if (str_contains($plain, '제목')) {
        return ['label' => $label, 'source' => 'title', 'name' => 'title'];
    }
    if ($plain === '작성자') {
        return ['label' => $label, 'source' => 'author', 'name' => 'author_name'];
    }
    if (in_array($plain, ['등록일', '작성일'], true)) {
        return ['label' => $label, 'source' => 'created_at', 'name' => 'created_at'];
    }
    if ($plain === '조회수') {
        return ['label' => $label, 'source' => 'views', 'name' => 'view_count'];
    }
    if (str_contains($plain, '첨부')) {
        return ['label' => $label, 'source' => 'attachments', 'name' => 'attachments'];
    }
    if ($plain === '카테고리') {
        return ['label' => $label, 'source' => 'category', 'name' => 'category'];
    }

    return [
        'label' => $label,
        'source' => 'custom',
        'name' => $known[$plain] ?? 'col_'.$index,
    ];
}

/**
 * @param  array<string, mixed>  $spec
 * @return list<array<string, mixed>>
 */
function ia_custom_fields(array $spec): array
{
    $fields = [];
    foreach ($spec['columns'] as $column) {
        if ($column['source'] !== 'custom' || $column['name'] === 'thumbnail' || str_contains($column['label'], '썸네일')) {
            continue;
        }
        $type = preg_match('/일시|일자|등록일|가입일/u', $column['label']) ? 'date' : 'text';
        $options = '';
        foreach ($spec['selects'] as $select) {
            if ($select['name'] === $column['name']) {
                $type = 'select';
                $options = implode(',', array_values(array_filter($select['options'], static fn (string $option): bool => $option !== '전체')));
            }
        }
        $fields[] = ia_field_row($column['name'], $column['label'], $type, $options);
    }

    foreach ($spec['selects'] as $select) {
        $exists = false;
        foreach ($fields as $field) {
            if ($field['name'] === $select['name']) {
                $exists = true;
            }
        }
        if ($exists || in_array($select['name'], ['title', 'content', 'category', 'all'], true)) {
            continue;
        }
        $fields[] = ia_field_row(
            $select['name'],
            $select['label'],
            'select',
            implode(',', array_values(array_filter($select['options'], static fn (string $option): bool => $option !== '전체')))
        );
    }

    return $fields;
}

/**
 * @return array<string, mixed>
 */
/**
 * @param  array{label: string, source?: string, name: string}  $column
 * @param  array{label: string, name: string, options: list<string>}|null  $select
 */
function ia_form_control(array $column, ?array $select): string
{
    $name = (string) $column['name'];
    $label = e((string) $column['label']);
    $safe = preg_replace('/[^a-z0-9_]/', '', $name) ?? $name;
    $current = "old('custom_field_{$safe}', \$iaSaved['{$safe}'] ?? '')";
    $value = '{{ '.$current.' }}';
    $inputId = 'custom_field_'.$safe;
    if ($select !== null) {
        $options = '<option value="">선택</option>';
        foreach ($select['options'] as $option) {
            if ($option === '전체' || $option === '') {
                continue;
            }
            $quoted = str_replace(['\\', '\''], ['\\\\', '\\\''], $option);
            $options .= '<option value="'.e($option).'" @selected('.$current.' == \''.$quoted.'\')>'.e($option).'</option>';
        }

        return '<div class="board-form-group"><label class="board-form-label" for="'.$inputId.'">'.$label.'</label><select class="board-form-control" id="'.$inputId.'" name="'.$inputId.'">'.$options.'</select></div>';
    }
    if (preg_match('/일시|일자|등록일|가입일/u', (string) $column['label'])) {
        return '<div class="board-form-group"><label class="board-form-label" for="'.$inputId.'">'.$label.'</label><input type="date" class="board-form-control" id="'.$inputId.'" name="'.$inputId.'" value="'.$value.'"></div>';
    }

    return '<div class="board-form-group"><label class="board-form-label" for="'.$inputId.'">'.$label.'</label><input type="text" class="board-form-control" id="'.$inputId.'" name="'.$inputId.'" value="'.$value.'"></div>';
}

function ia_field_row(string $name, string $label, string $type, string $options): array
{
    return [
        'name' => $name,
        'label' => $label,
        'type' => $type,
        'required' => false,
        'options' => $options,
        'show' => 'both',
        'max_length' => null,
        'placeholder' => null,
    ];
}

/**
 * @param  array<string, mixed>  $spec
 * @return array<string, array{enabled: bool, required: bool, label: string}>
 */
function ia_field_config_from(array $spec, string $formText): array
{
    $sources = array_column($spec['columns'], 'source');
    $text = $formText.' '.implode(' ', array_column($spec['columns'], 'label'));
    $config = [
        'title' => ['enabled' => in_array('title', $sources, true) || str_contains($text, '제목'), 'required' => false, 'label' => '제목'],
        'content' => ['enabled' => str_contains($formText, '내용') || str_contains($formText, 'Editor') || str_contains($formText, '글쓰기'), 'required' => false, 'label' => '내용'],
        'category' => ['enabled' => in_array('category', $sources, true) || str_contains($text, '카테고리'), 'required' => false, 'label' => '카테고리'],
        'author_name' => ['enabled' => in_array('author', $sources, true) || str_contains($text, '작성자'), 'required' => false, 'label' => '작성자'],
        'password' => ['enabled' => false, 'required' => false, 'label' => '비밀번호'],
        'attachments' => ['enabled' => in_array('attachments', $sources, true) || str_contains($text, '첨부'), 'required' => false, 'label' => '첨부파일'],
        'thumbnail' => ['enabled' => str_contains($text, '썸네일'), 'required' => false, 'label' => '썸네일'],
        'is_secret' => ['enabled' => false, 'required' => false, 'label' => '비밀글'],
        'is_active' => ['enabled' => str_contains($text, '노출'), 'required' => false, 'label' => '노출여부'],
    ];
    foreach (['title', 'content', 'author_name'] as $key) {
        if ($config[$key]['enabled']) {
            $config[$key]['required'] = true;
        }
    }

    return $config;
}

/**
 * @param  array<string, mixed>  $spec
 */
function ia_write_index(string $slug, array $spec): void
{
    $directory = resource_path('views/backoffice/board-posts/'.$slug);
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $headers = '';
    $cells = "@php\n\$iaValues = json_decode((string) (\$post->custom_fields ?? ''), true);\n\$iaValues = is_array(\$iaValues) ? \$iaValues : [];\n@endphp\n";
    foreach ($spec['columns'] as $column) {
        $headers .= '<th>'.e($column['label']).'</th>';
        $cells .= '<td>'.ia_cell($column).'</td>';
    }

    $dateFilter = '';
    $selectFilters = '';
    $keywordFilters = '';
    foreach ($spec['date_filters'] ?? [] as $index => $filter) {
        if ($index === 0) {
            $startName = 'start_date';
            $endName = 'end_date';
            $fieldName = 'date_field';
            $startValue = "request('start_date')";
            $endValue = "request('end_date')";
        } else {
            $startName = 'dates['.$index.'][start]';
            $endName = 'dates['.$index.'][end]';
            $fieldName = 'dates['.$index.'][field]';
            $startValue = "request('dates.".$index.".start')";
            $endValue = "request('dates.".$index.".end')";
        }
        $dateFilter .= '<div class="filter-group"><label class="filter-label">'.e($filter['label']).'</label><div style="display:flex;gap:8px;align-items:center"><input type="date" name="'.$startName.'" class="filter-input" value="{{ '.$startValue.' }}"><span>~</span><input type="date" name="'.$endName.'" class="filter-input" value="{{ '.$endValue.' }}"></div>';
        if (($filter['field'] ?? '') !== '') {
            $dateFilter .= '<input type="hidden" name="'.$fieldName.'" value="'.e($filter['field']).'">';
        }
        $dateFilter .= '</div>';
    }
    foreach ($spec['selects'] as $select) {
        $input = $select['name'] === 'category' ? 'category' : 'cf['.$select['name'].']';
        $current = $select['name'] === 'category' ? "request('category')" : "request('cf.".$select['name']."')";
        $options = '';
        foreach ($select['options'] as $option) {
            $options .= '<option value="'.e($option === '전체' ? '' : $option).'" @selected('.$current.' == \''.e($option === '전체' ? '' : $option).'\')>'.e($option).'</option>';
        }
        $selectFilters .= '<div class="filter-group"><label class="filter-label">'.e($select['label']).'</label><select name="'.$input.'" class="filter-select">'.$options.'</select></div>';
    }
    if (! empty($spec['show_keyword'])) {
        $keyword = '';
        $hasKeywordChoice = false;
        foreach ($spec['keyword_options'] as $option) {
            if ($option['value'] !== 'all') {
                $hasKeywordChoice = true;
            }
            $keyword .= '<option value="'.e($option['value']).'" @selected(request(\'search_type\', \'all\') == \''.e($option['value']).'\')>'.e($option['label']).'</option>';
        }
        if ($hasKeywordChoice) {
            $keywordFilters .= '<div class="filter-group"><label class="filter-label">필터</label><select name="search_type" class="filter-select">'.$keyword.'</select></div>';
        }
        $keywordFilters .= '<div class="filter-group"><label class="filter-label">검색어</label><input type="text" name="keyword" class="filter-input" placeholder="검색어" value="{{ request(\'keyword\') }}"></div>';
    }

    $tools = '';
    if (in_array('excel', $spec['toolbar'], true)) {
        $tools .= '<a class="btn btn-secondary" href="{{ route(\'backoffice.board-posts.index\', $board->slug) }}?{{ http_build_query(array_merge(request()->query(), [\'export\' => \'xlsx\'])) }}"><i class="fas fa-file-excel"></i> 엑셀다운로드</a>';
    }
    if (in_array('bulk_restore', $spec['toolbar'], true)) {
        $tools .= '<button type="button" class="btn btn-primary" onclick="iaBulk(\'복구\', \'선택한 항목을 복구하시겠습니까?\')">일괄 복구</button>';
    }
    if (in_array('bulk_approve', $spec['toolbar'], true)) {
        $tools .= '<button type="button" class="btn btn-primary" onclick="iaBulk(\'승인\', \'선택한 항목을 승인하시겠습니까?\')">일괄 승인</button>';
    }
    if (in_array('bulk_delete', $spec['toolbar'], true)) {
        $tools .= '<button type="button" id="bulk-delete-btn" class="btn btn-danger" disabled><i class="fas fa-trash"></i> 선택 삭제</button>';
    }
    if (in_array('create', $spec['toolbar'], true)) {
        $tools .= '<a href="{{ route(\'backoffice.board-posts.create\', $board->slug) }}" class="btn btn-success"><i class="fas fa-plus"></i> 등록</a>';
    }

    $checkboxHead = $spec['checkbox'] ? '<th class="w5"><input type="checkbox" id="select-all" class="form-check-input"></th>' : '';
    $checkboxCell = $spec['checkbox'] ? '<td><input type="checkbox" name="selected_posts[]" value="{{ $post->id }}" class="form-check-input post-checkbox"></td>' : '';
    $numberHead = ! empty($spec['show_number']) ? '<th class="w5">번호</th>' : '';
    $numberCell = ! empty($spec['show_number']) ? '<td>{{ $posts->total() - ($posts->currentPage() - 1) * $posts->perPage() - $loop->index }}</td>' : '';
    $colspan = count($spec['columns']) + 1 + ($spec['checkbox'] ? 1 : 0) + (! empty($spec['show_number']) ? 1 : 0);
    $actions = '';
    foreach ($spec['actions'] as $action) {
        $actions .= ia_action_button($action, (string) $spec['status_field']);
    }

    $blade = <<<'BLADE'
@extends('backoffice.layouts.app')

@section('title', $board->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success board-hidden-alert">{{ session('success') }}</div>
    @endif

    <div class="board-container">
        <div class="board-card">
            <div class="board-card-body">
                <div class="board-filter">
                    <form method="GET" action="{{ route('backoffice.board-posts.index', $board->slug) }}" class="filter-form">
                        <div class="filter-row">
BLADE;
    $blade .= $selectFilters.$dateFilter;
    $blade .= <<<'BLADE'
                        </div>
                        <div class="filter-row">
BLADE;
    $blade .= $keywordFilters;
    $blade .= <<<'BLADE'
                            <div class="filter-group">
                                <div class="filter-buttons">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> 검색</button>
                                    <a href="{{ route('backoffice.board-posts.index', $board->slug) }}" class="btn btn-secondary"><i class="fas fa-undo"></i> 초기화</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="board-list-header">
                    <div class="list-info">
                        <span class="list-count">총 {{ $posts->total() }}건 ({{ $posts->currentPage() }}/{{ max(1, $posts->lastPage()) }})</span>
                    </div>
                    <div class="list-controls">
BLADE;
    $blade .= '<div class="board-page-buttons">'.$tools.'</div>';
    if (! empty($spec['show_per_page'])) {
        $blade .= <<<'BLADE'
                        <form method="GET" action="{{ route('backoffice.board-posts.index', $board->slug) }}" class="per-page-form">
                            @foreach(request()->except('per_page', 'page') as $name => $value)
                                @if(is_string($value))
                                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <select name="per_page" class="per-page-select" onchange="this.form.submit()">
                                @foreach([10, 20, 50] as $size)
                                    <option value="{{ $size }}" @selected((int) request('per_page', 10) === $size)>{{ $size }}개</option>
                                @endforeach
                            </select>
                        </form>
BLADE;
    }
    $blade .= <<<'BLADE'
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="board-table">
                        <thead>
                            <tr>
BLADE;
    $blade .= $checkboxHead.$numberHead.$headers.'<th class="w15">관리</th>';
    $blade .= <<<'BLADE'
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($posts as $post)
                                <tr>
BLADE;
    $blade .= $checkboxCell.$numberCell.$cells;
    $blade .= '<td><div class="board-btn-group">'.$actions.'</div></td>';
    $blade .= <<<BLADE
                                </tr>
                            @empty
                                <tr><td colspan="{$colspan}" class="text-center">등록된 데이터가 없습니다.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination :paginator="\$posts" />
            </div>
        </div>
    </div>
    <form id="ia-bulk-form" method="POST" action="{{ route('backoffice.board-posts.bulk_status', \$board->slug) }}">
        @csrf
        <input type="hidden" name="status" id="ia-bulk-status" value="">
        <input type="hidden" name="field" value="{$spec['status_field']}">
        <div id="ia-bulk-ids"></div>
    </form>
@endsection

@section('scripts')
    <script src="{{ asset('js/backoffice/board-posts.js') }}"></script>
    <script>
        document.getElementById('select-all')?.addEventListener('change', function (event) {
            document.querySelectorAll('.post-checkbox').forEach(function (box) { box.checked = event.target.checked; });
        });
        function iaBulk(status, message) {
            var boxes = document.querySelectorAll('.post-checkbox:checked');
            if (!boxes.length) { alert('항목을 선택하세요.'); return; }
            if (!confirm(message)) return;
            var holder = document.getElementById('ia-bulk-ids');
            holder.innerHTML = '';
            boxes.forEach(function (box) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'post_ids[]';
                input.value = box.value;
                holder.appendChild(input);
            });
            document.getElementById('ia-bulk-status').value = status;
            document.getElementById('ia-bulk-form').submit();
        }
    </script>
@endsection
BLADE;

    file_put_contents($directory.'/index.blade.php', $blade);
}

function ia_write_single_index(string $slug): void
{
    $directory = resource_path('views/backoffice/board-posts/'.$slug);
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    file_put_contents($directory.'/index.blade.php', <<<'BLADE'
@extends('backoffice.layouts.app')

@section('title', $board->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
    <div class="board-container">
        <div class="board-card">
            <div class="board-card-body">
                <div class="board-list-header">
                    <div class="list-info"><span class="list-count">{{ $board->name }}</span></div>
                    <div class="list-controls"><div class="board-page-buttons">
                        @if($posts->count() > 0)
                            <a href="{{ route('backoffice.board-posts.edit', [$board->slug, $posts->first()->id]) }}" class="btn btn-primary">수정</a>
                        @else
                            <a href="{{ route('backoffice.board-posts.create', $board->slug) }}" class="btn btn-success">등록</a>
                        @endif
                    </div></div>
                </div>
            </div>
        </div>
    </div>
@endsection
BLADE);
}

function ia_write_calendar_index(string $slug, string $text, bool $hasForm): void
{
    $directory = resource_path('views/backoffice/board-posts/'.$slug);
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $columns = [];
    if (preg_match('/클릭\s*시\s*(.+)$/us', $text, $matched)) {
        foreach (preg_split('/[,，]/u', $matched[1]) ?: [] as $piece) {
            $label = trim(preg_replace('/표시.*$/u', '', (string) $piece) ?? (string) $piece);
            if ($label === '' || mb_strlen($label) > 24) {
                continue;
            }
            $columns[] = ia_column($label, count($columns) + 1);
        }
    }
    $headers = '';
    $cells = '';
    foreach ($columns as $column) {
        $headers .= '<th>'.e($column['label']).'</th>';
        $cells .= '<td>'.ia_cell($column).'</td>';
    }
    if ($headers === '') {
        $headers = '<th>제목</th><th>날짜</th>';
        $cells = '<td><a href="{{ route(\'backoffice.board-posts.edit\', [$board->slug, $post->id]) }}">{{ $post->title }}</a></td><td>{{ optional($post->created_at)->format(\'Y-m-d\') }}</td>';
    }
    $create = $hasForm
        ? '<a href="{{ route(\'backoffice.board-posts.create\', $board->slug) }}" class="btn btn-success"><i class="fas fa-plus"></i> 등록</a>'
        : '';
    $toggle = str_contains($text, '리스트로 전환')
        ? '<a href="{{ route(\'backoffice.board-posts.index\', $board->slug) }}?view={{ request(\'view\') === \'list\' ? \'calendar\' : \'list\' }}" class="btn btn-secondary">{{ request(\'view\') === \'list\' ? \'캘린더로 전환\' : \'리스트로 전환\' }}</a>'
        : '';

    $blade = <<<'BLADE'
@extends('backoffice.layouts.app')

@section('title', $board->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">
@endsection

@section('content')
    @php
        $iaMonthText = (string) request('month', '');
        if ($iaMonthText === '' && $posts->count() > 0) {
            $iaMonthText = optional($posts->first()->created_at)->format('Y-m') ?: now()->format('Y-m');
        }
        try {
            $iaCursor = \Illuminate\Support\Carbon::createFromFormat('Y-m', $iaMonthText !== '' ? $iaMonthText : now()->format('Y-m'))->startOfMonth();
        } catch (\Throwable $exception) {
            $iaCursor = now()->startOfMonth();
        }
        $iaEvents = [];
        foreach ($posts as $iaPost) {
            $iaCustom = json_decode((string) ($iaPost->custom_fields ?? ''), true);
            $iaCustom = is_array($iaCustom) ? $iaCustom : [];
            $iaDay = '';
            foreach ($iaCustom as $iaValue) {
                if (is_string($iaValue) && preg_match('/^\d{4}-\d{2}-\d{2}/', $iaValue)) {
                    $iaDay = substr($iaValue, 0, 10);
                    break;
                }
            }
            if ($iaDay === '' && $iaPost->created_at) {
                $iaDay = $iaPost->created_at->format('Y-m-d');
            }
            if ($iaDay !== '' && str_starts_with($iaDay, $iaCursor->format('Y-m'))) {
                $iaEvents[$iaDay][] = $iaPost;
            }
        }
        $iaGridStart = $iaCursor->copy()->startOfWeek(\Illuminate\Support\Carbon::SUNDAY);
        $iaGridEnd = $iaCursor->copy()->endOfMonth()->endOfWeek(\Illuminate\Support\Carbon::SATURDAY);
    @endphp
    <div class="board-container">
        <div class="board-card">
            <div class="board-card-body">
                <div class="board-list-header">
                    <div class="list-info">
                        <a class="btn btn-secondary btn-sm" href="{{ route('backoffice.board-posts.index', $board->slug) }}?month={{ $iaCursor->copy()->subMonth()->format('Y-m') }}&view={{ request('view') }}">이전달</a>
                        <span class="list-count">{{ $iaCursor->format('Y-m') }}</span>
                        <a class="btn btn-secondary btn-sm" href="{{ route('backoffice.board-posts.index', $board->slug) }}?month={{ $iaCursor->copy()->addMonth()->format('Y-m') }}&view={{ request('view') }}">다음달</a>
                    </div>
                    <div class="list-controls"><div class="board-page-buttons">
BLADE;
    $blade .= $toggle.$create;
    $blade .= <<<'BLADE'
                    </div></div>
                </div>
                @if(request('view') === 'list')
                    <div class="table-responsive">
                        <table class="board-table">
                            <thead><tr>
BLADE;
    $blade .= $headers;
    $blade .= <<<'BLADE'
                            </tr></thead>
                            <tbody>
                                @forelse($posts as $post)
                                    @php
                                        $iaValues = json_decode((string) ($post->custom_fields ?? ''), true);
                                        $iaValues = is_array($iaValues) ? $iaValues : [];
                                    @endphp
                                    <tr>
BLADE;
    $blade .= $cells;
    $blade .= <<<'BLADE'
                                    </tr>
                                @empty
                                    <tr><td class="text-center">등록된 일정이 없습니다.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:8px;">
                        @foreach(['일','월','화','수','목','금','토'] as $iaWeek)
                            <div style="text-align:center;font-weight:600;">{{ $iaWeek }}</div>
                        @endforeach
                        @for($iaDate = $iaGridStart->copy(); $iaDate->lte($iaGridEnd); $iaDate->addDay())
                            <div style="min-height:88px;border:1px solid #e5e7eb;border-radius:8px;padding:6px;{{ $iaDate->format('Y-m') === $iaCursor->format('Y-m') ? '' : 'opacity:.4;' }}">
                                <div>{{ $iaDate->day }}</div>
                                @foreach($iaEvents[$iaDate->format('Y-m-d')] ?? [] as $iaEvent)
                                    <a href="{{ route('backoffice.board-posts.edit', [$board->slug, $iaEvent->id]) }}" style="display:block;font-size:12px;">{{ $iaEvent->title }}</a>
                                @endforeach
                            </div>
                        @endfor
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
BLADE;

    file_put_contents($directory.'/index.blade.php', $blade);
}

/**
 * @param  array{source: string, name: string}  $column
 */
function ia_cell(array $column): string
{
    return match ($column['source']) {
        'title' => '<a href="{{ route(\'backoffice.board-posts.edit\', [$board->slug, $post->id]) }}" class="board-title-link">{{ $post->title }}</a>',
        'author' => '{{ $post->author_name }}',
        'created_at' => '{{ optional($post->created_at)->format(\'Y-m-d\') }}',
        'views' => '{{ $post->view_count }}',
        'category' => '{{ $post->category }}',
        'attachments' => "@if(!empty(\$post->attachments) && \$post->attachments !== '[]' && \$post->attachments !== 'null') 있음 @endif",
        default => "{{ \$iaValues['".$column['name']."'] ?? '' }}",
    };
}

function ia_action_button(string $action, string $field): string
{
    return match ($action) {
        '수정' => '<a href="{{ route(\'backoffice.board-posts.edit\', [$board->slug, $post->id]) }}" class="btn btn-primary btn-sm">수정</a>',
        '상세' => '<a href="{{ route(\'backoffice.board-posts.edit\', [$board->slug, $post->id]) }}" class="btn btn-primary btn-sm">수정</a>',
        '삭제' => '<form action="{{ route(\'backoffice.board-posts.destroy\', [$board->slug, $post->id]) }}" method="POST" class="d-inline" onsubmit="return confirm(\'삭제하시겠습니까?\')">@csrf @method(\'DELETE\')<button type="submit" class="btn btn-danger btn-sm">삭제</button></form>',
        default => '<form action="{{ route(\'backoffice.board-posts.bulk_status\', $board->slug) }}" method="POST" class="d-inline" onsubmit="return confirm(\''.$action.' 하시겠습니까?\')">@csrf<input type="hidden" name="field" value="'.e($field).'"><input type="hidden" name="status" value="'.e($action).'"><input type="hidden" name="post_ids[]" value="{{ $post->id }}"><button type="submit" class="btn btn-secondary btn-sm">'.$action.'</button></form>',
    };
}

/**
 * @param  list<array<string, string>>  $rows
 * @return list<string>
 */
function ia_refresh_from_rows(array $rows): array
{
    $groups = [];
    foreach ($rows as $row) {
        $depth1 = trim((string) ($row['depth1'] ?? ''));
        if (in_array($depth1, ['로그인', '대시보드'], true)) {
            continue;
        }
        $label = ia_row_label($depth1, (string) ($row['depth2'] ?? ''));
        if ($label === '') {
            continue;
        }
        $groups[$label][] = $row;
    }

    $notes = [];
    $boards = App\Models\Board::query()->get();
    $boardService = new App\Services\Backoffice\BoardService();
    foreach ($groups as $label => $group) {
        $list = '';
        $form = '';
        foreach ($group as $row) {
            $description = trim((string) ($row['description'] ?? ''));
            $isList = ($row['depth3'] ?? '') === '리스트' || str_contains($description, '리스트:');
            if ($isList && $list === '') {
                $list = $description;
            } elseif (! $isList) {
                $form .= "\n".$description;
            }
        }
        $spec = ia_list_spec($list);
        if ($spec === null) {
            continue;
        }
        $key = preg_replace('/\s+/u', '', $label) ?? $label;
        $board = $boards->first(function ($item) use ($key) {
            $name = preg_replace('/\s+/u', '', (string) $item->name) ?? '';

            return $name === $key;
        });
        if (! $board) {
            $notes[] = $label.': 목록 화면을 둘 게시판이 없습니다.';
            continue;
        }
        $popups = ia_form_popups($form);
        $described = ia_description_items($form);
        $hasCreate = false;
        foreach ($group as $row) {
            $depth2 = (string) ($row['depth2'] ?? '');
            $depth3 = (string) ($row['depth3'] ?? '');
            if (preg_match('/등록/u', $depth2) || in_array($depth3, ['등록', '등록 / 수정', '등록/수정'], true)) {
                $hasCreate = true;
            }
        }
        if ($hasCreate && ! in_array('create', $spec['toolbar'], true)) {
            $spec['toolbar'][] = 'create';
        }
        $boardService->updateBoard($board, ia_description_has_inputs($described) ? [
            'field_config' => ia_field_config_from_items($described),
            'custom_fields_config' => ia_fields_from_items($described),
            'is_active' => true,
        ] : [
            'field_config' => ia_field_config_from($spec, $form),
            'custom_fields_config' => ia_merge_popups(ia_custom_fields($spec), $popups),
            'is_active' => true,
        ]);
        ia_write_index($board->slug, $spec);
        ia_write_entry_forms($board->slug, $spec, $popups, $form);
        $notes[] = $board->name.': 목록 화면을 IA에 맞췄습니다.';
    }

    return $notes;
}

/**
 * @return list<array{name: string, label: string, button: string, slug: string, column: string}>
 */
function ia_form_popups(string $text): array
{
    $popups = [];
    $map = [
        '강사' => ['name' => 'instructor', 'slug' => 'instructor', 'column' => '강사명'],
        '강의' => ['name' => 'lecture', 'slug' => 'lecture', 'column' => '강의명'],
    ];
    foreach ($map as $label => $popup) {
        if (! preg_match('/'.$label.'[^\n]{0,30}추가하기[\s\S]{0,220}팝업/u', $text)) {
            continue;
        }
        $popups[] = [
            'name' => $popup['name'],
            'label' => $label,
            'button' => $label.' 추가하기',
            'slug' => $popup['slug'],
            'column' => $popup['column'],
        ];
    }

    return $popups;
}

/**
 * @param  list<array<string, mixed>>  $fields
 * @param  list<array<string, string>>  $popups
 * @return list<array<string, mixed>>
 */
function ia_merge_popups(array $fields, array $popups): array
{
    foreach ($popups as $popup) {
        $found = false;
        foreach ($fields as $index => $field) {
            if (($field['name'] ?? '') !== $popup['name']) {
                continue;
            }
            $fields[$index]['type'] = 'popup';
            $fields[$index]['label'] = $popup['label'];
            $fields[$index]['options'] = $popup['slug'];
            $found = true;
        }
        if (! $found) {
            $fields[] = ia_field_row($popup['name'], $popup['label'], 'popup', $popup['slug']);
        }
    }

    return $fields;
}

/**
 * @param  list<array<string, string>>  $popups
 */
function ia_form_is_picker(string $form, array $popups): bool
{
    if ($popups === [] || ! str_contains($form, '제목') || ! str_contains($form, '내용')) {
        return false;
    }

    $plain = preg_replace('/\s+/u', '', $form) ?? $form;
    foreach (['썸네일', '금액', '파일첨부', '강사명', '강의명', '카테고리'] as $word) {
        if (str_contains($plain, $word)) {
            return false;
        }
    }

    return true;
}

/**
 * 등록·상세 설명에 적힌 순서 그대로 입력 항목을 만듭니다.
 *
 * @return list<array<string, mixed>>
 */
function ia_description_items(string $text): array
{
    $lines = [];
    foreach (preg_split('/\R/u', $text) ?: [] as $line) {
        $line = ia_text((string) $line);
        if ($line !== '') {
            $lines[] = $line;
        }
    }

    $items = [];
    $used = [];
    $count = count($lines);
    $index = 0;
    while ($index < $count) {
        $line = $lines[$index];
        if (ia_is_intro_line($line) || ia_is_noise_line($line)) {
            $index++;
            continue;
        }

        if (preg_match('/^(.+?)\s*:\s*$/u', $line, $matched)) {
            $label = trim($matched[1]);
            $next = $lines[$index + 1] ?? '';
            if (ia_label_from_control($next) !== '') {
                $items[] = ia_blank_item('heading', $label);
                $index++;
                continue;
            }
            if ($next !== '' && (ia_is_noise_line($next) || ia_is_intro_line($next))) {
                $index += 2;
                continue;
            }
            if ($next !== '' && (ia_control_kind($next) !== '' || ia_is_option_line($next) || (preg_match('/첨부|파일|썸네일/u', $label) && ia_is_follow_hint($next)))) {
                $index += 2;
                $hint = ia_is_follow_hint($next) && ia_control_kind($next) === '' ? $next : '';
                $control = $hint === '' ? $next : '';
                while (isset($lines[$index]) && ia_is_follow_hint($lines[$index])) {
                    $hint = trim($hint.' '.$lines[$index]);
                    $index++;
                }
                foreach (ia_make_items($label, $control, trim($hint), $used) as $item) {
                    $items[] = $item;
                }
                continue;
            }
            $items[] = ia_blank_item('heading', $label);
            $index++;
            continue;
        }

        if (ia_is_screen_title($line)) {
            $index++;
            continue;
        }

        $kind = ia_control_kind($line);
        $next = $lines[$index + 1] ?? '';
        $third = $lines[$index + 2] ?? '';
        if ($kind === '' && ! ia_is_option_line($line) && ! ia_is_sentence_line($line) && ia_control_kind($third) === 'radio' && ia_is_plain_label($next)) {
            $index += 3;
            $hint = $next;
            while (isset($lines[$index]) && ia_is_follow_hint($lines[$index])) {
                $hint = trim($hint.' '.$lines[$index]);
                $index++;
            }
            foreach (ia_make_items($line, $third, $hint, $used) as $item) {
                $items[] = $item;
            }
            continue;
        }
        if ($kind === '' && ! ia_is_option_line($line) && ! ia_is_sentence_line($line) && ia_control_kind($next) === 'radio') {
            $index += 2;
            $hint = '';
            while (isset($lines[$index]) && ia_is_follow_hint($lines[$index])) {
                $hint = trim($hint.' '.$lines[$index]);
                $index++;
            }
            foreach (ia_make_items($line, $next, $hint, $used) as $item) {
                $items[] = $item;
            }
            continue;
        }
        if ($kind === '' && ! ia_is_option_line($line) && ia_is_sentence_line($line)) {
            $items[] = ia_blank_item('note', $line);
            $index++;
            continue;
        }

        $made = ia_make_items('', $line, '', $used);
        $index++;
        while (isset($lines[$index]) && ia_is_follow_hint($lines[$index])) {
            $last = count($made) - 1;
            if ($last >= 0) {
                $made[$last]['hint'] = trim(((string) ($made[$last]['hint'] ?? '')).' '.$lines[$index]);
            }
            $index++;
        }
        foreach ($made as $item) {
            $items[] = $item;
        }
    }

    return $items;
}

function ia_description_has_inputs(array $items): bool
{
    foreach ($items as $item) {
        if (in_array($item['kind'] ?? '', ['text', 'radio', 'select', 'date', 'dates', 'file', 'editor', 'check', 'popup'], true)) {
            return true;
        }
    }

    return false;
}

/**
 * @param  list<array<string, mixed>>  $items
 * @return list<array<string, mixed>>
 */
function ia_fields_from_items(array $items): array
{
    $fields = [];
    foreach ($items as $item) {
        $kind = (string) ($item['kind'] ?? '');
        if ($kind === 'dates') {
            foreach ([['start_name', 'start_label'], ['end_name', 'end_label']] as $pair) {
                $name = (string) ($item[$pair[0]] ?? '');
                if ($name === '' || ia_is_builtin_field($name)) {
                    continue;
                }
                $fields[] = ia_field_row($name, (string) ($item[$pair[1]] ?? ''), 'date', '');
            }
            continue;
        }
        $name = (string) ($item['name'] ?? '');
        if ($name === '' || ia_is_builtin_field($name) || ($kind === 'popup' && ($item['fills'] ?? '') !== '')) {
            continue;
        }
        $type = match ($kind) {
            'radio' => 'radio',
            'select' => 'select',
            'date' => 'date',
            'file' => 'file',
            'editor' => 'editor',
            'check' => 'checkbox',
            'popup' => 'popup',
            default => 'text',
        };
        $options = $kind === 'popup' ? (string) ($item['popup_slug'] ?? '') : implode(',', (array) ($item['options'] ?? []));
        $fields[] = ia_field_row($name, (string) ($item['label'] ?? ''), $type, $options);
    }

    return $fields;
}

/**
 * @param  list<array<string, mixed>>  $items
 * @return array<string, array{enabled: bool, required: bool, label: string}>
 */
function ia_field_config_from_items(array $items): array
{
    $names = [];
    foreach ($items as $item) {
        foreach (['name', 'start_name', 'end_name'] as $key) {
            if (($item[$key] ?? '') !== '') {
                $names[] = (string) $item[$key];
            }
        }
    }
    $config = [
        'title' => ['enabled' => false, 'required' => false, 'label' => '제목'],
        'content' => ['enabled' => false, 'required' => false, 'label' => '내용'],
        'category' => ['enabled' => false, 'required' => false, 'label' => '카테고리'],
        'author_name' => ['enabled' => false, 'required' => false, 'label' => '작성자'],
        'password' => ['enabled' => false, 'required' => false, 'label' => '비밀번호'],
        'attachments' => ['enabled' => false, 'required' => false, 'label' => '첨부파일'],
        'thumbnail' => ['enabled' => false, 'required' => false, 'label' => '썸네일'],
        'is_secret' => ['enabled' => false, 'required' => false, 'label' => '비밀글'],
        'is_active' => ['enabled' => false, 'required' => false, 'label' => '노출여부'],
    ];
    $config['title']['enabled'] = in_array('title', $names, true);
    $config['content']['enabled'] = in_array('content', $names, true);
    $config['category']['enabled'] = in_array('category', $names, true);
    $config['author_name']['enabled'] = in_array('author_name', $names, true);
    $config['attachments']['enabled'] = in_array('attachments', $names, true);
    $config['thumbnail']['enabled'] = in_array('thumbnail', $names, true);
    $config['is_active']['enabled'] = false;
    $config['is_secret']['enabled'] = false;
    foreach (['title', 'content', 'author_name'] as $key) {
        if ($config[$key]['enabled']) {
            $config[$key]['required'] = true;
        }
    }

    return $config;
}

function ia_utf8(string $value): string
{
    $clean = iconv('UTF-8', 'UTF-8//IGNORE', $value);

    return is_string($clean) ? $clean : '';
}

function ia_text(string $value): string
{
    return trim(html_entity_decode(ia_utf8($value), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
}

function ia_is_builtin_field(string $name): bool
{
    return in_array($name, ['title', 'content', 'category', 'author_name', 'thumbnail', 'attachments', 'is_notice'], true);
}

/**
 * @return array<string, mixed>
 */
function ia_blank_item(string $kind, string $label): array
{
    return [
        'kind' => $kind,
        'label' => $label,
        'name' => '',
        'start_name' => '',
        'end_name' => '',
        'start_label' => '',
        'end_label' => '',
        'options' => [],
        'hint' => '',
        'file' => '',
        'fills' => '',
        'popup_slug' => '',
        'popup_column' => '',
        'max_files' => 5,
        'max_mb' => 10,
    ];
}

function ia_is_intro_line(string $line): bool
{
    return preg_match('/확인할 수 있는 페이지|고객사 로그인|^대시보드/u', $line) === 1;
}

function ia_is_noise_line(string $line): bool
{
    if (preg_match('/Alert|하시겠습니까|CASE\s*\d|팝업 형태|확인\s*->|취소\s*->/iu', $line)) {
        return true;
    }

    return preg_match('/Button/iu', $line) === 1
        && preg_match('/추가하기|Radio|Select|input|Editor|Drop|파일|:/iu', $line) !== 1;
}

function ia_is_sentence_line(string $line): bool
{
    return ia_control_kind($line) === '' && preg_match('/까지|입니다|해주세요|있는 페이지|활성화/u', $line) === 1;
}

function ia_is_screen_title(string $line): bool
{
    if (preg_match('/등록\s*\(\s*Text\s*\)\s*$/iu', $line) && ! preg_match('/input|Select|Radio|file|Editor|Drop/iu', $line)) {
        return true;
    }

    return ia_control_kind($line) === ''
        && preg_match('/:\s*$/u', $line) !== 1
        && preg_match('/등록$/u', $line) === 1
        && mb_strlen($line) <= 24;
}

function ia_is_plain_label(string $line): bool
{
    return $line !== '' && ia_control_kind($line) === '' && ! ia_is_option_line($line) && ! ia_is_sentence_line($line) && ! ia_is_noise_line($line) && preg_match('/:\s*$/u', $line) !== 1;
}

function ia_is_unlabeled_control(string $line): bool
{
    return $line !== '' && (ia_control_kind($line) !== '' || ia_is_option_line($line)) && ia_label_from_control($line) === '';
}

function ia_is_follow_hint(string $line): bool
{
    return str_starts_with($line, '*') || preg_match('/최대|업로드|클릭\s*시|활성화/u', $line) === 1;
}

function ia_is_option_line(string $line): bool
{
    return substr_count($line, ',') >= 2 && ia_control_kind($line) === '' && mb_strlen($line) < 220;
}

function ia_control_kind(string $line): string
{
    if (preg_match('/추가하기/u', $line) && preg_match('/강사|강의/u', $line)) {
        return 'popup';
    }
    if (preg_match('/Radio\s*Button/iu', $line)) {
        return 'radio';
    }
    if (preg_match('/Editor|글쓰기/iu', $line) && preg_match('/Drop\s*Box|파일첨부|파일선택|\bfile\b/iu', $line)) {
        return 'editor_file';
    }
    if (preg_match('/Editor|글쓰기/iu', $line)) {
        return 'editor';
    }
    if (preg_match('/Drop\s*Box|file\s*select|파일선택|파일첨부|\bfile\b/iu', $line)) {
        return 'file';
    }
    if (preg_match('/Select\s*Box/iu', $line)) {
        return 'select';
    }
    if ((str_contains($line, '~') || str_contains($line, '～')) && preg_match('/Calender|Calendar|캘린더|체크인|마감/iu', $line)) {
        return 'dates';
    }
    if (preg_match('/Calender|Calendar/iu', $line)) {
        return 'date';
    }
    if (preg_match('/Check\s*Box|checkbox/iu', $line)) {
        return 'check';
    }
    if (preg_match('/등록\s*\(\s*Button/iu', $line)) {
        return 'check';
    }
    if (preg_match('/input\s*Text|\(Text\)|:\s*Text\b/iu', $line)) {
        return 'text';
    }

    return '';
}

function ia_label_from_control(string $line): string
{
    if (ia_control_kind($line) === 'radio') {
        return '';
    }
    if (preg_match('/^(.+?)\s*:\s*\S/u', $line, $matched)) {
        $label = trim($matched[1]);
        if ($label !== '' && mb_strlen($label) <= 40) {
            return $label;
        }
    }
    if (preg_match('/^(.+?)\s*\(\s*(?:Radio|Button|Drop|Select|input|Text|Check|file|Editor)/iu', $line, $matched)) {
        return trim($matched[1], ' :');
    }
    if (preg_match('/Select\s*Box/iu', $line) && preg_match('/^(.+?)\s*[\({]/u', $line, $matched)) {
        return trim($matched[1], ' :');
    }

    return '';
}

/**
 * @param  array<string, bool>  $used
 * @return list<array<string, mixed>>
 */
function ia_make_items(string $label, string $control, string $hint, array &$used): array
{
    $source = trim($control !== '' ? $control : $label);
    $kind = ia_control_kind($source);
    if ($kind === '' && ia_is_option_line($control)) {
        $kind = 'select';
    }
    if ($kind === '' && $control === '' && ia_is_sentence_line($label)) {
        return [ia_blank_item('note', $label)];
    }
    if ($label === '') {
        $label = ia_label_from_control($source);
    }
    if ($kind === 'editor_file') {
        $editor = ia_make_items($label !== '' ? $label : '내용', '글쓰기 Editor', '', $used);
        $file = ia_make_items('파일첨부', '파일첨부', $hint, $used);

        return array_merge($editor, $file);
    }
    if ($kind === '' && preg_match('/첨부|썸네일/u', $label)) {
        $kind = 'file';
    }
    if ($kind === '') {
        $kind = 'text';
    }
    if (preg_match('/input\s*Text/iu', $source) && preg_match_all('/input\s*Text/iu', $source) >= 2) {
        $first = ia_blank_item('text', $label !== '' ? $label : '입력');
        $second = ia_blank_item('text', ($label !== '' ? $label : '입력').' 2');
        $first['name'] = ia_reserve_name($first['label'], $used, 'text');
        $second['name'] = ia_reserve_name($second['label'], $used, 'text');
        $first['hint'] = $hint;

        return [$first, $second];
    }

    $item = ia_blank_item($kind, $label !== '' ? $label : $source);
    $item['hint'] = $hint;
    if ($kind === 'radio' || $kind === 'select') {
        $item['options'] = $kind === 'radio' ? ia_radio_options($source) : ia_select_options($control !== '' ? $control : $source);
    }
    if ($kind === 'dates') {
        $range = ia_date_range($source, $label);
        $item['label'] = $range['label'];
        $item['start_label'] = $range['start'];
        $item['end_label'] = $range['end'];
        $item['start_name'] = ia_reserve_name($range['start'], $used, 'date');
        $item['end_name'] = ia_reserve_name($range['end'], $used, 'date');
        if (preg_match('/강의\s*기간/u', $item['label']) && ! isset($used['lecture_at'])) {
            unset($used[$item['start_name']]);
            $item['start_name'] = 'lecture_at';
            $used['lecture_at'] = true;
            unset($used[$item['end_name']]);
            $item['end_name'] = 'lecture_end';
            $used['lecture_end'] = true;
        }

        return [$item];
    }
    if ($kind === 'popup') {
        $popup = ia_popup_target($label.' '.$source);
        $item['label'] = $popup['label'];
        $item['popup_slug'] = $popup['slug'];
        $item['popup_column'] = $popup['column'];
        $item['fills'] = isset($used[$popup['name']]) ? $popup['name'] : '';
        $item['name'] = $item['fills'] !== '' ? $popup['name'] : ia_reserve_name($popup['name'], $used, 'popup');

        return [$item];
    }
    if ($kind === 'file') {
        $item['file'] = str_contains($item['label'], '썸네일') ? 'thumbnail' : (preg_match('/첨부/u', $item['label']) ? 'attachments' : 'custom');
        $item['name'] = $item['file'] === 'custom' ? ia_reserve_name($item['label'], $used, 'file') : $item['file'];
        $used[$item['name']] = true;
        if (preg_match('/(\d+)\s*개/u', $hint, $matched)) {
            $item['max_files'] = (int) $matched[1];
        }
        if (preg_match('/(\d+)\s*MB/iu', $hint, $matched)) {
            $item['max_mb'] = (int) $matched[1];
        }

        return [$item];
    }
    if ($kind === 'check' && preg_match('/공지/u', $item['label'])) {
        $item['name'] = 'is_notice';
        $used['is_notice'] = true;

        return [$item];
    }

    $item['name'] = ia_reserve_name($item['label'], $used, $kind);

    return [$item];
}

/**
 * @return array{label: string, start: string, end: string}
 */
function ia_date_range(string $line, string $label): array
{
    $clean = trim((string) preg_replace('/\((?:Calender|Calendar)\)/iu', '', $line));
    if (preg_match('/^(.*?)\s*:\s*(.+?)\s*~\s*(.+)$/u', $clean, $matched)) {
        return [
            'label' => trim($matched[1]) !== '' ? trim($matched[1]) : ($label !== '' ? $label : '기간'),
            'start' => trim($matched[2]),
            'end' => trim($matched[3]),
        ];
    }
    if (preg_match('/^(.+?)\s*~\s*(.+)$/u', $clean, $matched)) {
        return [
            'label' => $label !== '' ? $label : trim($matched[1]).' ~ '.trim($matched[2]),
            'start' => trim($matched[1]),
            'end' => trim($matched[2]),
        ];
    }

    return ['label' => $label !== '' ? $label : '기간', 'start' => '시작', 'end' => '종료'];
}

/**
 * @return list<string>
 */
function ia_radio_options(string $line): array
{
    $options = [];
    foreach (preg_split('/\(\s*Radio\s*Button\s*\)?/iu', $line) ?: [] as $part) {
        $part = trim((string) $part, " ,()（）");
        if ($part !== '') {
            $options[] = $part;
        }
    }

    return $options;
}

/**
 * @return list<string>
 */
function ia_select_options(string $line): array
{
    $body = $line;
    if (preg_match('/\(([^)]*,[^)]*)\)/u', $line, $matched)) {
        $body = $matched[1];
    }
    $body = preg_replace('/[\({]?\s*Select\s*Box\s*[\)\}]?/iu', '', $body) ?? $body;
    $body = preg_replace('/^[^:]*:/u', '', $body) ?? $body;
    $options = [];
    foreach (preg_split('/[,，]/u', $body) ?: [] as $option) {
        $option = trim((string) $option);
        if ($option !== '' && $option !== '전체') {
            $options[] = $option;
        }
    }

    return $options;
}

/**
 * @return array{name: string, label: string, slug: string, column: string}
 */
function ia_popup_target(string $text): array
{
    if (preg_match('/강의/u', $text) && ! preg_match('/강사/u', $text)) {
        return ['name' => 'lecture', 'label' => '강의 추가하기', 'slug' => 'lecture', 'column' => '강의명'];
    }

    return ['name' => 'instructor', 'label' => '강사 추가하기', 'slug' => 'instructor', 'column' => '강사명'];
}

/**
 * @param  array<string, bool>  $used
 */
function ia_reserve_name(string $label, array &$used, string $kind): string
{
    $plain = preg_replace('/\s+/u', '', $label) ?? $label;
    if ($kind === 'editor' || preg_match('/내용|답변|강의구성/u', $plain)) {
        $name = 'content';
    } elseif (str_contains($plain, '제목')) {
        $name = 'title';
    } elseif (str_contains($plain, '썸네일')) {
        $name = 'thumbnail';
    } elseif (str_contains($plain, '첨부')) {
        $name = 'attachments';
    } elseif ($plain === '작성자') {
        $name = 'author_name';
    } elseif (str_contains($plain, '카테고리')) {
        $name = 'category';
    } elseif (preg_match('/공지글/u', $plain)) {
        $name = 'is_notice';
    } elseif (preg_match('/결제.*금액|결제금액/u', $plain)) {
        $name = 'amount';
    } elseif (preg_match('/금액|가격/u', $plain)) {
        $name = 'price';
    } elseif (preg_match('/캘린더등록/u', $plain)) {
        $name = 'calendar_flag';
    } elseif (preg_match('/강사명|^강사$/u', $plain)) {
        $name = 'instructor';
    } elseif (str_contains($plain, '강의명')) {
        $name = 'lecture';
    } elseif (str_contains($plain, '체크인')) {
        $name = 'check_in';
    } elseif (str_contains($plain, '체크아웃')) {
        $name = 'check_out';
    } elseif (in_array($kind, ['popup'], true) && in_array($label, ['instructor', 'lecture'], true)) {
        $name = $label;
    } else {
        $column = ia_column($label, count($used) + 1);
        $name = str_starts_with($column['name'], 'col_') ? 'f_'.substr(md5($plain), 0, 8) : $column['name'];
    }

    $base = $name;
    $suffix = 2;
    while (isset($used[$name])) {
        $name = $base.'_'.$suffix;
        $suffix++;
    }
    $used[$name] = true;

    return $name;
}

/**
 * @param  list<array<string, string>>  $popups
 */
function ia_write_entry_forms(string $slug, array $spec, array $popups, string $formText = '', array $orderedFields = []): void
{
    $directory = resource_path('views/backoffice/board-posts/'.$slug);
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    $items = ia_description_items($formText);
    if (ia_description_has_inputs($items)) {
        file_put_contents($directory.'/create.blade.php', ia_described_form($items, false));
        file_put_contents($directory.'/edit.blade.php', ia_described_form($items, true));

        return;
    }

    file_put_contents($directory.'/create.blade.php', ia_entry_form($spec, $popups, false, $formText, $orderedFields));
    file_put_contents($directory.'/edit.blade.php', ia_entry_form($spec, $popups, true, $formText, $orderedFields));
}

/**
 * 클로드가 컴포넌트 규칙으로 돌려준 입력 순서를 유지하고, 목록에만 있는 항목은 뒤에 붙입니다.
 *
 * @param  list<array<string, mixed>>  $claudeFields
 * @param  array<string, mixed>  $spec
 * @return list<array<string, mixed>>
 */
function ia_ordered_form_fields(array $claudeFields, array $spec): array
{
    $ordered = [];
    foreach ($claudeFields as $field) {
        if (! is_array($field) || ! in_array($field['show'] ?? 'both', ['form', 'both'], true)) {
            continue;
        }
        $ordered[] = $field;
    }
    foreach ($spec['columns'] ?? [] as $column) {
        if (($column['source'] ?? '') !== 'custom') {
            continue;
        }
        foreach ($ordered as $field) {
            if (($field['name'] ?? '') === $column['name'] || ($field['label'] ?? '') === $column['label']) {
                continue 2;
            }
        }
        $type = 'text';
        $options = '';
        foreach ($spec['selects'] ?? [] as $select) {
            if ($select['name'] === $column['name']) {
                $type = 'select';
                $options = implode(',', array_values(array_filter($select['options'], static fn (string $option): bool => $option !== '전체')));
            }
        }
        if (str_contains((string) $column['label'], '썸네일')) {
            $type = 'file';
        } elseif ($type === 'text' && preg_match('/일시|일자|등록일|가입일/u', (string) $column['label'])) {
            $type = 'date';
        }
        $ordered[] = ia_field_row((string) $column['name'], (string) $column['label'], $type, $options);
    }

    return $ordered;
}

function ia_write_picker_forms(string $slug, array $popups): void
{
    ia_write_entry_forms($slug, ['columns' => []], $popups);
}

/**
 * @param  list<array<string, string>>  $popups
 */
function ia_entry_form(array $spec, array $popups, bool $edit, string $formText = '', array $orderedFields = []): string
{
    return ia_picker_form($spec, $popups, $edit, $formText, $orderedFields);
}

/**
 * @param  list<array<string, mixed>>  $items
 */
function ia_described_form(array $items, bool $edit): string
{
    $fields = ia_layout_described_items($items, $edit);
    $modals = '';
    $showThumbnail = false;
    $showAttachments = false;
    $showEditor = false;
    $showCustomFile = false;
    foreach ($items as $item) {
        if (($item['kind'] ?? '') === 'popup') {
            $modals .= ia_picker_modal([
                'name' => (string) ($item['fills'] !== '' ? $item['fills'] : $item['name']),
                'label' => (string) $item['label'],
                'slug' => (string) $item['popup_slug'],
                'column' => (string) $item['popup_column'],
            ]);
        }
        if (($item['file'] ?? '') === 'thumbnail') {
            $showThumbnail = true;
        }
        if (($item['file'] ?? '') === 'attachments') {
            $showAttachments = true;
        }
        if (($item['file'] ?? '') === 'custom') {
            $showCustomFile = true;
        }
        if (($item['kind'] ?? '') === 'editor') {
            $showEditor = true;
        }
    }

    $extraStyles = $modals !== '' ? "\n    <link rel=\"stylesheet\" href=\"{{ asset('css/backoffice/approval.css') }}?v=2\">" : '';
    $extraScripts = ($showThumbnail || $showAttachments) ? "\n<script src=\"{{ asset('js/backoffice/board-post-form.js') }}\"></script>" : '';
    $editorAssets = $showEditor ? "\n<x-backoffice-ckeditor-assets />" : '';
    $customFileScript = '';
    if ($showCustomFile) {
        $customFileScript = <<<'JS'
<script>
document.querySelectorAll('[data-ia-file]').forEach(function (box) {
    var input = box.querySelector('input[type="file"]');
    if (!input) return;
    box.addEventListener('dragover', function (event) { event.preventDefault(); box.classList.add('board-file-drag-over'); });
    box.addEventListener('dragleave', function () { box.classList.remove('board-file-drag-over'); });
    box.addEventListener('drop', function (event) {
        event.preventDefault();
        box.classList.remove('board-file-drag-over');
        if (event.dataTransfer && event.dataTransfer.files.length) input.files = event.dataTransfer.files;
    });
});
</script>
JS;
    }
    $action = $edit
        ? "{{ route('backoffice.board-posts.update', [\$board->slug, \$post->id]) }}"
        : "{{ route('backoffice.board-posts.store', \$board->slug) }}";
    $method = $edit ? "\n                @method('PUT')" : '';

    return <<<BLADE
@extends('backoffice.layouts.app')

@section('title', \$board->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">{$extraStyles}
@endsection

@section('content')
@php
    \$iaSaved = [];
    if (isset(\$post)) {
        \$decoded = json_decode((string) (\$post->custom_fields ?? ''), true);
        \$iaSaved = is_array(\$decoded) ? \$decoded : [];
    }
@endphp
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.board-posts.index', \$board->slug) }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> 목록으로</a>
    </div>
    <div class="board-card">
        <div class="board-card-body">
            @if (\$errors->any())
                <div class="board-alert board-alert-danger"><ul>@foreach (\$errors->all() as \$error)<li>{{ \$error }}</li>@endforeach</ul></div>
            @endif
            <form action="{$action}" method="POST" enctype="multipart/form-data">
                @csrf{$method}
                {$fields}
                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <a href="{{ route('backoffice.board-posts.index', \$board->slug) }}" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>
{$modals}
@endsection

@section('scripts')
{$editorAssets}{$extraScripts}{$customFileScript}
<script>
document.querySelectorAll('[data-ia-open]').forEach(function (button) {
    button.addEventListener('click', function () {
        var popup = document.getElementById('ia-popup-' + button.dataset.iaOpen);
        if (popup) popup.hidden = false;
    });
});
document.querySelectorAll('[data-ia-clear]').forEach(function (button) {
    button.addEventListener('click', function () {
        var name = button.dataset.iaClear;
        var input = document.getElementById('custom_field_' + name);
        if (input) input.value = '';
        var picked = document.getElementById('ia-picked-' + name);
        if (picked) picked.textContent = '';
    });
});
document.querySelectorAll('[data-ia-pick]').forEach(function (button) {
    button.addEventListener('click', function () {
        var name = button.dataset.iaPick;
        var input = document.getElementById('custom_field_' + name);
        if (input) input.value = button.dataset.iaValue;
        var picked = document.getElementById('ia-picked-' + name);
        if (picked) picked.textContent = button.dataset.iaValue;
        if (button.dataset.iaCategory && document.getElementById('category')) {
            document.getElementById('category').value = button.dataset.iaCategory;
        }
        document.getElementById('ia-popup-' + name).hidden = true;
    });
});
document.querySelectorAll('[data-ia-close]').forEach(function (button) {
    button.addEventListener('click', function () {
        var popup = document.getElementById('ia-popup-' + button.dataset.iaClose);
        if (popup) popup.hidden = true;
    });
});
document.querySelectorAll('[data-ia-filter]').forEach(function (input) {
    input.addEventListener('input', function () {
        var keyword = input.value.trim().toLowerCase();
        document.querySelectorAll('#ia-popup-' + input.dataset.iaFilter + ' tr[data-ia-row]').forEach(function (row) {
            row.hidden = keyword !== '' && row.textContent.toLowerCase().indexOf(keyword) < 0;
        });
    });
});
</script>
@endsection
BLADE;
}

/**
 * 짧은 입력은 두 칸, 내용·파일·구역 제목은 한 줄입니다.
 *
 * @param  list<array<string, mixed>>  $items
 */
function ia_layout_described_items(array $items, bool $edit): string
{
    $html = '';
    $pair = [];
    $sectionCount = 0;
    $flush = function () use (&$html, &$pair, $edit): void {
        if ($pair === []) {
            return;
        }
        $html .= '<div class="board-form-row">';
        foreach ($pair as $item) {
            $html .= '<div class="board-form-col-6">'.ia_described_control($item, $edit).'</div>';
        }
        if (count($pair) === 1) {
            $html .= '<div class="board-form-col-6"></div>';
        }
        $html .= '</div>';
        $pair = [];
    };

    foreach ($items as $item) {
        if (! is_array($item)) {
            continue;
        }
        if (! ia_field_is_wide($item)) {
            $pair[] = $item;
            if (count($pair) === 2) {
                $flush();
            }
            continue;
        }
        $flush();
        if (($item['kind'] ?? '') === 'heading') {
            $sectionCount++;
            $margin = $sectionCount === 1 ? '0 0 0.75rem' : '1.35rem 0 0.75rem';
            $html .= '<div style="margin:'.$margin.';padding-bottom:0.45rem;border-bottom:1px solid #dee2e6;font-size:1rem;font-weight:700">'.e((string) ($item['label'] ?? '')).'</div>';
            continue;
        }
        $html .= ia_described_control($item, $edit);
    }
    $flush();

    return $html;
}

/**
 * @param  array<string, mixed>  $item
 */
function ia_field_is_wide(array $item): bool
{
    $kind = (string) ($item['kind'] ?? '');
    if (in_array($kind, ['heading', 'note', 'editor', 'file', 'dates'], true)) {
        return true;
    }
    $label = (string) ($item['label'] ?? '');
    if (preg_match('/내용|설명|상세|소개|주소|비고|메모|구성|본문|문의/u', $label)) {
        return true;
    }
    if (preg_match('/제목|강의명|강좌명|상품명/u', $label)) {
        return true;
    }
    if ($kind !== 'select') {
        return false;
    }
    $longest = 0;
    foreach ((array) ($item['options'] ?? []) as $option) {
        $longest = max($longest, mb_strlen(trim((string) $option)));
    }

    return $longest >= 16;
}

/**
 * 예상 글자 수에 맞춰 입력창 너비를 정합니다. 빈 문자열이면 칸을 채웁니다.
 *
 * @param  array<string, mixed>  $item
 */
function ia_input_max_width(array $item): string
{
    $kind = (string) ($item['kind'] ?? '');
    if (in_array($kind, ['heading', 'note', 'editor', 'file', 'check', 'radio', 'popup'], true) || ia_field_is_wide($item)) {
        return '';
    }
    $label = (string) ($item['label'] ?? '');
    if ($kind === 'dates') {
        return '26rem';
    }
    if ($kind === 'date') {
        return '11rem';
    }
    if ($kind === 'select') {
        $longest = 0;
        foreach ((array) ($item['options'] ?? []) as $option) {
            $longest = max($longest, mb_strlen(trim((string) $option)));
        }
        if ($longest <= 3) {
            return '8rem';
        }
        if ($longest <= 8) {
            return '12rem';
        }

        return '16rem';
    }
    if (preg_match('/성별|여부|인원|수량/u', $label)) {
        return '8rem';
    }
    if (preg_match('/연락처|전화|휴대폰|금액|가격|요금|번호|코드|아이디|일시/u', $label)) {
        return '14rem';
    }
    if (preg_match('/이메일|메일/u', $label)) {
        return '18rem';
    }
    if (preg_match('/이름|성명|닉네임|작성자/u', $label)) {
        return '12rem';
    }
    if (preg_match('/병원|소속|상호|업체/u', $label)) {
        return '16rem';
    }

    return '16rem';
}

/**
 * @param  array<string, mixed>  $item
 */
function ia_limit_group(array $item, string $html): string
{
    $max = ia_input_max_width($item);
    $open = '<div class="board-form-group">';
    if ($max === '' || ! str_starts_with($html, $open)) {
        return $html;
    }

    return '<div class="board-form-group" style="max-width:'.$max.'">'.substr($html, strlen($open));
}

/**
 * @param  array<string, mixed>  $item
 */
function ia_described_control(array $item, bool $edit): string
{
    $kind = (string) ($item['kind'] ?? '');
    $label = e((string) ($item['label'] ?? ''));
    $hint = trim((string) ($item['hint'] ?? ''));
    $hintHtml = $hint !== '' ? '<p class="board-form-text">'.e($hint).'</p>' : '';
    if ($kind === 'heading') {
        return '<div style="margin:1.35rem 0 0.75rem;padding-bottom:0.45rem;border-bottom:1px solid #dee2e6;font-size:1rem;font-weight:700">'.$label.'</div>';
    }
    if ($kind === 'note') {
        return '<p class="board-form-text">'.$label.'</p>';
    }
    if ($kind === 'dates') {
        $start = ia_described_date((string) $item['start_name'], (string) $item['start_label'], $edit);
        $end = ia_described_date((string) $item['end_name'], (string) $item['end_label'], $edit);

        return ia_limit_group($item, '<div class="board-form-group"><label class="board-form-label">'.$label.'</label><div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">'.$start.'<span>~</span>'.$end.'</div>'.$hintHtml.'</div>');
    }
    if ($kind === 'popup') {
        $name = e((string) (($item['fills'] ?? '') !== '' ? $item['fills'] : $item['name']));
        $hidden = ($item['fills'] ?? '') === ''
            ? '<input type="hidden" name="custom_field_'.$name.'" id="custom_field_'.$name.'" value="{{ old(\'custom_field_'.$name.'\', $iaSaved[\''.$name.'\'] ?? \'\') }}"><span id="ia-picked-'.$name.'">{{ old(\'custom_field_'.$name.'\', $iaSaved[\''.$name.'\'] ?? \'\') }}</span>'
            : '';

        return ia_limit_group($item, '<div class="board-form-group"><label class="board-form-label">'.$label.'</label><div class="board-page-buttons">'.$hidden.'<button type="button" class="btn btn-primary btn-sm" data-ia-open="'.$name.'">'.$label.'</button></div>'.$hintHtml.'</div>');
    }
    if ($kind === 'file') {
        return ia_described_file($item);
    }

    $name = (string) ($item['name'] ?? '');
    $inputName = e(ia_input_name($name));
    $inputId = e(ia_input_id($name));
    $current = ia_old_expression($name, $edit);
    if ($kind === 'radio' || $kind === 'select') {
        $html = ia_described_choice($item, $inputName, $inputId, $current);
        if ($hintHtml !== '') {
            $html = preg_replace('/<\/label>/', '</label>'.$hintHtml, $html, 1) ?? $html;
        }

        return ia_limit_group($item, $html);
    }
    if ($kind === 'editor') {
        return '<div class="board-form-group"><label class="board-form-label" for="'.$inputId.'">'.$label.' <span class="required">*</span></label><textarea class="board-form-control board-form-textarea" id="'.$inputId.'" name="'.$inputName.'" rows="12" data-backoffice-ckeditor data-source-editing="true" required>{{ '.$current.' }}</textarea>'.$hintHtml.'</div>';
    }
    if ($kind === 'check') {
        $checked = $name === 'is_notice'
            ? "@checked(old('is_notice', isset(\$post) ? \$post->is_notice : 0) == 1)"
            : '@checked('.$current.' == \'1\')';

        return ia_limit_group($item, '<div class="board-form-group"><div class="board-checkbox-item"><input type="checkbox" class="board-checkbox-input" id="'.$inputId.'" name="'.$inputName.'" value="1" '.$checked.'><label for="'.$inputId.'" class="board-form-label">'.$label.'</label></div>'.$hintHtml.'</div>');
    }
    if ($kind === 'date') {
        return ia_limit_group($item, '<div class="board-form-group"><label class="board-form-label" for="'.$inputId.'">'.$label.'</label>'.ia_described_date($name, '', $edit).$hintHtml.'</div>');
    }

    $required = in_array($name, ['title', 'content'], true) ? ' required' : '';
    $star = $required !== '' ? ' <span class="required">*</span>' : '';

    return ia_limit_group($item, '<div class="board-form-group"><label class="board-form-label" for="'.$inputId.'">'.$label.$star.'</label><input type="text" class="board-form-control" id="'.$inputId.'" name="'.$inputName.'" value="{{ '.$current.' }}"'.$required.'>'.$hintHtml.'</div>');
}

/**
 * @param  array<string, mixed>  $item
 */
function ia_described_choice(array $item, string $inputName, string $inputId, string $current): string
{
    $label = e((string) $item['label']);
    if (($item['kind'] ?? '') === 'select') {
        $options = '<option value="">선택</option>';
        foreach ((array) $item['options'] as $option) {
            $quoted = str_replace(['\\', '\''], ['\\\\', '\\\''], (string) $option);
            $options .= '<option value="'.e((string) $option).'" @selected('.$current.' == \''.$quoted.'\')>'.e((string) $option).'</option>';
        }

        return '<div class="board-form-group"><label class="board-form-label" for="'.$inputId.'">'.$label.'</label><select class="board-form-control" id="'.$inputId.'" name="'.$inputName.'">'.$options.'</select></div>';
    }

    $radios = '';
    foreach ((array) $item['options'] as $index => $option) {
        $quoted = str_replace(['\\', '\''], ['\\\\', '\\\''], (string) $option);
        $optionId = $inputId.'_'.$index;
        $radios .= '<div class="board-option-item"><input type="radio" id="'.$optionId.'" name="'.$inputName.'" value="'.e((string) $option).'" @checked('.$current.' == \''.$quoted.'\')><label for="'.$optionId.'">'.e((string) $option).'</label></div>';
    }

    return '<div class="board-form-group"><label class="board-form-label">'.$label.'</label><div class="board-options-list board-options-horizontal">'.$radios.'</div></div>';
}

function ia_described_date(string $name, string $label, bool $edit): string
{
    $inputName = e(ia_input_name($name));
    $inputId = e(ia_input_id($name));
    $current = ia_old_expression($name, $edit);
    $caption = $label !== '' ? '<span class="board-form-text">'.e($label).'</span>' : '';

    return $caption.'<input type="date" class="board-form-control" id="'.$inputId.'" name="'.$inputName.'" value="{{ '.$current.' }}" style="width:11rem">';
}

/**
 * @param  array<string, mixed>  $item
 */
function ia_described_file(array $item): string
{
    $label = e((string) $item['label']);
    $hint = trim((string) ($item['hint'] ?? ''));
    $mode = (string) ($item['file'] ?? 'custom');
    if ($mode === 'thumbnail') {
        return <<<HTML
<div class="board-form-group">
    <label for="thumbnail" class="board-form-label">{$label}</label>
    <div class="board-file-upload">
        <div class="board-file-input-wrapper">
            <input type="file" class="board-file-input" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.gif">
            <div class="board-file-input-content">
                <i class="fas fa-image"></i>
                <span class="board-file-input-text">썸네일 이미지를 선택하거나 여기로 드래그하세요</span>
                <span class="board-file-input-subtext">JPG, PNG, GIF 파일만 가능 (최대 5MB)</span>
            </div>
        </div>
        <div class="board-file-preview" id="thumbnailPreview"></div>
    </div>
</div>
HTML;
    }
    if ($mode === 'attachments') {
        $maxFiles = (int) ($item['max_files'] ?? 5);
        $maxMb = (int) ($item['max_mb'] ?? 10);
        $sub = e('최대 '.$maxFiles.'개, 각 파일 '.$maxMb.'MB 이하');
        $note = $hint !== '' ? '<p class="board-form-text">'.e($hint).'</p>' : '';

        return <<<HTML
<div class="board-form-group">
    <label class="board-form-label">{$label}</label>
    {$note}
    <div class="board-file-upload">
        <div class="board-file-input-wrapper">
            <input type="file" class="board-file-input" id="attachments" name="attachments[]" multiple>
            <div class="board-file-input-content">
                <i class="fas fa-cloud-upload-alt"></i>
                <span class="board-file-input-text">파일을 선택하거나 여기로 드래그하세요</span>
                <span class="board-file-input-subtext">{$sub}</span>
            </div>
        </div>
        <div class="board-file-preview" id="filePreview"></div>
    </div>
</div>
HTML;
    }

    $name = (string) $item['name'];
    $inputName = e(ia_input_name($name));
    $inputId = e(ia_input_id($name));
    $note = $hint !== '' ? '<p class="board-form-text">'.e($hint).'</p>' : '';

    return <<<HTML
<div class="board-form-group">
    <label class="board-form-label" for="{$inputId}">{$label}</label>
    {$note}
    @if(!empty(\$iaSaved['{$name}']))
        <p class="board-form-text">현재 파일: {{ \$iaSaved['{$name}'] }}</p>
        <input type="hidden" name="existing_custom_field_{$name}" value="{{ \$iaSaved['{$name}'] }}">
    @endif
    <div class="board-file-upload" data-ia-file="{$inputId}">
        <div class="board-file-input-wrapper">
            <input type="file" class="board-file-input" id="{$inputId}" name="{$inputName}">
            <div class="board-file-input-content">
                <i class="fas fa-cloud-upload-alt"></i>
                <span class="board-file-input-text">파일을 선택하거나 여기로 드래그하세요</span>
                <span class="board-file-input-subtext">파일을 선택해 주세요</span>
            </div>
        </div>
    </div>
</div>
HTML;
}

function ia_input_name(string $name): string
{
    return match ($name) {
        'title', 'content', 'category', 'author_name', 'thumbnail', 'is_notice' => $name,
        'attachments' => 'attachments[]',
        default => 'custom_field_'.$name,
    };
}

function ia_input_id(string $name): string
{
    return match ($name) {
        'title', 'content', 'category', 'author_name', 'thumbnail', 'attachments', 'is_notice' => $name,
        default => 'custom_field_'.$name,
    };
}

function ia_old_expression(string $name, bool $edit): string
{
    return match ($name) {
        'title' => "old('title', isset(\$post) ? \$post->title : '')",
        'content' => "old('content', isset(\$post) ? \$post->content : '')",
        'category' => "old('category', isset(\$post) ? \$post->category : '')",
        'author_name' => "old('author_name', isset(\$post) ? \$post->author_name : '')",
        default => "old('custom_field_{$name}', \$iaSaved['{$name}'] ?? '')",
    };
}

function ia_picker_form(array $spec, array $popups, bool $edit, string $formText = '', array $orderedFields = []): string
{
    $fields = '';
    $modals = '';
    $showThumbnail = str_contains($formText, '썸네일');
    $showContent = str_contains($formText, '내용') || str_contains($formText, 'Editor') || str_contains($formText, '글쓰기');
    $showTitle = str_contains($formText, '제목');
    $popupNames = array_column($popups, 'name');
    $selects = [];
    foreach ($spec['selects'] ?? [] as $select) {
        $selects[$select['name']] = $select;
    }
    if ($orderedFields !== []) {
        foreach ($orderedFields as $field) {
            $label = (string) ($field['label'] ?? '');
            $type = (string) ($field['type'] ?? 'text');
            if (str_contains($label, '제목')) {
                $showTitle = true;
            }
            if ($type === 'file' || str_contains($label, '썸네일')) {
                $showThumbnail = true;
                continue;
            }
            if ($type === 'editor' || in_array($label, ['내용', '본문'], true)) {
                $showContent = true;
                continue;
            }
            if ($type === 'popup' || in_array($field['name'] ?? '', $popupNames, true)) {
                continue;
            }
            $select = null;
            if ($type === 'select') {
                $select = [
                    'label' => $label,
                    'name' => (string) $field['name'],
                    'options' => array_values(array_filter(array_map('trim', explode(',', (string) ($field['options'] ?? ''))))),
                ];
            }
            $column = ['name' => (string) $field['name'], 'label' => $label, 'source' => 'custom'];
            if ($type !== 'date' && $type !== 'select' && preg_match('/일시|일자|등록일|가입일/u', $label)) {
                $type = 'date';
            }
            if ($type === 'date') {
                $safe = preg_replace('/[^a-z0-9_]/', '', (string) $field['name']) ?? '';
                $current = "old('custom_field_{$safe}', \$iaSaved['{$safe}'] ?? '')";
                $fields .= '<div class="board-form-group"><label class="board-form-label" for="custom_field_'.$safe.'">'.e($label).'</label><input type="date" class="board-form-control" id="custom_field_'.$safe.'" name="custom_field_'.$safe.'" value="{{ '.$current.' }}"></div>';
                continue;
            }
            $fields .= ia_form_control($column, $select);
        }
    } else {
        foreach ($spec['columns'] ?? [] as $column) {
            if (($column['source'] ?? '') === 'title') {
                $showTitle = true;
            }
            if (($column['name'] ?? '') === 'thumbnail' || str_contains((string) ($column['label'] ?? ''), '썸네일')) {
                $showThumbnail = true;
                continue;
            }
            if (($column['source'] ?? '') !== 'custom' || in_array($column['name'] ?? '', $popupNames, true)) {
                continue;
            }
            $fields .= ia_form_control($column, $selects[$column['name']] ?? null);
        }
    }
    foreach ($popups as $popup) {
        $name = e($popup['name']);
        $fields .= '<div class="board-form-group"><label class="board-form-label">'.e($popup['label']).'</label><input type="hidden" name="custom_field_'.$name.'" id="custom_field_'.$name.'" value="{{ old(\'custom_field_'.$name.'\', $iaSaved[\''.$name.'\'] ?? \'\') }}"><div class="board-page-buttons"><span id="ia-picked-'.$name.'">{{ old(\'custom_field_'.$name.'\', $iaSaved[\''.$name.'\'] ?? \'\') }}</span><button type="button" class="btn btn-primary btn-sm" data-ia-open="'.$name.'">'.e($popup['button']).'</button><button type="button" class="btn btn-secondary btn-sm" data-ia-clear="'.$name.'">지우기</button></div></div>';
        $modals .= ia_picker_modal($popup);
    }
    $thumbnail = '';
    if ($showThumbnail) {
        $thumbnail = <<<'HTML'
                <div class="board-form-group">
                    <label for="thumbnail" class="board-form-label">썸네일 이미지</label>
                    <div class="board-file-upload">
                        <div class="board-file-input-wrapper">
                            <input type="file" class="board-file-input" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.gif">
                            <div class="board-file-input-content">
                                <i class="fas fa-image"></i>
                                <span class="board-file-input-text">썸네일 이미지를 선택하거나 여기로 드래그하세요</span>
                                <span class="board-file-input-subtext">JPG, PNG, GIF 파일만 가능 (최대 5MB)</span>
                            </div>
                        </div>
                        <div class="board-file-preview" id="thumbnailPreview"></div>
                    </div>
                </div>
HTML;
    }
    $extraStyles = $modals !== '' ? "\n    <link rel=\"stylesheet\" href=\"{{ asset('css/backoffice/approval.css') }}?v=2\">" : '';
    $extraScripts = $showThumbnail ? "\n<script src=\"{{ asset('js/backoffice/board-post-form.js') }}\"></script>" : '';
    $editorAssets = $showContent ? "\n<x-backoffice-ckeditor-assets />" : '';

    $action = $edit
        ? "{{ route('backoffice.board-posts.update', [\$board->slug, \$post->id]) }}"
        : "{{ route('backoffice.board-posts.store', \$board->slug) }}";
    $method = $edit ? "\n                @method('PUT')" : '';
    $titleValue = $edit ? "{{ old('title', \$post->title) }}" : "{{ old('title') }}";
    $contentValue = $edit ? "{{ old('content', \$post->content) }}" : "{{ old('content') }}";
    $titleField = $showTitle
        ? '<div class="board-form-group"><label for="title" class="board-form-label">제목 <span class="required">*</span></label><input type="text" class="board-form-control" id="title" name="title" value="'.$titleValue.'" required></div>'
        : '';
    $contentField = $showContent
        ? '<div class="board-form-group"><label for="content" class="board-form-label">내용 <span class="required">*</span></label><textarea class="board-form-control board-form-textarea" id="content" name="content" rows="12" data-backoffice-ckeditor data-source-editing="true" required>'.$contentValue.'</textarea></div>'
        : '';
    $categoryHidden = '<input type="hidden" name="category" id="category" value="{{ old(\'category\', isset($post) ? $post->category : \'\') }}">';

    return <<<BLADE
@extends('backoffice.layouts.app')

@section('title', \$board->name)

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/backoffice/boards.css') }}">{$extraStyles}
@endsection

@section('content')
@php
    \$iaSaved = [];
    if (isset(\$post)) {
        \$decoded = json_decode((string) (\$post->custom_fields ?? ''), true);
        \$iaSaved = is_array(\$decoded) ? \$decoded : [];
    }
@endphp
<div class="board-container">
    <div class="board-header">
        <a href="{{ route('backoffice.board-posts.index', \$board->slug) }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> 목록으로</a>
    </div>
    <div class="board-card">
        <div class="board-card-body">
            @if (\$errors->any())
                <div class="board-alert board-alert-danger"><ul>@foreach (\$errors->all() as \$error)<li>{{ \$error }}</li>@endforeach</ul></div>
            @endif
            <form action="{$action}" method="POST" enctype="multipart/form-data">
                @csrf{$method}
                {$titleField}
                {$fields}
                {$thumbnail}
                {$contentField}
                {$categoryHidden}
                <div class="board-form-actions">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <a href="{{ route('backoffice.board-posts.index', \$board->slug) }}" class="btn btn-secondary">취소</a>
                </div>
            </form>
        </div>
    </div>
</div>
{$modals}
@endsection

@section('scripts')
{$editorAssets}{$extraScripts}
<script>
document.querySelectorAll('[data-ia-open]').forEach(function (button) {
    button.addEventListener('click', function () {
        var popup = document.getElementById('ia-popup-' + button.dataset.iaOpen);
        if (popup) popup.hidden = false;
    });
});
document.querySelectorAll('[data-ia-clear]').forEach(function (button) {
    button.addEventListener('click', function () {
        var name = button.dataset.iaClear;
        document.getElementById('custom_field_' + name).value = '';
        document.getElementById('ia-picked-' + name).textContent = '';
    });
});
document.querySelectorAll('[data-ia-pick]').forEach(function (button) {
    button.addEventListener('click', function () {
        var name = button.dataset.iaPick;
        document.getElementById('custom_field_' + name).value = button.dataset.iaValue;
        document.getElementById('ia-picked-' + name).textContent = button.dataset.iaValue;
        if (button.dataset.iaCategory && document.getElementById('category')) {
            document.getElementById('category').value = button.dataset.iaCategory;
        }
        document.getElementById('ia-popup-' + name).hidden = true;
    });
});
document.querySelectorAll('[data-ia-close]').forEach(function (button) {
    button.addEventListener('click', function () {
        var popup = document.getElementById('ia-popup-' + button.dataset.iaClose);
        if (popup) popup.hidden = true;
    });
});
document.querySelectorAll('[data-ia-filter]').forEach(function (input) {
    input.addEventListener('input', function () {
        var keyword = input.value.trim().toLowerCase();
        document.querySelectorAll('#ia-popup-' + input.dataset.iaFilter + ' tr[data-ia-row]').forEach(function (row) {
            row.hidden = keyword !== '' && row.textContent.toLowerCase().indexOf(keyword) < 0;
        });
    });
});
</script>
@endsection
BLADE;
}

/**
 * @param  array<string, string>  $popup
 */
function ia_picker_modal(array $popup): string
{
    $name = e($popup['name']);
    $slug = preg_replace('/[^a-z0-9-]/', '', $popup['slug']) ?? '';

    return <<<BLADE
<div class="approval-modal-backdrop" id="ia-popup-{$name}" hidden>
    <div class="approval-modal-panel" role="dialog" aria-modal="true">
        <div class="approval-modal-header">
            <h5>{$popup['label']} 선택</h5>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-ia-close="{$name}">닫기</button>
        </div>
        <div class="approval-modal-search">
            <input type="text" class="form-control" data-ia-filter="{$name}" placeholder="검색어">
            <button type="button" class="btn btn-primary btn-sm">검색</button>
        </div>
        <div class="table-responsive approval-modal-table-wrap">
            <table class="board-table">
                <thead><tr><th class="w10">번호</th><th>{$popup['column']}</th><th>카테고리</th><th class="w10">선택</th></tr></thead>
                <tbody>
                    @php
                        \$iaRows = Illuminate\Support\Facades\Schema::hasTable('board_{$slug}')
                            ? Illuminate\Support\Facades\DB::table('board_{$slug}')->whereNull('deleted_at')->orderByDesc('id')->limit(50)->get()
                            : collect();
                        \$iaSelects = [];
                        \$iaConfig = json_decode((string) (Illuminate\Support\Facades\DB::table('boards')->where('slug', '{$slug}')->value('custom_fields_config') ?? ''), true);
                        if (is_array(\$iaConfig)) {
                            foreach (\$iaConfig as \$iaField) {
                                if (is_array(\$iaField) && (\$iaField['type'] ?? '') === 'select' && preg_match('/카테고리|역할|분류/u', (string) (\$iaField['label'] ?? ''))) {
                                    \$iaSelects[] = (string) (\$iaField['name'] ?? '');
                                }
                            }
                        }
                        \$iaShown = 0;
                    @endphp
                    @foreach(\$iaRows as \$iaRow)
                        @php
                            \$iaCustom = json_decode((string) (\$iaRow->custom_fields ?? ''), true);
                            \$iaCustom = is_array(\$iaCustom) ? \$iaCustom : [];
                            \$iaName = trim((string) (\$iaCustom['name_ko'] ?? ''));
                            if (\$iaName === '') \$iaName = trim((string) (\$iaRow->title ?? ''));
                            \$iaCategory = trim((string) (\$iaRow->category ?? ''));
                            if (\$iaCategory === '') \$iaCategory = trim((string) (\$iaCustom['category'] ?? ''));
                            if (\$iaCategory === '' && isset(\$iaSelects)) {
                                foreach (\$iaSelects as \$iaSelectName) {
                                    \$iaPicked = trim((string) (\$iaCustom[\$iaSelectName] ?? ''));
                                    if (\$iaPicked !== '') { \$iaCategory = \$iaPicked; break; }
                                }
                            }
                        @endphp
                        @if(\$iaName !== '')
                            @php \$iaShown++; @endphp
                            <tr data-ia-row="{$name}">
                                <td>{{ \$iaShown }}</td>
                                <td>{{ \$iaName }}</td>
                                <td>{{ \$iaCategory }}</td>
                                <td><button type="button" class="btn btn-primary btn-sm" data-ia-pick="{$name}" data-ia-value="{{ \$iaName }}" data-ia-category="{{ \$iaCategory }}">선택</button></td>
                            </tr>
                        @endif
                    @endforeach
                    @if(\$iaShown === 0)
                        <tr><td colspan="4" class="text-center">등록된 {$popup['label']}가 없습니다.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
BLADE;
}

function ia_row_label(string $depth1, string $depth2): string
{
    $depth1 = trim($depth1);
    $depth2 = trim($depth2);
    if ($depth1 !== '' && $depth2 !== '' && (preg_match('/등록|상세|환불/u', $depth2))) {
        return $depth1;
    }
    $types = ['리스트', '목록', '상세', '등록', '수정', '삭제', '등록 / 수정', '등록/수정', '삭제/선택삭제'];
    if ($depth2 !== '' && ! in_array($depth2, $types, true)) {
        return $depth2;
    }
    if ($depth1 === '') {
        return $depth2;
    }
    if ($depth2 === '' || in_array($depth2, ['리스트', '목록'], true)) {
        return $depth1;
    }

    return trim($depth1.' '.$depth2);
}

function ia_seed_samples(): array
{
    $notes = [];
    foreach (App\Models\Board::query()->where('is_active', true)->get() as $board) {
        $slug = (string) $board->slug;
        if (! preg_match('/^[a-z0-9-]+$/', $slug) || $board->is_single_page) {
            if (preg_match('/^[a-z0-9-]+$/', $slug) && Illuminate\Support\Facades\Schema::hasTable('board_'.$slug)) {
                Illuminate\Support\Facades\DB::table('board_'.$slug)->where('author_name', '샘플')->delete();
            }
            continue;
        }
        if (! Illuminate\Support\Facades\Schema::hasTable('board_'.$slug)) {
            continue;
        }
        $table = 'board_'.$slug;
        try {
        Illuminate\Support\Facades\DB::table($table)->where('author_name', '샘플')->delete();
        $count = ia_sample_count($slug, (string) $board->name);
        $fields = is_array($board->custom_fields_config) ? $board->custom_fields_config : [];
        $categories = ia_sample_categories($slug);
        if ($categories === []) {
            $categories = ia_sample_role_options($fields);
        }
        $rows = [];
        for ($index = 1; $index <= $count; $index++) {
            $custom = ['ia_sample' => '1'];
            foreach ($fields as $field) {
                if (! is_array($field)) {
                    continue;
                }
                if (($field['type'] ?? '') === 'popup') {
                    $name = (string) ($field['name'] ?? '');
                    if ($name !== '') {
                        $custom[$name] = '샘플'.$index;
                    }
                    continue;
                }
                $name = (string) ($field['name'] ?? '');
                if ($name === '' || $name === 'thumbnail') {
                    continue;
                }
                $custom[$name] = ia_sample_field_value($field, $index);
            }
            $rows[] = [
                'title' => ia_sample_title((string) $board->name, $index),
                'content' => '샘플 내용입니다.',
                'author_name' => '샘플',
                'category' => $categories === [] ? null : $categories[$index % count($categories)],
                'custom_fields' => json_encode($custom, JSON_UNESCAPED_UNICODE),
                'thumbnail' => null,
                'attachments' => null,
                'is_notice' => 0,
                'is_secret' => 0,
                'is_active' => 1,
                'view_count' => 0,
                'sort_order' => 0,
                'created_at' => now()->subDays(($index * 11) % 180)->subHours($index % 18),
                'updated_at' => now(),
            ];
        }
        foreach (array_chunk($rows, 40) as $chunk) {
            Illuminate\Support\Facades\DB::table($table)->insert($chunk);
        }
        $notes[] = $board->name.': 샘플 '.$count.'건을 넣었습니다.';
        } catch (Throwable $exception) {
            $notes[] = $board->name.': 샘플을 넣지 못했습니다.';
        }
    }

    return $notes;
}

function ia_sample_count(string $slug, string $name): int
{
    $text = $slug.' '.$name;
    if (preg_match('/applicant|신청/u', $text)) {
        return 30;
    }
    if (preg_match('/member|회원|탈퇴/u', $text)) {
        return 20;
    }
    if (preg_match('/notice|faq|review|공지|후기/u', $text)) {
        return 22;
    }

    return 20;
}

function ia_sample_title(string $name, int $index): string
{
    $short = ['안내', '신청', '후기', '문의', '등록'];
    if ($index % 3 === 1) {
        return $short[$index % 5].' '.$index;
    }
    if ($index % 3 === 2) {
        return $name.' 샘플 항목 '.$index;
    }

    return $name.' 샘플 항목 '.$index.' 입니다. 목록에서 길이가 다른 제목이 함께 보이도록 만든 샘플입니다.';
}

/**
 * @param  array<string, mixed>  $field
 */
function ia_sample_field_value(array $field, int $index): string
{
    $label = (string) ($field['label'] ?? '');
    $type = (string) ($field['type'] ?? 'text');
    $rawOptions = $field['options'] ?? '';
    $options = array_values(array_filter(array_map(
        'trim',
        is_array($rawOptions) ? $rawOptions : explode(',', (string) $rawOptions)
    )));
    if ($type === 'select' && $options !== []) {
        return $options[$index % count($options)];
    }
    if ($type === 'date' || preg_match('/일시|일자|가입일|등록일/u', $label)) {
        return now()->subDays(($index * 9) % 160)->format('Y-m-d');
    }
    if (preg_match('/여부|유무/u', $label)) {
        return $index % 5 === 0 ? 'N' : 'Y';
    }
    if (preg_match('/가격|금액/u', $label)) {
        return (string) (10000 * $index);
    }
    if (str_contains($label, '이메일')) {
        return 'sample'.$index.'@example.com';
    }
    if (str_contains($label, '연락처') || str_contains($label, '전화')) {
        return '000-0000-'.str_pad((string) $index, 4, '0', STR_PAD_LEFT);
    }

    return $index % 2 === 0 ? '샘플'.$index : '샘플 항목 '.$index.'번';
}

/**
 * @param  list<array<string, mixed>>  $fields
 * @return list<string>
 */
function ia_sample_role_options(array $fields): array
{
    foreach ($fields as $field) {
        if (! is_array($field) || ($field['type'] ?? '') !== 'select') {
            continue;
        }
        if (! preg_match('/카테고리|역할|분류/u', (string) ($field['label'] ?? ''))) {
            continue;
        }
        $raw = $field['options'] ?? '';
        $options = array_values(array_filter(array_map(
            'trim',
            is_array($raw) ? $raw : explode(',', (string) $raw)
        )));
        if ($options !== []) {
            return $options;
        }
    }

    return [];
}

/**
 * @return list<string>
 */
function ia_sample_categories(string $slug): array
{
    $path = resource_path('views/backoffice/board-posts/'.$slug.'/index.blade.php');
    if (! is_file($path)) {
        return [];
    }
    $text = (string) file_get_contents($path);
    if (! preg_match('/name="category".*?<\/select>/s', $text, $matched)) {
        return [];
    }
    preg_match_all('/<option value="([^"]+)"/', $matched[0], $options);

    return array_values(array_filter(array_map(
        static fn (string $option): string => html_entity_decode($option, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        $options[1] ?? []
    )));
}
