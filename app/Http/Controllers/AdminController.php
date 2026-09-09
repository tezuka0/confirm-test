<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * お問い合わせ一覧（検索・絞り込み）を表示する。
     */
    public function index(IndexContactRequest $request): View
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
            ->when(
                array_key_exists('gender', $validated) && (int) $validated['gender'] !== 0,
                function ($query) use ($validated) {
                    $query->where('gender', $validated['gender']);
                }
            )
            ->when($validated['category_id'] ?? null, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($validated['date'] ?? null, function ($query, $date) {
                $query->whereDate('created_at', $date);
            })
            ->latest()
            ->paginate(7);

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('categories', 'contacts', 'tags'));
    }

    /**
     * お問い合わせ詳細を表示する。
     */
    public function show(Contact $contact): View
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    /**
     * お問い合わせを削除する。
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('admin.index');
    }
}
