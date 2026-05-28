<?php

namespace App\Http\Requests;

use App\Models\StaffAttendanceRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kind' => [
                'required',
                'string',
                Rule::in([StaffAttendanceRecord::KIND_CLOCK_IN, StaffAttendanceRecord::KIND_CLOCK_OUT]),
            ],
            'workplace' => [
                'required',
                'string',
                Rule::in([StaffAttendanceRecord::WORKPLACE_OFFICE, StaffAttendanceRecord::WORKPLACE_REMOTE]),
            ],
            'recorded_at' => [
                'nullable',
                'date_format:Y-m-d\TH:i',
                'before_or_equal:now',
            ],
            'adjustment_reason' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'kind.required' => '출근 또는 퇴근을 선택해 주세요.',
            'kind.in' => '출근 또는 퇴근만 선택할 수 있습니다.',
            'workplace.required' => '사무실 또는 재택을 선택해 주세요.',
            'workplace.in' => '사무실 또는 재택만 선택할 수 있습니다.',
            'recorded_at.date_format' => '기록일시 형식이 올바르지 않습니다.',
            'recorded_at.before_or_equal' => '기록일시는 현재 시각 이후로 입력할 수 없습니다.',
            'adjustment_reason.max' => '보정 사유는 1000자 이내로 입력해 주세요.',
        ];
    }
}
