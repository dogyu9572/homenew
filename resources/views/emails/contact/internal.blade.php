@php
    /** @var \App\Models\Contact $contact */
    $servicesLine = is_array($contact->services) ? implode(', ', $contact->services) : '';
    $submittedAt = $contact->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s') ?? '';
@endphp
<table cellpadding="0" cellspacing="0" border="0" style="font-family:'Malgun Gothic',sans-serif; font-size:14px; color:#333; max-width:640px;">
	<tr><td style="padding:8px 0;"><strong>회사명</strong></td><td style="padding:8px 0;">{{ $contact->company }}</td></tr>
	<tr><td style="padding:8px 0; vertical-align:top;"><strong>담당자 성함/직책</strong></td><td style="padding:8px 0;">{{ $contact->contact_person }}</td></tr>
	<tr><td style="padding:8px 0;"><strong>연락처</strong></td><td style="padding:8px 0;">{{ $contact->phone }}</td></tr>
	<tr><td style="padding:8px 0;"><strong>이메일</strong></td><td style="padding:8px 0;">{{ $contact->email }}</td></tr>
	<tr><td style="padding:8px 0; vertical-align:top;"><strong>관심서비스</strong></td><td style="padding:8px 0;">{{ $servicesLine !== '' ? $servicesLine : '-' }}</td></tr>
	<tr><td style="padding:8px 0; vertical-align:top;"><strong>현재 사이트 주소</strong></td><td style="padding:8px 0;">{{ filled($contact->current_site) ? $contact->current_site : '-' }}</td></tr>
	<tr>
		<td style="padding:8px 0; vertical-align:top;"><strong>문의 내용</strong></td>
		<td style="padding:8px 0;">
			@if (filled($contact->message))
				{!! nl2br(e($contact->message)) !!}
			@else
				-
			@endif
		</td>
	</tr>
	<tr><td style="padding:8px 0; vertical-align:top;"><strong>첨부파일</strong></td><td style="padding:8px 0;">
		@if (! empty($contact->attachments))
			@foreach ($contact->attachments as $att)
				<div>{{ $att['original_name'] ?? ($att['path'] ?? '') }}</div>
			@endforeach
		@else
			-
		@endif
	</td></tr>
	<tr><td style="padding:8px 0;"><strong>프로젝트 예산</strong></td><td style="padding:8px 0;">{{ filled($contact->budget) ? $contact->budget : '-' }}</td></tr>
	<tr><td style="padding:8px 0;"><strong>문의일시</strong></td><td style="padding:8px 0;">{{ $submittedAt }}</td></tr>
	<tr><td style="padding:8px 0; vertical-align:top;"><strong>유입경로</strong></td><td style="padding:8px 0; white-space:pre-wrap;">{{ $contact->inflowSummaryLine() }}</td></tr>
</table>
