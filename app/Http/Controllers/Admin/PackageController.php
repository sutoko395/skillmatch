<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Services\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PackageController extends Controller
{
    public function index()
    {
        Gate::authorize('manage', Package::class);

        return view('admin.packages.index', ['packages' => Package::orderBy('price')->orderBy('id')->paginate(15)]);
    }

    public function create()
    {
        Gate::authorize('manage', Package::class);

        return view('admin.packages.form', ['package' => new Package]);
    }

    public function store(Request $r, PackageService $service)
    {
        $service->save($r->user(), $r->all());

        return redirect()->route('admin.packages.index')->with('success', 'Paket tersimpan.');
    }

    public function edit(Package $package)
    {
        Gate::authorize('manage', Package::class);

        return view('admin.packages.form', compact('package'));
    }

    public function update(Request $r, Package $package, PackageService $service)
    {
        $service->save($r->user(), $r->all(), $package);

        return redirect()->route('admin.packages.index')->with('success', 'Paket diperbarui; snapshot pembelian lama dipertahankan.');
    }
}
