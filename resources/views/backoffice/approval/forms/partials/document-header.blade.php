@php
    $templateKey = (string) ($template['key'] ?? '');
    $expenseDocClass = str_starts_with($templateKey, 'expense-') ? ' approval-doc-sheet--expense' : '';
@endphp
<div class="approval-doc-sheet{{ $expenseDocClass }}">
    @php
        $detailMode = (bool) ($isDetail ?? false);
        $doc = $documentModel ?? null;
        $content = $documentContent ?? [];
        $approvalSlots = ['', '', '', '', ''];
        $approvalDates = ['', '', '', '', ''];
        $approvalSlotClasses = array_fill(0, 5, '');
        $cooperationSlot = '';
        $cooperationDate = '';
        $cooperationSlotClass = '';
        if ($detailMode && $doc) {
            // 1번 결재칸은 기안자(상신자) 고정
            $approvalSlots[0] = $doc->writer?->name ?? '';
            $approvalDates[0] = optional($doc->submitted_at ?? $doc->created_at)->format('m/d 기안') ?? '';
            $approvalSlotClasses[0] = 'is-requester is-approved';

            $approvalLines = $doc->lines->where('line_type', 'approval')->sortBy('line_order')->values();
            foreach ($approvalLines as $idx => $line) {
                // 2~5번 칸에 결재선 표시 (최대 4명)
                if ($idx > 3) {
                    break;
                }
                $slotIndex = $idx + 1;
                $approvalSlots[$slotIndex] = $line->user?->name ?? '';
                $actionType = (string) (($line->meta['action_type'] ?? '') ?: '');
                $actionLabel = match (true) {
                    $line->status === \App\Models\ApprovalLine::STATUS_APPROVED && $actionType === 'delegate' => '전결',
                    $line->status === \App\Models\ApprovalLine::STATUS_APPROVED => '결재',
                    $line->status === \App\Models\ApprovalLine::STATUS_REJECTED && $actionType === 'hold' => '보류',
                    $line->status === \App\Models\ApprovalLine::STATUS_REJECTED => '기각',
                    default => '',
                };
                $approvalDates[$slotIndex] = $actionLabel !== '' ? (optional($line->acted_at)->format('m/d').' '.$actionLabel) : '';
                if ($line->status === \App\Models\ApprovalLine::STATUS_APPROVED) {
                    $approvalSlotClasses[$slotIndex] = 'is-approved';
                } elseif ($line->status === \App\Models\ApprovalLine::STATUS_REJECTED) {
                    $approvalSlotClasses[$slotIndex] = 'is-rejected';
                }
            }
            $coLine = $doc->lines->where('line_type', 'cooperation')->sortBy('line_order')->first();
            $cooperationSlot = $coLine?->user?->name ?? '';
            $coActionType = (string) (($coLine?->meta['action_type'] ?? '') ?: '');
            $coActionLabel = match (true) {
                $coLine?->status === \App\Models\ApprovalLine::STATUS_CONFIRMED && $coActionType === 'delegate' => '전결',
                $coLine?->status === \App\Models\ApprovalLine::STATUS_CONFIRMED => '결재',
                $coLine?->status === \App\Models\ApprovalLine::STATUS_REJECTED && $coActionType === 'hold' => '보류',
                $coLine?->status === \App\Models\ApprovalLine::STATUS_REJECTED => '기각',
                default => '',
            };
            $cooperationDate = $coActionLabel !== '' ? (optional($coLine?->acted_at)->format('m/d').' '.$coActionLabel) : '';
            if ($coLine?->status === \App\Models\ApprovalLine::STATUS_CONFIRMED) {
                $cooperationSlotClass = 'is-approved';
            } elseif ($coLine?->status === \App\Models\ApprovalLine::STATUS_REJECTED) {
                $cooperationSlotClass = 'is-rejected';
            }
        } else {
            $approvalSlots[0] = auth()->user()->name ?? '';
            $approvalSlotClasses[0] = 'is-requester';
        }
    @endphp
    <div class="approval-doc-title">{{ $template['name'] }}</div>

    <div class="approval-doc-top-grid">
        <div class="approval-doc-meta">
            <table class="approval-doc-table">
                <tbody>
                    <tr>
                        <th>문서번호</th>
                        <td>{{ $detailMode ? ($doc?->doc_no ?? '') : '2604-XXXX' }}</td>
                    </tr>
                    <tr>
                        <th>문서종류</th>
                        <td>{{ $template['name'] }}</td>
                    </tr>
                    <tr>
                        <th>작성부서</th>
                        <td>{{ $detailMode ? ($doc?->writer?->department ?? '') : (auth()->user()->department ?? '미지정') }}</td>
                    </tr>
                    <tr>
                        <th>기안일</th>
                        <td>{{ $detailMode ? optional($doc?->submitted_at ?? $doc?->created_at)->format('Y년 m월 d일') : now()->format('Y년 m월 d일') }}</td>
                    </tr>
                    <tr>
                        <th>기안자</th>
                        <td>{{ $detailMode ? ($doc?->writer?->name ?? '') : (auth()->user()->name ?? '관리자') }}</td>
                    </tr>
                    <tr>
                        <th>공개여부</th>
                        <td class="approval-doc-inline-cell">
                            <select class="form-select form-select-sm" @disabled($detailMode)>
                                <option>공개</option>
                                <option>비공개</option>
                            </select>
                            <span>보존기간</span>
                            <select class="form-select form-select-sm" @disabled($detailMode)>
                                <option>5년</option>
                                <option>10년</option>
                            </select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="approval-doc-line">
            <table class="approval-doc-table approval-signoff-table">
                <tbody>
                    <tr>
                        <th rowspan="2">결재</th>
                        <td class="approval-signoff-slot {{ $approvalSlotClasses[0] }}" data-sign-slot="approval-1">
                            <div class="approval-signoff-role"></div>
                            <div class="approval-signoff-name">{{ $approvalSlots[0] }}</div>
                            <div class="approval-signoff-date">{{ $approvalDates[0] }}</div>
                        </td>
                        <td class="approval-signoff-slot {{ $approvalSlotClasses[1] }}" data-sign-slot="approval-2">
                            <div class="approval-signoff-role"></div>
                            <div class="approval-signoff-name">{{ $approvalSlots[1] }}</div>
                            <div class="approval-signoff-date">{{ $approvalDates[1] }}</div>
                        </td>
                        <td class="approval-signoff-slot {{ $approvalSlotClasses[2] }}" data-sign-slot="approval-3">
                            <div class="approval-signoff-role"></div>
                            <div class="approval-signoff-name">{{ $approvalSlots[2] }}</div>
                            <div class="approval-signoff-date">{{ $approvalDates[2] }}</div>
                        </td>
                        <td class="approval-signoff-slot {{ $approvalSlotClasses[3] }}" data-sign-slot="approval-4">
                            <div class="approval-signoff-role"></div>
                            <div class="approval-signoff-name">{{ $approvalSlots[3] }}</div>
                            <div class="approval-signoff-date">{{ $approvalDates[3] }}</div>
                        </td>
                        <td class="approval-signoff-slot {{ $approvalSlotClasses[4] }}" data-sign-slot="approval-5">
                            <div class="approval-signoff-role"></div>
                            <div class="approval-signoff-name">{{ $approvalSlots[4] }}</div>
                            <div class="approval-signoff-date">{{ $approvalDates[4] }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5" class="approval-line-cell">
                            <div class="approval-line-selected approval-line-selected-hidden" data-line-target="approval"></div>
                            @unless($detailMode)
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-approver-open data-line-type="approval">지정</button>
                                <button type="button" class="btn btn-outline-dark btn-sm" data-approver-clear data-line-type="approval">초기화</button>
                            @endunless
                        </td>
                    </tr>
                    <tr>
                        <th rowspan="2">협조</th>
                        <td class="approval-signoff-slot {{ $cooperationSlotClass }}" data-sign-slot="cooperation-1">
                            <div class="approval-signoff-role"></div>
                            <div class="approval-signoff-name">{{ $cooperationSlot }}</div>
                            <div class="approval-signoff-date">{{ $cooperationDate }}</div>
                        </td>
                        <td class="approval-signoff-empty"></td>
                        <td class="approval-signoff-empty"></td>
                        <td class="approval-signoff-empty"></td>
                        <td class="approval-signoff-empty"></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="approval-line-cell">
                            <div class="approval-line-selected approval-line-selected-hidden" data-line-target="cooperation"></div>
                            @unless($detailMode)
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-approver-open data-line-type="cooperation">지정</button>
                                <button type="button" class="btn btn-outline-dark btn-sm" data-approver-clear data-line-type="cooperation">초기화</button>
                            @endunless
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

