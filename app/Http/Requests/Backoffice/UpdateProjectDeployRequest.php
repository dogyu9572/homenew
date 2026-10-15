<?php

namespace App\Http\Requests\Backoffice;

use App\Http\Requests\Backoffice\Concerns\ValidatesProjectDeployStack;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProjectDeployRequest extends FormRequest
{
    use ValidatesProjectDeployStack;
    private const RESERVED = [
        'root',
        'ubuntu',
        'www-data',
        'mysql',
        'provisioner',
        'provision',
        'admin',
        'nginx',
        'ftp',
        'homenew',
    ];

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'site_url' => ['required', 'string', 'max:255'],
            'server_ip' => ['required', 'ip'],
            'ftp_username' => [
                'required',
                'string',
                'max:32',
                'regex:/^[A-Za-z][A-Za-z0-9_-]{0,31}$/',
                Rule::notIn(self::RESERVED),
            ],
            'ftp_password' => ['nullable', 'string', 'min:8', 'max:128'],
            'source_path' => ['required', 'string', 'max:255', 'regex:/^\/(?!.*\.\.)[A-Za-z0-9\/_.-]+$/'],
            'git_url' => ['required', 'string', 'max:255'],
            'auto_deploy' => ['required', 'in:Y,N'],
            'environment' => ['required', 'in:dev,live'],
            ...$this->stackRules(),
        ];
    }

    public function withValidator($validator): void
    {
        $this->validateBuildTargets($validator);
    }

    public function messages(): array
    {
        return [
            'customer_name.required' => '고객사명을 입력하세요.',
            'site_url.required' => '주소를 입력하세요.',
            'server_ip.required' => 'IP를 입력하세요.',
            'server_ip.ip' => 'IP 형식이 올바르지 않습니다.',
            'ftp_username.required' => 'SFTP 아이디를 입력하세요.',
            'ftp_password.min' => 'SFTP 비밀번호는 8자 이상이어야 합니다.',
            'source_path.required' => '소스 위치를 입력하세요.',
            'source_path.regex' => '소스 위치는 절대 경로만 사용할 수 있습니다.',
            'git_url.required' => '깃 정보를 입력하세요.',
            'auto_deploy.required' => '자동화 배포 여부를 선택하세요.',
            'environment.required' => '구분을 선택하세요.',
            ...$this->stackMessages(),
        ];
    }
};
