<?php

require_once __DIR__.'/ia-list-screen.php';

if (defined('IA_APPLY_AS_LIBRARY')) {
    return;
}

$pubDir = getenv('PUB_DIR');
$payloadPath = getenv('IA_PAYLOAD');
if (! is_string($pubDir) || ! is_file($pubDir.'/artisan') || ! is_string($payloadPath) || ! is_file($payloadPath)) {
    fwrite(STDERR, "퍼블 경로 또는 IA 결과가 없습니다.\n");
    exit(1);
}

chdir($pubDir);
require $pubDir.'/vendor/autoload.php';
$app = require $pubDir.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$approvalSource = dirname(__DIR__).'/public/css/backoffice/approval.css';
$approvalTarget = public_path('css/backoffice/approval.css');
if (is_file($approvalSource)) {
    if (! is_dir(dirname($approvalTarget))) {
        mkdir(dirname($approvalTarget), 0775, true);
    }
    copy($approvalSource, $approvalTarget);
}

$items = json_decode((string) file_get_contents($payloadPath), true);
if (! is_array($items)) {
    fwrite(STDERR, "IA 결과를 읽지 못했습니다.\n");
    exit(1);
}

$skin = App\Models\BoardSkin::query()->where('is_default', true)->first()
    ?? App\Models\BoardSkin::query()->orderBy('id')->first();
if (! $skin) {
    fwrite(STDERR, "기본 게시판 스킨이 없습니다.\n");
    exit(1);
}

$boardService = new App\Services\Backoffice\BoardService();
$fileService = new App\Services\Backoffice\BoardFileGeneratorService();
$created = [];
$notes = [];
$index = 1;

/**
 * @param  array<string, mixed>  $item
 */
function ia_board_label(array $item): string
{
    $depth1 = trim((string) ($item['depth1'] ?? ''));
    $depth2 = trim((string) ($item['depth2'] ?? ''));
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

    return $depth1.' '.$depth2;
}

foreach ($items as $item) {
    if (! is_array($item)) {
        continue;
    }

    $label = ia_board_label($item);
    $slugSource = trim((string) ($item['name'] ?? ''));
    if ($label === '') {
        $label = $slugSource;
    }
    if ($label === '') {
        continue;
    }
    if ($label === '대시보드' || $label === '로그인' || in_array(trim((string) ($item['depth1'] ?? '')), ['대시보드', '로그인'], true)) {
        continue;
    }
    $depth1 = trim((string) ($item['depth1'] ?? ''));
    $depth2 = trim((string) ($item['depth2'] ?? ''));
    if ($depth1 !== '' && $depth2 !== '' && $depth2 !== $depth1 && preg_match('/등록|상세|환불/u', $depth2)) {
        continue;
    }
    if (in_array($depth2, ['삭제', '삭제/선택삭제', '삭제 / 선택삭제'], true) || str_ends_with($label, '삭제/선택삭제')) {
        continue;
    }

    $slug = Illuminate\Support\Str::slug($slugSource !== '' ? $slugSource : $label);
    if ($slug === '') {
        $slug = 'ia-'.$index;
    }
    $index++;

    $fields = [];
    foreach ((array) ($item['fields'] ?? []) as $field) {
        if (! is_array($field)) {
            continue;
        }
        $fieldName = strtolower((string) preg_replace('/[^a-zA-Z0-9_]/', '', (string) ($field['name'] ?? '')));
        $fieldLabel = trim((string) ($field['label'] ?? ''));
        $type = (string) ($field['type'] ?? '');
        if (str_contains($fieldLabel, '썸네일')) {
            $type = 'file';
        }
        if ($type === '' && preg_match('/강사|강의/u', $fieldLabel) && str_contains($fieldLabel, '추가')) {
            $type = 'popup';
        }
        if ($fieldName === '' || $fieldLabel === '' || ! in_array($type, ['text', 'select', 'checkbox', 'radio', 'date', 'editor', 'file', 'popup'], true)) {
            continue;
        }
        if (in_array($fieldLabel, ['제목', '내용', '본문'], true) || in_array($fieldName, ['title', 'content'], true)) {
            continue;
        }
        $options = $field['options'] ?? '';
        if (is_array($options)) {
            $options = implode(',', $options);
        }
        $show = (string) ($field['show'] ?? 'both');
        if (! in_array($show, ['list', 'form', 'search', 'both'], true)) {
            $show = 'both';
        }
        foreach ($fields as $existing) {
            if ($existing['name'] === $fieldName || $existing['label'] === $fieldLabel) {
                continue 2;
            }
        }
        $fields[] = [
            'name' => $fieldName,
            'label' => $fieldLabel,
            'type' => $type,
            'required' => (bool) ($field['required'] ?? false),
            'options' => trim((string) $options),
            'show' => $show,
            'max_length' => null,
            'placeholder' => null,
        ];
    }

    $storedFields = array_values(array_filter(
        $fields,
        static fn (array $field): bool => $field['show'] !== 'search'
    ));

    try {
        $listText = (string) ($item['list_text'] ?? '');
        $formText = (string) ($item['form_text'] ?? '');
        $spec = ia_list_spec($listText);
        $popups = ia_form_popups($formText);
        $described = ia_description_items($formText);
        $useDescribed = ia_description_has_inputs($described);
        $customFields = $useDescribed ? ia_fields_from_items($described) : ($spec ? ia_custom_fields($spec) : $storedFields);
        $payload = [
            'name' => $label,
            'is_active' => true,
            'is_single_page' => (bool) ($item['single'] ?? false),
            'permission_read' => ($item['read'] ?? '') === 'all' ? 'all' : 'member',
            'list_count' => 10,
            'field_config' => $useDescribed ? ia_field_config_from_items($described) : ($spec ? ia_field_config_from($spec, $formText) : ia_field_config()),
            'custom_fields_config' => $useDescribed ? $customFields : ia_merge_popups($customFields, $popups),
        ];
        $board = App\Models\Board::withTrashed()->where('slug', $slug)->first();
        if ($board) {
            if ($board->trashed()) {
                $board->restore();
            }
            $boardService->updateBoard($board, $payload);
            refresh_board_views($fileService, $board);
            $created[] = $label.': 기존 게시판을 IA에 맞췄습니다.';
        } else {
            $board = $boardService->createBoard($payload + [
                'slug' => $slug,
                'skin_id' => $skin->id,
                'list_count' => 10,
                'permission_write' => 'admin',
                'permission_comment' => 'admin',
            ]);
            $fileService->generateBoardFiles($board);
            $created[] = $label.': 게시판과 필드를 만들었습니다.';
        }
        if ($spec) {
            ia_write_index($board->slug, $spec);
            ia_write_entry_forms($board->slug, $spec, $popups, $formText, ia_ordered_form_fields($storedFields, $spec));
        } else {
            apply_ia_list($board->slug, $fields);
        }
        add_menu((string) ($item['depth1'] ?? $label), $label, '/backoffice/board-posts/'.$board->slug);
    } catch (Throwable $exception) {
        $notes[] = $label.': 게시판을 만들지 못했습니다.';
    }
}

fix_board_menus();
$notes = array_merge($notes, ia_seed_samples());

echo json_encode(['created' => $created, 'notes' => $notes], JSON_UNESCAPED_UNICODE);

function ia_field_config(): array
{
    $config = (new App\Models\BoardTemplate())->getDefaultFieldConfig();
    $config['is_secret']['enabled'] = false;

    return $config;
}

function refresh_board_views(App\Services\Backoffice\BoardFileGeneratorService $fileService, App\Models\Board $board): void
{
    $existed = Illuminate\Support\Facades\Schema::hasTable('board_'.$board->slug);
    $pattern = database_path('migrations/*_create_board_'.$board->slug.'_table.php');
    $before = glob($pattern) ?: [];
    $fileService->generateBoardFiles($board);
    if ($existed) {
        foreach (array_diff(glob($pattern) ?: [], $before) as $file) {
            unlink($file);
        }
    }
}

function menu_key(string $name): string
{
    return preg_replace('/\s+/u', '', $name) ?? $name;
}

function fix_board_menus(): void
{
    $boards = App\Models\Board::query()->get();
    $menus = App\Models\AdminMenu::query()->where('url', 'like', '/backoffice/board-posts/%')->get();
    foreach ($menus as $menu) {
        $slug = basename((string) $menu->url);
        if ($boards->firstWhere('slug', $slug)) {
            continue;
        }
        $key = menu_key((string) $menu->name);
        $board = $boards->first(function ($item) use ($key) {
            $name = menu_key((string) $item->name);

            return $name !== '' && ($name === $key || str_contains($name, $key) || str_contains($key, $name));
        });
        if ($board) {
            $menu->url = '/backoffice/board-posts/'.$board->slug;
            $menu->is_active = true;
            $menu->save();
            continue;
        }
        $menu->is_active = false;
        $menu->save();
    }
}

function apply_ia_list(string $slug, array $fields): void
{
    $path = resource_path('views/backoffice/board-posts/'.$slug.'/index.blade.php');
    if (! is_file($path)) {
        return;
    }

    $listFields = array_values(array_filter(
        $fields,
        static fn (array $field): bool => in_array($field['show'], ['list', 'both'], true)
    ));
    $searchFields = array_values(array_filter(
        $fields,
        static fn (array $field): bool => $field['show'] === 'search'
    ));
    $html = (string) file_get_contents($path);

    if ($searchFields !== []) {
        $filters = '';
        foreach ($searchFields as $field) {
            $label = e($field['label']);
            $name = e($field['name']);
            if (in_array($field['type'], ['select', 'radio', 'checkbox'], true) && $field['options'] !== '') {
                $options = '';
                foreach (explode(',', $field['options']) as $option) {
                    $option = trim($option);
                    if ($option === '') {
                        continue;
                    }
                    $optionText = e($option);
                    $options .= '<option value="'.$optionText.'">'.$optionText.'</option>';
                }
                $filters .= '<div class="filter-group"><label class="filter-label">'.$label.'</label><select name="'.$name.'" class="filter-select"><option value="">전체</option>'.$options.'</select></div>';
            } elseif ($field['type'] === 'date') {
                $filters .= '<div class="filter-group"><label class="filter-label">'.$label.'</label><input type="date" name="'.$name.'" class="filter-input" value="{{ request(\''.$name.'\') }}"></div>';
            } else {
                $filters .= '<div class="filter-group"><label class="filter-label">'.$label.'</label><input type="text" name="'.$name.'" class="filter-input" value="{{ request(\''.$name.'\') }}"></div>';
            }
        }
        $html = preg_replace('/<div class="filter-row">/', '<div class="filter-row">'.$filters, $html, 1) ?? $html;
    }

    if ($listFields !== []) {
        $headers = '';
        foreach ($listFields as $field) {
            $headers .= '<th>'.e($field['label']).'</th>';
        }
        $cells = "@php\n\$iaValues = [];\nif (!empty(\$post->custom_fields)) {\n    \$decoded = json_decode(\$post->custom_fields, true);\n    \$iaValues = is_array(\$decoded) ? \$decoded : [];\n}\n@endphp\n";
        foreach ($listFields as $field) {
            $cells .= '<td>{{ $iaValues[\''.e($field['name']).'\'] ?? \'\' }}</td>';
        }
        $html = str_replace('<th class="w15">관리</th>', $headers.'<th class="w15">관리</th>', $html);
        $html = preg_replace(
            '/<td>\s*<div class="board-btn-group">/',
            $cells.'<td><div class="board-btn-group">',
            $html,
            1
        ) ?? $html;
        $extra = count($listFields);
        $html = str_replace(
            'colspan="{{ $board->enable_sorting ? \'8\' : \'7\' }}"',
            'colspan="{{ ($board->enable_sorting ? 8 : 7) + '.$extra.' }}"',
            $html
        );
    }

    if ($searchFields !== []) {
        $html = preg_replace(
            '/<div class="filter-group">\s*<label for="start_date"[\s\S]*?<label for="keyword"[\s\S]*?<\/div>\s*<\/div>/',
            '',
            $html,
            1
        ) ?? $html;
    }

    file_put_contents($path, $html);
}

function add_menu(string $depth1, string $depth2, string $url): void
{
    $parentName = $depth1 !== '' ? $depth1 : $depth2;
    $childName = $depth2 !== '' ? $depth2 : $parentName;
    $parent = App\Models\AdminMenu::query()->whereNull('parent_id')->orderBy('id')->get()
        ->first(fn ($menu) => menu_key((string) $menu->name) === menu_key($parentName));
    if (! $parent) {
        $parent = App\Models\AdminMenu::query()->create([
            'parent_id' => null,
            'name' => $parentName,
            'url' => null,
            'icon' => 'fa-list',
            'order' => ((int) App\Models\AdminMenu::query()->whereNull('parent_id')->max('order')) + 1,
            'is_active' => true,
        ]);
    }

    $existing = App\Models\AdminMenu::query()->where('url', $url)->first();
    if (! $existing) {
        $existing = App\Models\AdminMenu::query()->where('parent_id', $parent->id)->orderBy('id')->get()
            ->first(fn ($menu) => menu_key((string) $menu->name) === menu_key($childName));
    }
    if ($existing) {
        $existing->parent_id = $parent->id;
        $existing->name = $childName;
        $existing->url = $url;
        $existing->is_active = true;
        $existing->save();

        return;
    }

    App\Models\AdminMenu::query()->create([
        'parent_id' => $parent->id,
        'name' => $childName,
        'url' => $url,
        'icon' => null,
        'order' => ((int) App\Models\AdminMenu::query()->where('parent_id', $parent->id)->max('order')) + 1,
        'is_active' => true,
    ]);
}
