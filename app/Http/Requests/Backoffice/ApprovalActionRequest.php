<?php

namespace App\Http\Requests\Backoffice;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ApprovalActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'in:approve,reject,confirm,delegate,hold'],
            'opinion' => ['nullable', 'string', 'max:5000', 'required_if:action,reject'],
        ];
    }
}
