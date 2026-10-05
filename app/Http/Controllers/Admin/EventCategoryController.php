<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EventCategoryController extends Controller
{
    public function index()
    {
        $categories = Category::latest()->get();

        return view('admin.master-data.event-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:categories,name',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        Category::create($validated);

        return back()->with('success', 'Kategori event berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $category->update($validated);

        return back()->with('success', 'Kategori event berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->events()->exists()) {
            return back()->withErrors(['master' => 'Data masih direferensikan. Gunakan penonaktifan melalui pengelolaan master setelah tersedia.']);
        }
        $category->delete();

        return back()->with('success', 'Kategori event berhasil dihapus.');
    }
}
