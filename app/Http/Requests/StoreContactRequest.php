<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
{
    private const CONTACT_FORM_MIN_SECONDS = 2;

    private const CONTACT_FORM_MAX_SECONDS = 7200;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'agree-terms' => ['accepted'],
            'service' => ['required', 'array', 'min:1'],
            'service.*' => ['string', Rule::in(Contact::SERVICE_OPTIONS)],
            'current_site' => ['nullable', 'string', 'max:2048'],
            'message' => ['nullable', 'string', 'max:10000'],
            'budget' => ['nullable', 'string', 'max:500'],
            'source_type' => ['nullable', 'string', 'max:50'],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'source_url' => ['nullable', 'string', 'max:2048'],
            'source_title' => ['nullable', 'string', 'max:500'],
            'contact_token' => ['nullable', 'string', 'max:100'],
            'contact_ts' => ['nullable', 'integer', 'min:1'],
            'homepage' => ['nullable', 'string', 'max:255'],
            'attachments' => ['nullable', 'array', 'max:3'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,gif,webp,doc,docx,xls,xlsx,ppt,pptx,zip'],
        ];
    }

    public function attributes(): array
    {
        return [
            'company' => '회사명',
            'contact_person' => '담당자성함/직책',
            'phone' => '연락처',
            'email' => '이메일',
            'agree-terms' => '개인정보 이용 및 수집 동의',
            'service' => '관심 서비스',
            'current_site' => '현재 사이트',
            'message' => '문의 내용',
            'budget' => '프로젝트 예산',
            'source_type' => '유입 유형',
            'source_id' => '유입 식별자',
            'source_url' => '유입 URL',
            'source_title' => '유입 화면 제목',
            'contact_token' => '폼 토큰',
            'contact_ts' => '폼 시간',
            'homepage' => '숨김 필드',
            'attachments' => '첨부파일',
        ];
    }

    public function messages(): array
    {
        return [
            'company.required' => '회사명을 입력해 주세요.',
            'company.max' => '회사명은 255자 이내로 입력해 주세요.',
            'contact_person.required' => '담당자성함/직책을 입력해 주세요.',
            'contact_person.max' => '담당자성함/직책은 100자 이내로 입력해 주세요.',
            'phone.required' => '연락처를 입력해 주세요.',
            'phone.max' => '연락처는 50자 이내로 입력해 주세요.',
            'email.required' => '이메일을 입력해 주세요.',
            'email.email' => '올바른 이메일 형식을 입력해 주세요.',
            'email.max' => '이메일은 255자 이내로 입력해 주세요.',
            'agree-terms.accepted' => '개인정보 이용 및 수집 동의는 필수입니다.',
            'service.required' => '관심 서비스를 1개 이상 선택해 주세요.',
            'service.min' => '관심 서비스를 1개 이상 선택해 주세요.',
            'service.*.in' => '선택한 관심 서비스를 확인해 주세요.',
            'current_site.max' => '현재 사이트 주소는 2048자 이내로 입력해 주세요.',
            'message.max' => '문의 내용은 10000자 이내로 입력해 주세요.',
            'budget.max' => '프로젝트 예산은 500자 이내로 입력해 주세요.',
            'attachments.max' => '첨부파일은 최대 3개까지 업로드할 수 있습니다.',
            'attachments.*.file' => '첨부파일을 확인해 주세요.',
            'attachments.*.max' => '첨부파일 하나당 최대 10MB까지 업로드할 수 있습니다.',
            'attachments.*.mimes' => '허용되지 않는 파일 형식입니다. (pdf, 이미지, 문서, 압축 파일 등)',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            // 1) 허니팟: 봇이 숨김 필드를 채우면 차단
            if (filled((string) $this->input('homepage', ''))) {
                $validator->errors()->add('company', '정상적인 접수 요청이 아닙니다. 다시 시도해 주세요.');

                return;
            }

            // 2) 폼 토큰/시간 검증: 외부 자동 POST, 재전송 요청 차단
            $sessionToken = (string) $this->session()->get('contact_form_token', '');
            $sessionTs = (int) $this->session()->get('contact_form_ts', 0);
            $inputToken = (string) $this->input('contact_token', '');
            $inputTs = (int) $this->input('contact_ts', 0);
            $elapsed = now()->timestamp - $inputTs;

            $invalidToken = $sessionToken === '' || $inputToken === '' || ! hash_equals($sessionToken, $inputToken);
            $invalidTs = $sessionTs <= 0 || $inputTs <= 0 || $sessionTs !== $inputTs;
            $invalidElapsed = $elapsed < self::CONTACT_FORM_MIN_SECONDS || $elapsed > self::CONTACT_FORM_MAX_SECONDS;

            if ($invalidToken || $invalidTs || $invalidElapsed) {
                $validator->errors()->add('company', '요청을 처리할 수 없습니다. 페이지를 새로고침 후 다시 접수해 주세요.');

                return;
            }

            // 3) 핵심 필드 URL 패턴 차단: 기계식 스팸 문구 유입 방지
            foreach (['company', 'contact_person'] as $field) {
                $value = (string) $this->input($field, '');
                if ($value !== '' && preg_match('/(?:https?:\/\/|www\.)/i', $value)) {
                    $validator->errors()->add($field, '정상적인 입력값을 입력해 주세요.');

                    return;
                }
            }
        });
    }
}
