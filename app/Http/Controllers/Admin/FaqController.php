<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * مدیریت سؤالات متداول صفحهٔ اصلی.
 */
class FaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs', [
            'faqs' => Faq::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        Faq::create($data + ['sort_order' => (int) (Faq::max('sort_order') ?? 1)]);

        return back()->with('success', 'سؤال جدید اضافه شد.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return back()->with('success', 'سؤال به‌روز شد.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('success', 'سؤال حذف شد.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:255'],
            'answer' => ['required', 'string', 'min:5', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'question.required' => 'متن سؤال را وارد کنید.',
            'answer.required' => 'متن پاسخ را وارد کنید.',
        ]);

        $result = [
            'question' => $data['question'],
            'answer' => $data['answer'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->filled('sort_order')) {
            $result['sort_order'] = (int) $data['sort_order'];
        }

        return $result;
    }
}
