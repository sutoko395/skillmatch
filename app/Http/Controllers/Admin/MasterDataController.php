<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Skill;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    // Halaman kelola Skill & Kategori
    public function index()
    {
        $skills = Skill::latest()->get();
        $categories = Category::latest()->get();
        return view('admin.master-data', compact('skills', 'categories'));
    }

    // Simpan Skill Baru
    public function storeSkill(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        Skill::create($request->only('name', 'category'));

        return back()->with('success', 'Skill berhasil ditambahkan!');
    }

    // Hapus Skill
    public function destroySkill(Skill $skill)
    {
        $skill->delete();
        return back()->with('success', 'Skill berhasil dihapus!');
    }

    // Simpan Kategori Baru
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Category::create($request->only('name', 'description'));

        return back()->with('success', 'Kategori berhasil ditambahkan!');
    }

    // Hapus Kategori
    public function destroyCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Kategori berhasil dihapus!');
    }
}