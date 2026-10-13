<?php

/**
 * IA 목록 설명으로 참고 화면과 같은 검색, 버튼, 컬럼을 만듭니다.
 *
 * @return array<string, mixed>|null
 */
function ia_list_spec(string $text): ?array
{
    $text = trim($text);
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
        if ($position !== false) {
            $actions[$position] = $action;
        }
    }
    ksort($actions);
    $actions = array_values($actions);
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
    if ($showKeyword && $keywordOptions === []) {
        $keywordOptions[] = ['label' => '전체', 'value' => 'all'];
    }

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

    $dateLabel = '';
    if (preg_match('/등록일\s*시작/u', $text)) {
        $dateLabel = '등록일';
    } elseif (preg_match('/([가-힣A-Za-z][가-힣A-Za-z0-9\s]{1,20}?)\s*(?:시작)?\s*[:：]?\s*(?:\([^)]*\)|\[[^\]]*\]|\([Cc]all?ender\))\s*~/u', $text, $matched)) {
        $dateLabel = trim(preg_replace('/시작$/u', '', trim($matched[1])) ?? trim($matched[1]));
    }

    $dateField = '';
    $dateColumn = null;
    if ($dateLabel !== '') {
        foreach ($columns as $column) {
            if ($column['label'] === $dateLabel || str_contains($column['label'], $dateLabel) || str_contains($dateLabel, $column['label'])) {
                $dateColumn = $column;
                break;
            }
        }
    }
    if ($dateColumn === null) {
        foreach ($columns as $column) {
            if (preg_match('/일시|가입일|등록일/u', $column['label'])) {
                $dateColumn = $column;
                $dateLabel = $column['label'];
                break;
            }
        }
    }
    if ($dateColumn !== null && $dateColumn['source'] !== 'created_at') {
        $dateField = $dateColumn['name'];
    }

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
    if ($spec['date_label'] !== '') {
        $dateFilter .= '<div class="filter-group"><label class="filter-label">'.e($spec['date_label']).'</label><div style="display:flex;gap:8px;align-items:center"><input type="date" name="start_date" class="filter-input" value="{{ request(\'start_date\') }}"><span>~</span><input type="date" name="end_date" class="filter-input" value="{{ request(\'end_date\') }}"></div>';
        if ($spec['date_field'] !== '') {
            $dateFilter .= '<input type="hidden" name="date_field" value="'.e($spec['date_field']).'">';
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
        foreach ($spec['keyword_options'] as $option) {
            $keyword .= '<option value="'.e($option['value']).'" @selected(request(\'search_type\', \'all\') == \''.e($option['value']).'\')>'.e($option['label']).'</option>';
        }
        if ($keyword !== '') {
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
        $tools .= '<button type="button" id="bulk-delete-btn" class="btn btn-danger"><i class="fas fa-trash"></i> 선택 삭제</button>';
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
        '상세' => '<a href="{{ route(\'backoffice.board-posts.show\', [$board->slug, $post->id]) }}" class="btn btn-primary btn-sm">상세</a>',
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
        $boardService->updateBoard($board, [
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
 * @param  list<array<string, string>>  $popups
 */
function ia_write_entry_forms(string $slug, array $spec, array $popups, string $formText = ''): void
{
    $directory = resource_path('views/backoffice/board-posts/'.$slug);
    if (! is_dir($directory)) {
        mkdir($directory, 0775, true);
    }

    file_put_contents($directory.'/create.blade.php', ia_entry_form($spec, $popups, false, $formText));
    file_put_contents($directory.'/edit.blade.php', ia_entry_form($spec, $popups, true, $formText));
}

function ia_write_picker_forms(string $slug, array $popups): void
{
    ia_write_entry_forms($slug, ['columns' => []], $popups);
}

/**
 * @param  list<array<string, string>>  $popups
 */
function ia_entry_form(array $spec, array $popups, bool $edit, string $formText = ''): string
{
    return ia_picker_form($spec, $popups, $edit, $formText);
}

function ia_picker_form(array $spec, array $popups, bool $edit, string $formText = ''): string
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
        if (! preg_match('/^[a-z0-9-]+$/', $slug) || in_array($slug, ['companyintro', 'company-intro', 'privacy-policy', 'terms', 'email-policy', 'register', 'announcements', 'calendar', 'login', 'dashboard'], true)) {
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

    return array_values(array_filter($options[1] ?? []));
}
