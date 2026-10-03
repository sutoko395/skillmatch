<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SkillController extends Controller
{
    public function index()
    {
        $skills = Skill::latest()->get();

        return view('admin.master-data.skills.index', compact('skills'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:skills,name',
            ],
            'category' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        Skill::create($validated);

        return back()->with('success', 'Skill berhasil ditambahkan.');
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('skills', 'name')->ignore($skill->id),
            ],
            'category' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $skill->update($validated);

        return back()->with('success', 'Skill berhasil diperbarui.');
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();

        return back()->with('success', 'Skill berhasil dihapus.');
    }
}