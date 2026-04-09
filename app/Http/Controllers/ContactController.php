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
        $limitKey = 'contact-submit:'.$request->ip();
        if (RateLimiter::tooManyAttempts($limitKey, 4)) {
            return redirect()
                ->route('contact.contact')
                ->withErrors(['company' => '짧은 시간 내 접수가 많습니다. 잠시 후 다시 시도해 주세요.'])
                ->withInput();
        }
        RateLimiter::hit($limitKey, 600);

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
