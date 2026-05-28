<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BackofficeProjectManageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ProjectName' => 'required|string|max:50',
            'CompanyName' => 'nullable|string|max:50',
            'DomainUrl' => 'nullable|string|max:255',
            'DomainAdminUrl' => 'nullable|string|max:255',
            'TestUrl' => 'nullable|string|max:255',
            'DomainSubUrl' => 'nullable|string|max:255',
            'DomainCompany' => 'nullable|string|max:50',
            'ServerCompany' => 'nullable|string|max:50',
            'DevelopLanguage' => 'nullable|string|max:50',
            'DbType' => 'nullable|string|max:50',
            'DbHost' => 'nullable|string|max:50',
            'DbName' => 'nullable|string|max:50',
            'DbId' => 'nullable|string|max:50',
            'DbPasswd' => 'nullable|string|max:50',
            'FtpUrl' => 'nullable|string|max:50',
            'FtpPort' => 'nullable|string|max:50',
            'FtpId' => 'nullable|string|max:50',
            'FtpPasswd' => 'nullable|string|max:50',
            'Domain_Etc' => 'nullable|string',
            'CompanyPerson' => 'nullable|string|max:50',
            'CpEmail' => 'nullable|string|max:50',
            'CpTel' => 'nullable|string|max:20',
            'CpPhone' => 'nullable|string|max:20',
            'CompanyAddPerson' => 'nullable|string',
            'CompanyCollaborator' => 'nullable|string',
            'ProjectGubun' => 'nullable|string|max:10',
            'ProjectIngState' => 'nullable|string|max:10',
            'ReferSiteUrl' => 'nullable|string',
            'ReferEtc' => 'nullable|string',
            'IntraBusiness' => 'nullable|string|max:20',
            'IntraManager' => 'nullable|string|max:20',
            'IntraDesiner' => 'nullable|string|max:20',
            'IntraPublisher' => 'nullable|string|max:20',
            'IntraProgramer' => 'nullable|string|max:20',
            'ProjectSdate' => 'nullable|string|max:10',
            'ProjectEdate' => 'nullable|string|max:10',
            'HostingSdate' => 'nullable|string|max:10',
            'HostingEdate' => 'nullable|string|max:10',
            'LicensessName' => 'nullable|string|max:50',
            'LicensessNumber' => 'nullable|string|max:50',
            'LicensessTel' => 'nullable|string|max:15',
            'LicensessFax' => 'nullable|string|max:15',
            'LicensessPost' => 'nullable|string|max:7',
            'LicensessAddr' => 'nullable|string|max:1000',
            'ProjectMemo' => 'nullable|string',
            'SpeicalMemo' => 'nullable|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
            'existing_attachment_tokens' => 'nullable|array',
            'existing_attachment_tokens.*' => 'string',
        ];
    }
}
