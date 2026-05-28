@include('backoffice.approval.forms.partials.document-header')
@php
    $detailMode = (bool) ($isDetail ?? false);
    $editableDetailMode = (bool) ($canEditDraft ?? false);
    $readOnlyMode = $detailMode && ! $editableDetailMode;
    $content = $documentContent ?? [];
    $educationRows = $content['education_rows'] ?? [];
    $legacyEducationName = $content['education_name'] ?? '';
    $legacyEducationPeriod = $content['education_period'] ?? '';
    $legacyEducationProvider = $content['education_provider'] ?? '';
@endphp

<div class="approval-doc-body">
    <table class="approval-doc-table">
        <tbody>
            <tr>
                <th>제목</th>
                <td><input type="text" class="form-control" name="title" placeholder="교육품의 제목을 입력하세요" value="{{ $detailMode ? ($documentModel->title ?? '') : '' }}" @readonly($readOnlyMode)></td>
            </tr>
            <tr>
                <th>교육 항목</th>
                <td class="approval-subtable-cell">
                    <table class="approval-subtable approval-expense-subtable">
                        <thead>
                            <tr>
                                <th>교육명</th>
                                <th>일정</th>
                                <th>교육비</th>
                                <th>참여 인원수</th>
                                <th>비고</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 0; $i < 10; $i++)
                                @php
                                    $row = $educationRows[$i] ?? [];
                                @endphp
                                <tr>
                                    <td><input type="text" class="form-control" name="content[education_rows][{{ $i }}][education_name]" value="{{ $row['education_name'] ?? ($i === 0 ? $legacyEducationName : '') }}" @readonly($readOnlyMode)></td>
                                    <td><input type="text" class="form-control" name="content[education_rows][{{ $i }}][schedule]" value="{{ $row['schedule'] ?? ($i === 0 ? $legacyEducationPeriod : '') }}" @readonly($readOnlyMode)></td>
                                    <td><input type="number" class="form-control" name="content[education_rows][{{ $i }}][fee_krw]" value="{{ $row['fee_krw'] ?? '' }}" @readonly($readOnlyMode)></td>
                                    <td><input type="number" class="form-control" name="content[education_rows][{{ $i }}][participants]" value="{{ $row['participants'] ?? '' }}" @readonly($readOnlyMode)></td>
                                    <td><input type="text" class="form-control" name="content[education_rows][{{ $i }}][note]" value="{{ $row['note'] ?? ($i === 0 ? $legacyEducationProvider : '') }}" @readonly($readOnlyMode)></td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </td>
            </tr>
            <tr>
                <th>교육 내용</th>
                <td>
                    <textarea
                        class="form-control"
                        name="content[education_detail]"
                        rows="6"
                        placeholder="교육 목적, 기대 효과, 상세 내용을 입력하세요"
                        @readonly($readOnlyMode)
                    >{{ $content['education_detail'] ?? ($content['detail'] ?? '') }}</textarea>
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
                                    id="approval_education_attachments"
                                    name="attachments[]"
                                    multiple
                                    data-approval-file-input
                                    data-preview-target="approvalEducationAttachmentPreview"
                                >
                                <div class="board-file-input-content">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span class="board-file-input-text">첨부파일을 선택하거나 드래그하세요</span>
                                    <span class="board-file-input-subtext">여러 파일 첨부 가능</span>
                                </div>
                            </div>
                            <div class="board-file-preview" id="approvalEducationAttachmentPreview"></div>
                        </div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="approval-doc-footer-note">
    상기와 같은 내용으로 {{ $template['name'] }}를 상신하오니 검토 부탁드립니다.
</div>
</div>
