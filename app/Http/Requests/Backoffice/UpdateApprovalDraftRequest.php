<?php

namespace App\Http\Requests\Backoffice;

use App\Support\ApprovalExpenseLines;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateApprovalDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'array'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:51200'],
        ];

        return array_merge($rules, $this->templateRules((string) $this->route('docNo')));
    }

    protected function prepareForValidation(): void
    {
        $content = (array) $this->input('content', []);

        if (($content['start_date'] ?? '') === '' && isset($content['period_start'])) {
            $content['start_date'] = $content['period_start'];
        }
        if (($content['end_date'] ?? '') === '' && isset($content['period_end'])) {
            $content['end_date'] = $content['period_end'];
        }
        if (! isset($content['days']) && isset($content['leave_days'])) {
            $content['days'] = $content['leave_days'];
        }
        if (($content['contact'] ?? '') === '' && isset($content['emergency_contact'])) {
            $content['contact'] = $content['emergency_contact'];
        }
        if (($content['replacement_worker'] ?? '') === '' && isset($content['substitute_worker'])) {
            $content['replacement_worker'] = $content['substitute_worker'];
        }

        $lines = (array) ($content['expense_lines'] ?? []);
        if ($lines !== []) {
            $content['expense_lines'] = ApprovalExpenseLines::normalize($lines);
        }

        $this->merge(['content' => $content]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    private function templateRules(string $docNo): array
    {
        if ($docNo === '') {
            return [];
        }

        $templateKey = (string) \App\Models\ApprovalDocument::query()
            ->where('doc_no', $docNo)
            ->value('template_key');

        if ($templateKey === 'proposal-education') {
            return [
                'content.education_rows' => ['nullable', 'array', 'max:10'],
                'content.education_rows.*.education_name' => ['nullable', 'string', 'max:255'],
                'content.education_rows.*.schedule' => ['nullable', 'string', 'max:255'],
                'content.education_rows.*.fee_krw' => ['nullable', 'numeric', 'min:0'],
                'content.education_rows.*.participants' => ['nullable', 'integer', 'min:0'],
                'content.education_rows.*.note' => ['nullable', 'string', 'max:255'],
                'content.education_detail' => ['nullable', 'string'],
            ];
        }

        if ($templateKey === 'vacation-quarter-day') {
            return [
                'content.start_date' => ['required', 'date_format:Y-m-d\TH:i'],
                'content.end_date' => ['required', 'date_format:Y-m-d\TH:i', 'after_or_equal:content.start_date'],
                'content.days' => ['required', 'integer', 'min:1'],
                'content.reason' => ['required', 'string'],
                'content.destination' => ['nullable', 'string', 'max:255'],
                'content.contact' => ['nullable', 'string', 'max:100'],
                'content.replacement_worker' => ['nullable', 'string', 'max:100'],
            ];
        }

        if (str_starts_with($templateKey, 'vacation-')) {
            return [
                'content.start_date' => ['required', 'date'],
                'content.end_date' => ['required', 'date', 'after_or_equal:content.start_date'],
                'content.days' => ['required', 'integer', 'min:1'],
                'content.reason' => ['required', 'string'],
                'content.destination' => ['nullable', 'string', 'max:255'],
                'content.contact' => ['nullable', 'string', 'max:100'],
                'content.replacement_worker' => ['nullable', 'string', 'max:100'],
            ];
        }

        if (str_starts_with($templateKey, 'expense-')) {
            return [
                'content.claim_amount' => ['nullable', 'numeric', 'min:0'],
                'content.settlement_amount' => ['nullable', 'numeric', 'min:0'],
                'content.expense_total' => ['nullable', 'numeric', 'min:0'],
                'content.expense_lines' => ['nullable', 'array', 'max:' . ApprovalExpenseLines::LINE_COUNT],
                'content.expense_lines.*.date' => ['nullable', 'date'],
                'content.expense_lines.*.detail' => ['nullable', 'string', 'max:255'],
                'content.expense_lines.*.amount' => ['nullable', 'numeric', 'min:0'],
                'content.expense_lines.*.note' => ['nullable', 'string', 'max:255'],
            ];
        }

        return [];
    }
}
