<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BackofficeIntraTaxPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'B_Title' => 'required|string|max:255',
            'B_Name' => 'nullable|string|max:50',
            'B_Email' => 'nullable|string|max:100',
            'B_Category' => 'nullable|string|max:200',
            'B_Content' => 'nullable|string',
            'B_Password' => 'nullable|string|max:100',
            'B_Secret' => 'nullable|in:Y',
            'B_Notice' => 'nullable|in:Y',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240',
        ];
    }
}
