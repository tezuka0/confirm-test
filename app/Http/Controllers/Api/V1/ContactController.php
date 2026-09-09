<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexContactRequest;
use App\Http\Requests\Api\StoreContactRequest;
use App\Http\Requests\Api\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function index(IndexContactRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $contacts = Contact::query()
            ->with(['category', 'tags'])
            ->when($validated['keyword'] ?? null, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when($validated['gender'] ?? null, fn ($query, $gender) => $query->where('gender', $gender))
            ->when($validated['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($validated['date'] ?? null, fn ($query, $date) => $query->whereDate('created_at', $date))
            ->latest()
            ->paginate($validated['per_page'] ?? 15);

        return ContactResource::collection($contacts);
    }

    public function show(Contact $contact): ContactResource
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $contact = Contact::create(collect($validated)->except('tag_ids')->toArray());

        if (! empty($validated['tag_ids'])) {
            $contact->tags()->attach($validated['tag_ids']);
        }

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    public function update(UpdateContactRequest $request, Contact $contact): ContactResource
    {
        $validated = $request->validated();

        $contact->update(collect($validated)->except('tag_ids')->toArray());

        if (array_key_exists('tag_ids', $validated)) {
            $contact->tags()->sync($validated['tag_ids'] ?? []);
        }

        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(null, 204);
    }
}
