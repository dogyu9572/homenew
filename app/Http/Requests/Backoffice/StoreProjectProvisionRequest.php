<?php

namespace App\Http\Requests\Backoffice;

use App\Models\ProjectProvision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreProjectProvisionRequest extends FormRequest
{
    private const IDENTIFIER = 'regex:/^[a-z][a-z0-9-]{1,30}$/';

    private const RESERVED = [
        'root',
        'ubuntu',
        'www-data',
        'mysql',
        'provisioner',
        'admin',
        'nginx',
        'ftp',
    ];

    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:100'],
            'project_name' => [
                'required',
                'string',
                self::IDENTIFIER,
                'unique:project_provisions,project_name',
                Rule::notIn(self::RESERVED),
            ],
            'ftp_username' => [
                'required',
                'string',
                'max:29',
                self::IDENTIFIER,
                'different:project_name',
                'unique:project_provisions,ftp_username',
                Rule::notIn(self::RESERVED),
            ],
            'ftp_password' => ['required', 'string', 'min:8', 'max:128', 'regex:/^[^\r\n]+$/'],
            'db_username' => [
                'required',
                'string',
                self::IDENTIFIER,
                'unique:project_provisions,db_username',
                Rule::notIn(self::RESERVED),
            ],
            'db_password' => ['required', 'string', 'min:8', 'max:128', 'regex:/^[^\r\n]+$/'],
            'stack' => ['required', 'string', Rule::in([ProjectProvision::STACK_LARAVEL, ProjectProvision::STACK_JAVA])],
            'ia_file' => ['nullable', 'file', 'mimes:xlsx', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => '고객사명',
            'project_name' => '프로젝트명',
            'ftp_username' => 'FTP 아이디',
            'ftp_password' => 'FTP 비밀번호',
            'db_username' => 'DB 아이디',
            'db_password' => 'DB 비밀번호',
            'stack' => '프로젝트 종류',
            'ia_file' => '관리자 IA',
        ];
    }

    public function messages(): array
    {
        return [
            'project_name.regex' => '프로젝트명은 영문 소문자로 시작하고, 소문자·숫자·하이픈만 사용할 수 있습니다.',
            'project_name.unique' => '이미 등록된 프로젝트명입니다.',
            'project_name.not_in' => '사용할 수 없는 프로젝트명입니다.',
            'ftp_username.regex' => 'FTP 아이디는 영문 소문자로 시작하고, 소문자·숫자·하이픈만 사용할 수 있습니다.',
            'ftp_username.different' => 'FTP 아이디는 프로젝트명과 달라야 합니다.',
            'ftp_username.unique' => '이미 등록된 FTP 아이디입니다.',
            'ftp_username.not_in' => '사용할 수 없는 FTP 아이디입니다.',
            'db_username.regex' => 'DB 아이디는 영문 소문자로 시작하고, 소문자·숫자·하이픈만 사용할 수 있습니다.',
            'db_username.unique' => '이미 등록된 DB 아이디입니다.',
            'db_username.not_in' => '사용할 수 없는 DB 아이디입니다.',
            'ftp_password.min' => 'FTP 비밀번호는 8자 이상이어야 합니다.',
            'db_password.min' => 'DB 비밀번호는 8자 이상이어야 합니다.',
            'stack.in' => '라라벨 또는 자바만 생성할 수 있습니다.',
            'ia_file.mimes' => '관리자 IA는 xlsx 파일만 올릴 수 있습니다.',
            'ia_file.max' => '관리자 IA는 10MB 이하만 올릴 수 있습니다.',
            'ftp_username.max' => 'FTP 아이디는 29자 이하여야 합니다. 앞에 pub를 붙인 계정이 32자를 넘을 수 없습니다.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $projectName = (string) $this->input('project_name');
            $ftpUsername = (string) $this->input('ftp_username');
            $pubFtpUsername = 'pub'.$ftpUsername;
            $pubProjectName = 'pub'.$projectName;

            if ($pubFtpUsername === $projectName || $pubFtpUsername === $ftpUsername) {
                $validator->errors()->add('ftp_username', 'pub를 붙인 FTP 아이디가 프로젝트명과 겹칩니다.');
            }

            if (in_array($pubFtpUsername, self::RESERVED, true)) {
                $validator->errors()->add('ftp_username', '사용할 수 없는 퍼블 FTP 아이디입니다.');
            }

            $pubTaken = ProjectProvision::query()
                ->where('project_name', $pubProjectName)
                ->orWhere('ftp_username', $pubFtpUsername)
                ->orWhere('project_name', $pubFtpUsername)
                ->exists();
            if ($pubTaken) {
                $validator->errors()->add('ftp_username', '퍼블 사이트 계정 또는 주소가 이미 사용 중입니다.');
            }
        });
    }
}
