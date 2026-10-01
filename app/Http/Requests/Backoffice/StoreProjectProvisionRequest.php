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
        return (bool) Auth::user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'project_name' => [
                'required',
                'string',
                self::IDENTIFIER,
                'unique:project_provisions,project_name',
                Rule::notIn(self::RESERVED),
            ],
            'root_password' => ['required', 'string', 'min:8', 'max:128', 'regex:/^[^\r\n]+$/'],
            'ftp_username' => [
                'required',
                'string',
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
            'stack' => ['required', 'string', Rule::in([ProjectProvision::STACK_LARAVEL])],
        ];
    }

    public function attributes(): array
    {
        return [
            'project_name' => '프로젝트명',
            'root_password' => 'ROOT 비밀번호',
            'ftp_username' => 'FTP 아이디',
            'ftp_password' => 'FTP 비밀번호',
            'db_username' => 'DB 아이디',
            'db_password' => 'DB 비밀번호',
            'stack' => '프로젝트 종류',
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
            'root_password.min' => 'ROOT 비밀번호는 8자 이상이어야 합니다.',
            'ftp_password.min' => 'FTP 비밀번호는 8자 이상이어야 합니다.',
            'db_password.min' => 'DB 비밀번호는 8자 이상이어야 합니다.',
            'stack.in' => '현재는 라라벨만 생성할 수 있습니다.',
        ];
    }
}
