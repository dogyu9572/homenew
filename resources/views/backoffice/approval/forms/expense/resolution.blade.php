@include('backoffice.approval.forms.partials.document-header')
@php
    $disableNoteEditor = ($template['key'] ?? '') === 'expense-congrats-support';
    $templateKey = (string) ($template['key'] ?? '');
    $detailMode = (bool) ($isDetail ?? false);
    $editableDetailMode = (bool) ($canEditDraft ?? false);
    $readOnlyMode = $detailMode && ! $editableDetailMode;
    $content = $documentContent ?? [];
    $expenseLinesRaw = $content['expense_lines'] ?? [];
    $expenseLines = \App\Support\ApprovalExpenseLines::normalize(is_array($expenseLinesRaw) ? $expenseLinesRaw : []);
    $expenseVariantFields = [
        'expense-congrats-support' => [
            ['label' => '경조 구분', 'name' => 'congrats_type', 'type' => 'text', 'placeholder' => '경조 구분'],
            ['label' => '대상자', 'name' => 'target_name', 'type' => 'text', 'placeholder' => '대상자 이름'],
        ],
        'expense-transport' => [
            ['label' => '교통수단', 'name' => 'transport_type', 'type' => 'text', 'placeholder' => '예: KTX, 택시'],
            ['label' => '이동 구간', 'name' => 'route', 'type' => 'text', 'placeholder' => '출발지 - 도착지'],
        ],
        'expense-purchase' => [
            ['label' => '구매 품목', 'name' => 'purchase_item', 'type' => 'text', 'placeholder' => '품목명'],
            ['label' => '구매 수량', 'name' => 'purchase_quantity', 'type' => 'number', 'placeholder' => '0'],
        ],
        'expense-outsourcing-deposit' => [
            ['label' => '외주 업체명', 'name' => 'vendor_company', 'type' => 'text', 'placeholder' => '외주 업체명'],
            ['label' => '입금 예정일', 'name' => 'deposit_due_date', 'type' => 'date'],
        ],
    ];
    $variantFields = $expenseVariantFields[$templateKey] ?? [];
@endphp

<div class="approval-doc-body">
    <table class="approval-doc-table">
        <tbody>
            <tr>
                <th>제목</th>
                <td><input type="text" class="form-control" name="title" placeholder="지출결의 제목을 입력하세요" value="{{ $detailMode ? ($documentModel->title ?? '') : '' }}" @readonly($readOnlyMode)></td>
            </tr>
            <tr>
                <th>청구금액</th>
                <td><input type="number" class="form-control" name="content[claim_amount]" placeholder="0" value="{{ $content['claim_amount'] ?? '' }}" @readonly($readOnlyMode)></td>
            </tr>
            <tr>
                <th>정산금액</th>
                <td><input type="number" class="form-control" name="content[settlement_amount]" placeholder="0" value="{{ $content['settlement_amount'] ?? '' }}" @readonly($readOnlyMode)></td>
            </tr>
            <tr>
                <th>수령인</th>
                <td><input type="text" class="form-control" name="content[recipient]" placeholder="수령인을 입력하세요" value="{{ $content['recipient'] ?? '' }}" @readonly($readOnlyMode)></td>
            </tr>
            @foreach($variantFields as $variantField)
                <tr>
                    <th>{{ $variantField['label'] }}</th>
                    <td>
                        <input
                            type="{{ $variantField['type'] }}"
                            class="form-control"
                            name="content[{{ $variantField['name'] }}]"
                            value="{{ $content[$variantField['name']] ?? '' }}"
                            placeholder="{{ $variantField['placeholder'] ?? '' }}"
                            @readonly($readOnlyMode)
                        >
                    </td>
                </tr>
            @endforeach
            <tr>
                <th>지출내역</th>
                <td class="approval-subtable-cell">
                    <table class="approval-subtable approval-expense-subtable">
                        <thead>
                            <tr>
                                <th>날짜</th>
                                <th>지출내역</th>
                                <th>금액</th>
                                <th>비고</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 0; $i < \App\Support\ApprovalExpenseLines::LINE_COUNT; $i++)
                                <tr>
                                    <td><input type="date" class="form-control" name="content[expense_lines][{{ $i }}][date]" value="{{ $expenseLines[$i]['date'] ?? '' }}" @readonly($readOnlyMode)></td>
                                    <td><input type="text" class="form-control" name="content[expense_lines][{{ $i }}][detail]" value="{{ $expenseLines[$i]['detail'] ?? '' }}" @readonly($readOnlyMode)></td>
                                    <td><input type="number" class="form-control" name="content[expense_lines][{{ $i }}][amount]" value="{{ $expenseLines[$i]['amount'] ?? '' }}" @readonly($readOnlyMode)></td>
                                    <td><input type="text" class="form-control" name="content[expense_lines][{{ $i }}][note]" value="{{ $expenseLines[$i]['note'] ?? '' }}" @readonly($readOnlyMode)></td>
                                </tr>
                            @endfor
                            <tr>
                                <th>합계</th>
                                <td colspan="3"><input type="number" class="form-control" name="content[expense_total]" value="{{ $content['expense_total'] ?? '' }}" @readonly($readOnlyMode)></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <th>비고</th>
                <td>
                    @if($disableNoteEditor)
                        <textarea
                            class="form-control"
                            id="approval_expense_note"
                            name="content[note]"
                            rows="4"
                            placeholder="추가 메모를 입력하세요"
                            @readonly($readOnlyMode)
                        >{{ $content['note'] ?? '' }}</textarea>
                    @else
                        @if($readOnlyMode)
                            <div class="approval-richtext-view">{!! $content['note'] ?? '' !!}</div>
                        @else
                            <textarea
                                class="form-control"
                                id="approval_expense_note"
                                name="content[note]"
                                rows="4"
                                placeholder="추가 메모를 입력하세요"
                                data-backoffice-ckeditor
                                data-source-editing="true"
                            >{{ $content['note'] ?? '' }}</textarea>
                        @endif
                    @endif
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
                                    id="approval_expense_attachments"
                                    name="attachments[]"
                                    multiple
                                    data-approval-file-input
                                    data-preview-target="approvalExpenseAttachmentPreview"
                                >
                                <div class="board-file-input-content">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="board-file-input-text">첨부파일을 선택하거나 드래그하세요</span>
                                    <span class="board-file-input-subtext">여러 파일 첨부 가능</span>
                                </div>
                            </div>
                            <div class="board-file-preview" id="approvalExpenseAttachmentPreview"></div>
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

