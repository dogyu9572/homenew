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
        ];
    }

    public function messages(): array
    {
        return [
            'kind.required' => '출근 또는 퇴근을 선택해 주세요.',
            'kind.in' => '출근 또는 퇴만 선택할 수 있습니다.',
            'workplace.required' => '사무실 또는 재택을 선택해 주세요.',
            'workplace.in' => '사무실 또는 재택만 선택할 수 있습니다.',
        ];
    }
}
