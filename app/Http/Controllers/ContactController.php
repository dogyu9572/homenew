<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Portfolio;
use App\Services\ContactMailNotifier;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $contactService,
        private ContactMailNotifier $contactMailNotifier,
    ) {}

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $ipKey = 'contact-submit:'.$request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 3)) {
            return redirect()
                ->route('contact.contact')
                ->withErrors(['company' => '짧은 시간 내 접수가 많습니다. 잠시 후 다시 시도해 주세요.'])
                ->withInput();
        }

        $emailNorm = strtolower(trim((string) $request->input('email', '')));
        $emailKey = $emailNorm !== '' ? 'contact-submit-email:'.hash('sha256', $emailNorm) : '';
        if ($emailKey !== '' && RateLimiter::tooManyAttempts($emailKey, 2)) {
            return redirect()
                ->route('contact.contact')
                ->withErrors(['email' => '동일 이메일로 짧은 기간 내 반복 접수되었습니다. 잠시 후 다시 시도해 주세요.'])
                ->withInput();
        }

        RateLimiter::hit($ipKey, 3600);
        if ($emailKey !== '') {
            RateLimiter::hit($emailKey, 86400);
        }

        $validated = $request->validated();

        $sourceType = $validated['source_type'] ?? null;
        $sourceId = $validated['source_id'] ?? null;
        $sourceTitle = $validated['source_title'] ?? null;
        if ($sourceType === 'portfolio' && $sourceId && ($sourceTitle === null || $sourceTitle === '')) {
            $sourceTitle = Portfolio::query()->whereKey($sourceId)->where('is_active', true)->value('title');
        }

        $contact = $this->contactService->create(
            [
                'company' => $validated['company'],
                'contact_person' => $validated['contact_person'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'services' => $validated['service'],
                'current_site' => $validated['current_site'] ?? null,
                'message' => $validated['message'] ?? null,
                'budget' => $validated['budget'] ?? null,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'source_url' => $validated['source_url'] ?? null,
                'source_title' => $sourceTitle,
            ],
            $request->file('attachments', [])
        );

        $this->contactMailNotifier->send($contact);
        $request->session()->forget(['contact_form_token', 'contact_form_ts']);

        return redirect()
            ->route('contact.contact')
            ->with('contact_submitted', true);
    }
}
