@include('backoffice.approval.forms.partials.document-header')
@php
    $detailMode = (bool) ($isDetail ?? false);
    $editableDetailMode = (bool) ($canEditDraft ?? false);
    $readOnlyMode = $detailMode && ! $editableDetailMode;
    $templateKey = (string) ($template['key'] ?? '');
    $content = $documentContent ?? [];
    $startDate = (string) ($content['start_date'] ?? $content['period_start'] ?? '');
    $endDate = (string) ($content['end_date'] ?? $content['period_end'] ?? '');
    $days = (int) ($content['days'] ?? $content['leave_days'] ?? 0);
    $contact = (string) ($content['contact'] ?? $content['emergency_contact'] ?? '');
    $replacementWorker = (string) ($content['replacement_worker'] ?? $content['substitute_worker'] ?? '');
    $quarterDayMode = $templateKey === 'vacation-quarter-day';
    $periodInputType = $templateKey === 'vacation-quarter-day' ? 'datetime-local' : 'date';
    $startDateParts = explode('T', $startDate);
    $endDateParts = explode('T', $endDate);
    $quarterStartDate = $startDateParts[0] ?? '';
    $quarterEndDate = $endDateParts[0] ?? '';
    $quarterStartHour = substr(($startDateParts[1] ?? '09:00'), 0, 2);
    $quarterEndHour = substr(($endDateParts[1] ?? '18:00'), 0, 2);
    $hourOptions = [
        '09' => '09시',
        '10' => '10시',
        '11' => '11시',
        '12' => '12시',
        '13' => '13시',
        '14' => '14시',
        '15' => '15시',
        '16' => '16시',
        '17' => '17시',
        '18' => '18시',
    ];
    $vacationVariantFields = [
        'vacation-half-day' => [
            ['label' => '반차 구분', 'name' => 'half_day_type', 'type' => 'select', 'options' => ['오전', '오후']],
        ],
        'vacation-quarter-day' => [
            ['label' => '반반차 구분', 'name' => 'quarter_day_type', 'type' => 'select', 'options' => ['오전', '오후']],
        ],
        'vacation-reserve-training' => [
            ['label' => '훈련 종류', 'name' => 'training_type', 'type' => 'text', 'placeholder' => '예비군/민방위'],
        ],
        'vacation-long-sick' => [
            ['label' => '진단명', 'name' => 'diagnosis', 'type' => 'text', 'placeholder' => '진단명을 입력하세요'],
        ],
        'vacation-leave-of-absence' => [
            ['label' => '휴직 구분', 'name' => 'leave_type', 'type' => 'text', 'placeholder' => '휴직 사유 구분'],
        ],
        'vacation-regular' => [
            ['label' => '정기휴가 차수', 'name' => 'regular_round', 'type' => 'text', 'placeholder' => '예: 하계 1차'],
        ],
        'vacation-sick-absence' => [
            ['label' => '병가 여부', 'name' => 'is_sick_leave', 'type' => 'text', 'placeholder' => '병가/결근'],
        ],
        'vacation-health' => [
            ['label' => '보건휴가 구분', 'name' => 'health_leave_type', 'type' => 'text', 'placeholder' => '보건휴가 사유'],
        ],
        'vacation-early-leave' => [
            ['label' => '조퇴 시간', 'name' => 'early_leave_time', 'type' => 'text', 'placeholder' => '예: 15:00'],
        ],
        'vacation-special' => [
            ['label' => '경조 구분', 'name' => 'special_leave_type', 'type' => 'text', 'placeholder' => '경조 종류'],
        ],
        'vacation-training' => [
            ['label' => '교육명', 'name' => 'course_name', 'type' => 'text', 'placeholder' => '교육명을 입력하세요'],
        ],
        'vacation-maternity' => [
            ['label' => '출산 예정일', 'name' => 'expected_birth_date', 'type' => 'date'],
        ],
    ];
    $variantFields = $vacationVariantFields[$templateKey] ?? [];
@endphp

    <div class="approval-doc-body">
        <table class="approval-doc-table">
            <tbody>
                <tr>
                    <th>제목</th>
                    <td><input type="text" class="form-control" name="title" placeholder="제목을 입력하세요" value="{{ $detailMode ? ($documentModel->title ?? '') : '' }}" @readonly($readOnlyMode)></td>
                </tr>
                <input type="hidden" name="content[vacation_type]" value="{{ $content['vacation_type'] ?? '연차' }}">
                <tr>
                    <th>기간</th>
                    <td>
                        @if($quarterDayMode)
                            <div class="approval-inline-fields">
                                <input
                                    type="date"
                                    class="form-control"
                                    name="content[quarter_start_date]"
                                    value="{{ $quarterStartDate }}"
                                    data-quarter-date="start"
                                    @readonly($readOnlyMode)
                                >
                                <select class="form-select" name="content[quarter_start_hour]" data-quarter-hour="start" @disabled($readOnlyMode)>
                                    @foreach($hourOptions as $hourValue => $hourLabel)
                                        <option value="{{ $hourValue }}" @selected($quarterStartHour === $hourValue)>{{ $hourLabel }}</option>
                                    @endforeach
                                </select>
                                <span>~</span>
                                <input
                                    type="date"
                                    class="form-control"
                                    name="content[quarter_end_date]"
                                    value="{{ $quarterEndDate }}"
                                    data-quarter-date="end"
                                    @readonly($readOnlyMode)
                                >
                                <select class="form-select" name="content[quarter_end_hour]" data-quarter-hour="end" @disabled($readOnlyMode)>
                                    @foreach($hourOptions as $hourValue => $hourLabel)
                                        <option value="{{ $hourValue }}" @selected($quarterEndHour === $hourValue)>{{ $hourLabel }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="content[start_date]" value="{{ $startDate }}" data-approval-start-date data-hour-only="true">
                                <input type="hidden" name="content[end_date]" value="{{ $endDate }}" data-approval-end-date data-hour-only="true">
                                <span class="approval-days-text">
                                    일수
                                    <strong data-approval-days-display>{{ $days }}</strong>일
                                </span>
                                <input type="hidden" name="content[days]" value="{{ $days }}" data-approval-days-input>
                            </div>
                        @else
                            <div class="approval-inline-fields">
                                <input
                                    type="{{ $periodInputType }}"
                                    class="form-control"
                                    name="content[start_date]"
                                    value="{{ $startDate }}"
                                    data-approval-start-date
                                    @readonly($readOnlyMode)
                                >
                                <span>~</span>
                                <input
                                    type="{{ $periodInputType }}"
                                    class="form-control"
                                    name="content[end_date]"
                                    value="{{ $endDate }}"
                                    data-approval-end-date
                                    @readonly($readOnlyMode)
                                >
                                <span class="approval-days-text">
                                    일수
                                    <strong data-approval-days-display>{{ $days }}</strong>일
                                </span>
                                <input type="hidden" name="content[days]" value="{{ $days }}" data-approval-days-input>
                            </div>
                        @endif
                    </td>
                </tr>
                @foreach($variantFields as $variantField)
                    <tr>
                        <th>{{ $variantField['label'] }}</th>
                        <td>
                            @if(($variantField['type'] ?? '') === 'select')
                                <select
                                    class="form-select"
                                    name="content[{{ $variantField['name'] }}]"
                                    @disabled($readOnlyMode)
                                >
                                    <option value="">선택</option>
                                    @foreach(($variantField['options'] ?? []) as $option)
                                        <option value="{{ $option }}" @selected(($content[$variantField['name']] ?? '') === $option)>{{ $option }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input
                                    type="{{ $variantField['type'] }}"
                                    class="form-control"
                                    name="content[{{ $variantField['name'] }}]"
                                    value="{{ $content[$variantField['name']] ?? '' }}"
                                    placeholder="{{ $variantField['placeholder'] ?? '' }}"
                                    @readonly($readOnlyMode)
                                >
                            @endif
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <th>사유</th>
                    <td>
                        <textarea
                            class="form-control"
                            id="approval_vacation_reason"
                            name="content[reason]"
                            rows="4"
                            placeholder="휴가 사유를 입력하세요"
                            @readonly($readOnlyMode)
                        >{{ $content['reason'] ?? '' }}</textarea>
                    </td>
                </tr>
                <tr>
                    <th>행선지</th>
                    <td><input type="text" class="form-control" name="content[destination]" placeholder="행선지를 입력하세요" value="{{ $content['destination'] ?? '' }}" @readonly($readOnlyMode)></td>
                </tr>
                <tr>
                    <th>연락처</th>
                    <td><input type="text" class="form-control" name="content[contact]" placeholder="연락 가능한 번호를 입력하세요" value="{{ $contact }}" @readonly($readOnlyMode)></td>
                </tr>
                <tr>
                    <th>대체 근무자</th>
                    <td>
                        <div class="approval-inline-fields">
                            <input type="text" class="form-control" name="content[replacement_worker]" placeholder="대체 근무자를 입력하세요" value="{{ $replacementWorker }}" @readonly($readOnlyMode)>
                            @unless($readOnlyMode)
                                <button type="button" class="btn btn-outline-secondary btn-sm">대체 근무자 선택</button>
                            @endunless
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>첨부파일</th>
                    <td>
                        @if($readOnlyMode)
                            @if(($documentModel->attachments ?? collect())->count() > 0)
                                <div class="approval-attachment-list">
                                    @foreach($documentModel->attachments as $attachment)
                                        <a
                                            href="{{ asset('storage/'.$attachment->stored_path) }}"
                                            class="approval-attachment-item"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <i class="fas fa-paperclip"></i>
                                            <span>{{ $attachment->original_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted">첨부파일 없음</span>
                            @endif
                        @else
                            @if(($documentModel->attachments ?? collect())->count() > 0)
                                <div class="approval-attachment-list mb-2">
                                    @foreach($documentModel->attachments as $attachment)
                                        <a
                                            href="{{ asset('storage/'.$attachment->stored_path) }}"
                                            class="approval-attachment-item"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <i class="fas fa-paperclip"></i>
                                            <span>{{ $attachment->original_name }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            <div class="board-file-upload approval-file-upload">
                                <div class="board-file-input-wrapper">
                                    <input
                                        type="file"
                                        class="board-file-input"
                                        id="approval_attachments"
                                        name="attachments[]"
                                        multiple
                                        data-approval-file-input
                                        data-preview-target="approvalAttachmentPreview"
                                    >
                                    <div class="board-file-input-content">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span class="board-file-input-text">첨부파일을 선택하거나 드래그하세요</span>
                                        <span class="board-file-input-subtext">여러 파일 첨부 가능</span>
                                    </div>
                                </div>
                                <div class="board-file-preview" id="approvalAttachmentPreview"></div>
                            </div>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="approval-doc-footer-note">
        상기와 같은 사유로 {{ $template['name'] }}를 제출하오니 재가바랍니다.
    </div>
</div>

