<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\StoreProjectProvisionRequest;
use App\Jobs\ApplyProvisionIaJob;
use App\Jobs\ProvisionLaravelProjectJob;
use App\Models\ProjectProvision;
use App\Services\Backoffice\ProjectProvisionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProjectProvisionController extends Controller
{
    public function index(Request $request, ProjectProvisionService $service)
    {
        $scheme = (string) config('provision.url_scheme', 'https');
        $suffix = (string) config('provision.domain_suffix', 'hk-test.co.kr');
        $provisions = ProjectProvision::query()->get()->keyBy('project_name');
        $names = collect($service->serverDirectories());

        foreach ($provisions as $provision) {
            $names->push($provision->project_name);
        }

        $knownUrls = [
            'hkmr' => 'https://vip.homepagekorea.com',
            'homepagekorea' => 'https://homepagekorea.com',
            'aseic' => 'https://aseicglobalforum.or.kr',
        ];

        $names = $names->unique()->values();
        $known = $names->flip();
        $sizes = $service->directorySizes();
        $rows = $names
            ->reject(fn (string $name) => str_starts_with($name, 'pub') && $known->has(substr($name, 3)))
            ->values()
            ->map(function (string $name) use ($provisions, $scheme, $suffix, $service, $knownUrls, $sizes) {
                $provision = $provisions->get($name);
                $bytes = ($sizes[$name] ?? 0) + ($sizes['pub'.$name] ?? 0);

                return (object) [
                    'name' => $name,
                    'customer_name' => $provision?->customer_name ?: '',
                    'ftp_username' => $provision?->ftp_username ?: '',
                    'url' => $knownUrls[$name] ?? ($provision?->site_url ?: $scheme.'://'.$name.'.'.$suffix),
                    'size' => $bytes > 0 ? $service->formatBytes($bytes) : '-',
                    'provision' => $provision,
                    'deletable' => ! $service->isKeptSite($name),
                ];
            })
            ->sortByDesc(fn ($row) => $row->provision?->created_at?->getTimestamp() ?? 0)
            ->values();

        $rows = $this->filterProvisionRows($rows, $request);

        $backups = $service->backupArchives();
        $stats = $service->serverStats();

        return view('backoffice.project-provisions.index', compact('rows', 'backups', 'stats'));
    }

    private function filterProvisionRows($rows, Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));
        if ($keyword === '') {
            return $rows;
        }

        $type = (string) $request->input('search_type', '');

        return $rows->filter(function ($row) use ($keyword, $type) {
            $fields = match ($type) {
                'customer_name' => [$row->customer_name],
                'project_name' => [$row->name],
                'ftp_username' => [$row->ftp_username],
                default => [$row->customer_name, $row->name, $row->ftp_username],
            };

            foreach ($fields as $value) {
                if ($value !== '' && mb_stripos((string) $value, $keyword) !== false) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    public function create()
    {
        return view('backoffice.project-provisions.create');
    }

    public function store(StoreProjectProvisionRequest $request)
    {
        $data = $request->validated();

        $provision = ProjectProvision::query()->create([
            'customer_name' => $data['customer_name'],
            'project_name' => $data['project_name'],
            'stack' => $data['stack'],
            'ftp_username' => $data['ftp_username'],
            'ftp_password' => $data['ftp_password'],
            'db_username' => $data['db_username'],
            'db_password' => $data['db_password'],
            'db_database' => $data['project_name'],
            'status' => ProjectProvision::STATUS_QUEUED,
            'created_by' => Auth::id(),
        ]);

        if ($request->hasFile('ia_file')) {
            $path = $request->file('ia_file')->storeAs('ia-provisions', $provision->id.'.xlsx');
            $fullPath = Storage::disk('local')->path($path);
            if (is_dir(dirname($fullPath))) {
                chmod(dirname($fullPath), 0775);
            }
            if (is_file($fullPath)) {
                chmod($fullPath, 0664);
            }
            $provision->ia_file_path = $path;
            $provision->save();
        }

        ProvisionLaravelProjectJob::dispatch($provision->id);

        return redirect()
            ->route('backoffice.project-provisions.show', $provision)
            ->with('success', '프로젝트 생성을 시작했습니다. 완료까지 몇 분 걸릴 수 있습니다.');
    }

    public function show(ProjectProvision $projectProvision)
    {
        return view('backoffice.project-provisions.show', [
            'provision' => $projectProvision,
        ]);
    }

    public function reapplyIa(Request $request, ProjectProvision $projectProvision)
    {
        if ($projectProvision->status !== ProjectProvision::STATUS_SUCCESS) {
            return back()->with('error', '서버 생성이 끝난 프로젝트만 IA를 다시 적용할 수 있습니다.');
        }

        $request->validate([
            'ia_file' => ['required', 'file', 'mimes:xlsx', 'max:10240'],
        ], [
            'ia_file.required' => 'IA 엑셀 파일을 선택하세요.',
            'ia_file.mimes' => 'IA 파일은 xlsx만 올릴 수 있습니다.',
            'ia_file.max' => 'IA 파일은 10MB 이하만 올릴 수 있습니다.',
        ]);

        $path = $request->file('ia_file')->storeAs('ia-provisions', $projectProvision->id.'.xlsx');
        $fullPath = Storage::disk('local')->path($path);
        if (is_dir(dirname($fullPath))) {
            chmod(dirname($fullPath), 0775);
        }
        if (is_file($fullPath)) {
            chmod($fullPath, 0664);
        }
        $projectProvision->ia_file_path = $path;
        $projectProvision->ia_apply_note = $projectProvision->stack === ProjectProvision::STACK_JAVA
            ? 'IA 재적용을 시작했습니다. 자바 관리자 메뉴와 게시판을 다시 넣습니다.'
            : 'IA 재적용을 시작했습니다. 퍼블 관리자를 기존 모듈로 되돌린 뒤 다시 넣습니다.';
        $projectProvision->save();

        ApplyProvisionIaJob::dispatch($projectProvision->id, true);

        return redirect()
            ->route('backoffice.project-provisions.show', $projectProvision)
            ->with('success', 'IA 재적용을 시작했습니다. 완료까지 몇 분 걸릴 수 있습니다.');
    }

    public function destroy(ProjectProvision $projectProvision, ProjectProvisionService $service)
    {
        try {
            $service->deleteProvision($projectProvision);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('backoffice.project-provisions.index')
            ->with('success', '서버 자료를 삭제했습니다. 백업 파일과 깃 저장소는 남겨 두었습니다.');
    }

    public function destroyDirectory(Request $request, ProjectProvisionService $service)
    {
        try {
            $service->deleteDirectory((string) $request->input('name'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('backoffice.project-provisions.index')
            ->with('success', '서버 자료를 삭제했습니다. 백업 파일과 깃 저장소는 남겨 두었습니다.');
    }

    public function restore(Request $request, ProjectProvisionService $service)
    {
        $file = (string) $request->input('file');

        try {
            $service->restoreBackup($file);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        try {
            $service->deleteBackup($file);
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('backoffice.project-provisions.index')
                ->with('error', '복구했습니다. 백업 파일 삭제는 실패했습니다.');
        }

        $message = '복구했습니다. 백업 파일은 삭제했습니다.';

        return redirect()
            ->route('backoffice.project-provisions.index')
            ->with('success', $message)
            ->with('alert', $message);
    }

    public function destroyBackup(Request $request, ProjectProvisionService $service)
    {
        try {
            $service->deleteBackup((string) $request->input('file'));
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('backoffice.project-provisions.index')
            ->with('success', '백업 파일을 삭제했습니다.');
    }

    public function refreshSizes(ProjectProvisionService $service)
    {
        try {
            $service->refreshDirectorySizes();
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('backoffice.project-provisions.index')
            ->with('success', '용량을 갱신했습니다.');
    }
}
