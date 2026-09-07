<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    /**
     * お問い合わせフォームの入力画面を表示する。
     */
    public function index(): View
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    /**
     * 入力内容を検証し、確認画面を表示する。
     */
    public function confirm(StoreContactRequest $request): View
    {
        $validated = $request->validated();

        $category = Category::findOrFail($validated['category_id']);
        $tags = Tag::whereIn('id', $validated['tag_ids'] ?? [])->get();

        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }

    /**
     * お問い合わせ内容を保存し、完了画面へリダイレクトする。
     */
    public function store(StoreContactRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $contact = Contact::create(Arr::except($validated, ['tag_ids']));

        if (!empty($validated['tag_ids'])) {
            $contact->tags()->attach($validated['tag_ids']);
        }

        return redirect()->route('contact.thanks');
    }

    /**
     * 送信完了画面を表示する。
     */
    public function thanks(): View
    {
        return view('contact.thanks');
    }

    public function export(ExportContactRequest $request): StreamedResponse
    {
        $validated = $request->validated();

        $contacts = Contact::query()
            ->with('category')
            ->when($validated['keyword'] ?? null, function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('email', 'like', "%{$keyword}%");
                });
            })
            ->when(
                array_key_exists('gender', $validated) && (int) $validated['gender'] !== 0,
                fn($query) => $query->where('gender', $validated['gender'])
            )
            ->when($validated['category_id'] ?? null, fn($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($validated['date'] ?? null, fn($query, $date) => $query->whereDate('created_at', $date))
            ->latest()
            ->get();

        $genderLabels = [1 => '男性', 2 => '女性', 3 => 'その他'];
        $filename = 'contacts_' . now()->format('YmdHis') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($contacts, $genderLabels) {
            $stream = fopen('php://output', 'w');

            // ExcelでBOMなしCSVを開くと文字化けするため、UTF-8のBOMを先頭に出力する
            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, ['ID', '氏名', '性別', 'メールアドレス', '電話番号', '住所', '建物名', 'お問い合わせの種類', 'お問い合わせ内容', '作成日時']);

            foreach ($contacts as $contact) {
                fputcsv($stream, [
                    $contact->id,
                    $contact->first_name . ' ' . $contact->last_name,
                    $genderLabels[$contact->gender] ?? '',
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category->content ?? '',
                    $contact->detail,
                    $contact->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }
}
