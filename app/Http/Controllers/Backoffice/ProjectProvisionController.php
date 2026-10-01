<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backoffice\StoreProjectProvisionRequest;
use App\Jobs\ProvisionLaravelProjectJob;
use App\Models\ProjectProvision;
use Illuminate\Support\Facades\Auth;

class ProjectProvisionController extends Controller
{
    public function index()
    {
        $this->ensureSuperAdmin();

        $provisions = ProjectProvision::query()->latest()->paginate(15);

        return view('backoffice.project-provisions.index', compact('provisions'));
    }

    public function create()
    {
        $this->ensureSuperAdmin();

        return view('backoffice.project-provisions.create');
    }

    public function store(StoreProjectProvisionRequest $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validated();

        $provision = ProjectProvision::query()->create([
            'project_name' => $data['project_name'],
            'stack' => $data['stack'],
            'root_password' => $data['root_password'],
            'ftp_username' => $data['ftp_username'],
            'ftp_password' => $data['ftp_password'],
            'db_username' => $data['db_username'],
            'db_password' => $data['db_password'],
            'db_database' => $data['project_name'],
            'status' => ProjectProvision::STATUS_QUEUED,
            'created_by' => Auth::id(),
        ]);

        ProvisionLaravelProjectJob::dispatch($provision->id);

        return redirect()
            ->route('backoffice.project-provisions.show', $provision)
            ->with('success', '프로젝트 생성을 시작했습니다. 완료까지 몇 분 걸릴 수 있습니다.');
    }

    public function show(ProjectProvision $projectProvision)
    {
        $this->ensureSuperAdmin();

        return view('backoffice.project-provisions.show', [
            'provision' => $projectProvision,
        ]);
    }

    private function ensureSuperAdmin(): void
    {
        if (! Auth::user()?->isSuperAdmin()) {
            abort(403, '슈퍼관리자만 프로젝트를 생성할 수 있습니다.');
        }
    }
}
