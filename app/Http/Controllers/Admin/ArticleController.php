<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * مدیریت مقالات (بلاگ) — افزودن، ویرایش، حذف، انتشار/پیش‌نویس.
 */
class ArticleController extends Controller
{
    public function index(): View
    {
        return view('admin.articles', [
            'articles' => Article::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['title']);

        Article::create($data);

        return back()->with('success', 'مقاله ایجاد شد.');
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['title'] !== $article->title) {
            $data['slug'] = $this->uniqueSlug($data['title'], $article->id);
        }

        $article->update($data);

        return back()->with('success', 'مقاله به‌روز شد.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        delete_uploaded_image($article->image_url);
        $article->delete();

        return back()->with('success', 'مقاله حذف شد.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:180'],
            'excerpt' => ['required', 'string', 'max:600'],
            'body' => ['nullable', 'string', 'max:60000'],
            'read_time' => ['nullable', 'integer', 'min:1', 'max:120'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'image_url' => ['nullable', 'string', 'max:2000'],
            'remove_image' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'عنوان مقاله را وارد کنید.',
            'excerpt.required' => 'خلاصهٔ مقاله را وارد کنید.',
            'image.image' => 'فایل انتخاب‌شده تصویر معتبر نیست.',
        ]);

        $article = $request->route('article');
        $currentImage = $article instanceof Article ? (string) $article->image_url : '';

        $imageUrl = $currentImage;

        if ($uploaded = store_uploaded_image($request->file('image'), 'articles')) {
            delete_uploaded_image($currentImage);
            $imageUrl = $uploaded;
        } elseif ($request->boolean('remove_image')) {
            delete_uploaded_image($currentImage);
            $imageUrl = '';
        } elseif (filled($request->input('image_url'))) {
            $imageUrl = (string) $request->input('image_url');
        }

        return [
            'title' => $data['title'],
            'excerpt' => $data['excerpt'],
            'body' => $data['body'] ?? '',
            'image_url' => $imageUrl,
            'read_time' => (int) ($data['read_time'] ?? 4),
            'is_published' => $request->boolean('is_published'),
        ];
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = persian_slug($title, 'article');
        $slug = $base;
        $i = 2;

        while (Article::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
