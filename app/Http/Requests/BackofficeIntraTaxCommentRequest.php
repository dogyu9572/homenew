<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BackofficeIntraTaxCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'C_Name' => 'nullable|string|max:50',
            'C_Passwd' => 'nullable|string|max:100',
            'C_Comment' => 'required|string|max:1000',
        ];
    }
}
