<?php

namespace App\Services\Backoffice;

use App\Models\Contact;
use App\Models\Portfolio;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ContactService
{
    public function getPaginatedList(Request $request): LengthAwarePaginator
    {
        $query = Contact::query()->orderByDesc('created_at');

        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');
            $query->where(function ($q) use ($keyword) {
                $q->where('company', 'like', '%'.$keyword.'%')
                    ->orWhere('contact_person', 'like', '%'.$keyword.'%')
                    ->orWhere('phone', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%')
                    ->orWhere('message', 'like', '%'.$keyword.'%')
                    ->orWhere('source_title', 'like', '%'.$keyword.'%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $perPage = min(100, max(10, $perPage));

        $paginator = $query->paginate($perPage)->withQueryString();

        $collection = $paginator->getCollection();
        $portfolioIds = $collection
            ->filter(fn (Contact $c) => $c->source_type === 'portfolio' && $c->source_id
                && ($c->source_title === null || $c->source_title === ''))
            ->pluck('source_id')
            ->unique()
            ->values();
        $titleMap = $portfolioIds->isNotEmpty()
            ? Portfolio::query()->whereIn('id', $portfolioIds)->pluck('title', 'id')
            : collect();
        $collection->each(function (Contact $c) use ($titleMap) {
            $resolved = $c->source_title;
            if (($resolved === null || $resolved === '') && $c->source_type === 'portfolio' && $c->source_id) {
                $resolved = $titleMap[$c->source_id] ?? null;
            }
            $c->setAttribute('resolved_source_title', $resolved);
        });

        return $paginator;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Contact $contact, array $data): void
    {
        $contact->update([
            'company' => $data['company'],
            'contact_person' => $data['contact_person'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'services' => $data['service'],
            'current_site' => $data['current_site'] ?? null,
            'message' => $data['message'] ?? null,
            'budget' => $data['budget'] ?? null,
            'status' => $data['status'],
            'admin_memo' => $data['admin_memo'] ?? null,
        ]);
    }
}
