<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminRequest extends FormRequest
{
    /**
     * 요청에 대한 권한을 확인합니다.
     */
    public function authorize(): bool
    {
        return true; // 컨트롤러에서 권한 체크
    }

    /**
     * 검증 전 입력 정리 (로그인 ID 앞뒤 공백 제거, 빈 문자열은 null)
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('login_id')) {
            $trimmed = trim((string) $this->input('login_id', ''));
            $this->merge([
                'login_id' => $trimmed === '' ? null : $trimmed,
            ]);
        }
    }

    /**
     * 유효성 검사 규칙을 정의합니다.
     */
    public function rules(): array
    {
        $adminId = $this->route('admin');

        // 라우트 모델 바인딩으로 User 인스턴스가 전달된 경우
        if ($adminId instanceof User) {
            $adminId = $adminId->id;
        }

        return [
            'login_id' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users')->ignore($adminId),
            ],
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($adminId),
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'department' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'admin_group_id' => 'nullable|exists:admin_groups,id',
        ];
    }

    /**
     * 유효성 검사 메시지를 정의합니다.
     */
    public function messages(): array
    {
        return AdminValidationMessages::getUpdateMessages();
    }
}
