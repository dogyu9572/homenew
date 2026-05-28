<div class="bo-form-section">
    <h3 class="bo-section-title">도메인 및 개발정보</h3>
    <div class="bo-form-list">
        <div class="bo-form-row">
            <label class="bo-form-label">프로젝트명 <span class="required">*</span></label>
            <div class="bo-form-field">
                <input type="text" class="board-form-control @error('ProjectName') is-invalid @enderror" name="ProjectName" value="{{ old('ProjectName', $project->ProjectName ?? '') }}" required>
                @error('ProjectName')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">도메인 주소</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DomainUrl" value="{{ old('DomainUrl', $project->DomainUrl ?? '') }}"></div>
            <label class="bo-form-label">관리자 주소</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DomainAdminUrl" value="{{ old('DomainAdminUrl', $project->DomainAdminUrl ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">서브도메인</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DomainSubUrl" value="{{ old('DomainSubUrl', $project->DomainSubUrl ?? '') }}"></div>
            <label class="bo-form-label">테스트 주소</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="TestUrl" value="{{ old('TestUrl', $project->TestUrl ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">도메인 기관</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DomainCompany" value="{{ old('DomainCompany', $project->DomainCompany ?? '') }}"></div>
            <label class="bo-form-label">서버 기관</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="ServerCompany" value="{{ old('ServerCompany', $project->ServerCompany ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">개발언어</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DevelopLanguage" value="{{ old('DevelopLanguage', $project->DevelopLanguage ?? '') }}"></div>
            <label class="bo-form-label">DB 종류</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DbType" value="{{ old('DbType', $project->DbType ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">FTP 주소</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="FtpUrl" value="{{ old('FtpUrl', $project->FtpUrl ?? '') }}"></div>
            <label class="bo-form-label">FTP 포트</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="FtpPort" value="{{ old('FtpPort', $project->FtpPort ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">FTP 아이디</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="FtpId" value="{{ old('FtpId', $project->FtpId ?? '') }}"></div>
            <label class="bo-form-label">FTP 비밀번호</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="FtpPasswd" value="{{ old('FtpPasswd', $project->FtpPasswd ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">DB 호스트</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DbHost" value="{{ old('DbHost', $project->DbHost ?? '') }}"></div>
            <label class="bo-form-label">DB 명</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DbName" value="{{ old('DbName', $project->DbName ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">DB 아이디</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DbId" value="{{ old('DbId', $project->DbId ?? '') }}"></div>
            <label class="bo-form-label">DB 비밀번호</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="DbPasswd" value="{{ old('DbPasswd', $project->DbPasswd ?? '') }}"></div>
        </div>

        <div class="bo-form-row">
            <label class="bo-form-label">기타</label>
            <div class="bo-form-field"><textarea class="board-form-control" name="Domain_Etc" rows="30">{{ old('Domain_Etc', $project->Domain_Etc ?? '') }}</textarea></div>
        </div>
    </div>
</div>

<div class="bo-form-section">
    <h3 class="bo-section-title">첨부파일</h3>
    <div class="bo-form-list">
        <div class="bo-form-row">
            <label class="bo-form-label">첨부파일</label>
            <div class="bo-form-field">
                <div class="board-file-upload">
                    <div class="board-file-input-wrapper">
                        <input type="file" class="board-file-input" id="attachments" name="attachments[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar">
                        <div class="board-file-input-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span class="board-file-input-text">파일을 선택하거나 여기로 드래그하세요</span>
                            <span class="board-file-input-subtext">최대 5개, 각 파일 10MB 이하</span>
                        </div>
                    </div>

                    @if(!empty($attachmentItems) && count($attachmentItems) > 0)
                        <div class="board-existing-files">
                            <div class="board-attachment-list">
                                @foreach($attachmentItems as $item)
                                    <div class="board-attachment-item existing-file">
                                        <i class="fas fa-file"></i>
                                        @if(!empty($item->stored_path))
                                            <a class="board-attachment-link board-attachment-name" href="{{ route('backoffice.project-manages.attachments.download', [$project->idx, $item->row_idx]) }}">{{ $item->display_name }}</a>
                                        @else
                                            <span class="board-attachment-name">{{ $item->display_name }}</span>
                                        @endif
                                        @if(!empty($item->size_bytes))
                                            <span class="board-attachment-size">({{ number_format($item->size_bytes / 1024 / 1024, 2) }}MB)</span>
                                        @endif
                                        <button type="button" class="board-attachment-remove btn-remove-existing-attachment">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <input type="hidden" name="existing_attachment_tokens[]" value="{{ $item->token }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="board-file-preview" id="filePreview"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="bo-form-section">
    <h3 class="bo-section-title">업체 담당자 정보</h3>
    <div class="bo-form-list">
        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">업체 담당자명</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="CompanyPerson" value="{{ old('CompanyPerson', $project->CompanyPerson ?? '') }}"></div>
            <label class="bo-form-label">이메일</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="CpEmail" value="{{ old('CpEmail', $project->CpEmail ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">핸드폰</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="CpPhone" value="{{ old('CpPhone', $project->CpPhone ?? '') }}"></div>
            <label class="bo-form-label">전화</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="CpTel" value="{{ old('CpTel', $project->CpTel ?? '') }}"></div>
        </div>

        <div class="bo-form-row">
            <label class="bo-form-label">담당자 추가</label>
            <div class="bo-form-field"><textarea class="board-form-control" name="CompanyAddPerson" rows="3">{{ old('CompanyAddPerson', $project->CompanyAddPerson ?? '') }}</textarea></div>
        </div>

        <div class="bo-form-row">
            <label class="bo-form-label">협력업체 정보</label>
            <div class="bo-form-field"><textarea class="board-form-control" name="CompanyCollaborator" rows="3">{{ old('CompanyCollaborator', $project->CompanyCollaborator ?? '') }}</textarea></div>
        </div>
    </div>
</div>

<div class="bo-form-section">
    <h3 class="bo-section-title">상세내용</h3>
    <div class="bo-form-list">
        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">프로젝트 구분</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="ProjectGubun" value="{{ old('ProjectGubun', $project->ProjectGubun ?? '') }}"></div>
            <label class="bo-form-label">진행상태</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="ProjectIngState" value="{{ old('ProjectIngState', $project->ProjectIngState ?? '') }}"></div>
        </div>

        <div class="bo-form-row">
            <label class="bo-form-label">벤치마킹 사이트</label>
            <div class="bo-form-field"><textarea class="board-form-control" name="ReferSiteUrl" rows="3">{{ old('ReferSiteUrl', $project->ReferSiteUrl ?? '') }}</textarea></div>
        </div>

        <div class="bo-form-row">
            <label class="bo-form-label">기타 참고 사항</label>
            <div class="bo-form-field"><textarea class="board-form-control" name="ReferEtc" rows="3">{{ old('ReferEtc', $project->ReferEtc ?? '') }}</textarea></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">영업</label>
            <div class="bo-form-field">
                <input type="text" class="board-form-control" name="IntraBusiness" value="{{ old('IntraBusiness', $project->IntraBusiness ?? '') }}">
            </div>
            <label class="bo-form-label">기획</label>
            <div class="bo-form-field">
                <input type="text" class="board-form-control" name="IntraManager" value="{{ old('IntraManager', $project->IntraManager ?? '') }}">
            </div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">디자인</label>
            <div class="bo-form-field">
                <input type="text" class="board-form-control" name="IntraDesiner" value="{{ old('IntraDesiner', $project->IntraDesiner ?? '') }}">
            </div>
            <label class="bo-form-label">퍼블</label>
            <div class="bo-form-field">
                <input type="text" class="board-form-control" name="IntraPublisher" value="{{ old('IntraPublisher', $project->IntraPublisher ?? '') }}">
            </div>
        </div>

        <div class="bo-form-row">
            <label class="bo-form-label">개발</label>
            <div class="bo-form-field">
                <input type="text" class="board-form-control" name="IntraProgramer" value="{{ old('IntraProgramer', $project->IntraProgramer ?? '') }}">
            </div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">프로젝트 기간</label>
            <div class="bo-form-field">
                <input type="text" class="board-form-control bo-gap-bottom" name="ProjectSdate" value="{{ old('ProjectSdate', $project->ProjectSdate ?? '') }}" placeholder="시작">
                <input type="text" class="board-form-control" name="ProjectEdate" value="{{ old('ProjectEdate', $project->ProjectEdate ?? '') }}" placeholder="종료">
            </div>
            <label class="bo-form-label">호스팅 기간</label>
            <div class="bo-form-field">
                <input type="text" class="board-form-control bo-gap-bottom" name="HostingSdate" value="{{ old('HostingSdate', $project->HostingSdate ?? '') }}" placeholder="시작">
                <input type="text" class="board-form-control" name="HostingEdate" value="{{ old('HostingEdate', $project->HostingEdate ?? '') }}" placeholder="종료">
            </div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">작업내용</label>
            <div class="bo-form-field"><textarea class="board-form-control" name="ProjectMemo" rows="4">{{ old('ProjectMemo', $project->ProjectMemo ?? '') }}</textarea></div>
            <label class="bo-form-label">특이사항</label>
            <div class="bo-form-field"><textarea class="board-form-control" name="SpeicalMemo" rows="4">{{ old('SpeicalMemo', $project->SpeicalMemo ?? '') }}</textarea></div>
        </div>
    </div>
</div>

<div class="bo-form-section">
    <h3 class="bo-section-title">업체 상세정보</h3>
    <div class="bo-form-list">
        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">업체명</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="CompanyName" value="{{ old('CompanyName', $project->CompanyName ?? '') }}"></div>
            <label class="bo-form-label">사업자번호</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="LicensessNumber" value="{{ old('LicensessNumber', $project->LicensessNumber ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">사업자명</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="LicensessName" value="{{ old('LicensessName', $project->LicensessName ?? '') }}"></div>
            <label class="bo-form-label">우편번호</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="LicensessPost" value="{{ old('LicensessPost', $project->LicensessPost ?? '') }}"></div>
        </div>

        <div class="bo-form-row bo-form-row-2">
            <label class="bo-form-label">전화번호</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="LicensessTel" value="{{ old('LicensessTel', $project->LicensessTel ?? '') }}"></div>
            <label class="bo-form-label">팩스</label>
            <div class="bo-form-field"><input type="text" class="board-form-control" name="LicensessFax" value="{{ old('LicensessFax', $project->LicensessFax ?? '') }}"></div>
        </div>

        <div class="bo-form-row">
            <label class="bo-form-label">주소</label>
            <div class="bo-form-field"><textarea class="board-form-control" name="LicensessAddr" rows="3">{{ old('LicensessAddr', $project->LicensessAddr ?? '') }}</textarea></div>
        </div>
    </div>
</div>

