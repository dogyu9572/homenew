<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactRequest;
use App\Models\Portfolio;
use App\Services\ContactService;
use Illuminate\Http\RedirectResponse;

class ContactController extends Controller
{
    public function __construct(private ContactService $contactService) {}

    public function store(StoreContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $sourceType = $validated['source_type'] ?? null;
        $sourceId = $validated['source_id'] ?? null;
        $sourceTitle = $validated['source_title'] ?? null;
        if ($sourceType === 'portfolio' && $sourceId && ($sourceTitle === null || $sourceTitle === '')) {
            $sourceTitle = Portfolio::query()->whereKey($sourceId)->where('is_active', true)->value('title');
        }

        $this->contactService->create(
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

        return redirect()
            ->route('contact.contact')
            ->with('contact_submitted', true);
    }
}
