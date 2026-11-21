<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $query = Project::with('client')->withCount('bids')->latest('id');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('judul', 'like', "%{$q}%")
                    ->orWhere('kategori', 'like', "%{$q}%");
            });
        }

        $projects = $query->get();

        return view('projects.index', compact('projects', 'q'));
    }

    public function show($id)
    {
        $project = Project::with(['client', 'bids.developer'])->findOrFail($id);
        $bids = $project->bids()->orderBy('is_winner', 'desc')->orderBy('harga', 'asc')->orderBy('id', 'asc')->get();

        return view('projects.show', compact('project', 'bids'));
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user || !$user->isKlien()) {
            abort(403, 'Hanya klien yang dapat memasang proyek.');
        }

        return view('projects.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isKlien()) {
            abort(403, 'Hanya klien yang dapat memasang proyek.');
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:200'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'kategori' => ['required', 'string', 'max:50'],
            'bmin' => ['required', 'integer', 'min:0'],
            'bmax' => ['required', 'integer', 'gte:bmin'],
            'batas' => ['required', 'date', 'after_or_equal:today'],
        ], [
            'judul.required' => 'Judul wajib diisi (maksimal 200 karakter).',
            'deskripsi.required' => 'Deskripsi wajib diisi (maksimal 5000 karakter).',
            'kategori.required' => 'Kategori wajib diisi (maksimal 50 karakter).',
            'bmin.min' => 'Budget harus berupa angka bulat tidak negatif.',
            'bmax.gte' => 'Budget tidak valid (maks harus >= min).',
            'batas.after_or_equal' => 'Batas lelang harus tanggal yang valid dan belum lewat.',
        ]);

        $project = Project::create([
            'client_id' => $user->id,
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'kategori' => $validated['kategori'],
            'budget_min' => $validated['bmin'],
            'budget_max' => $validated['bmax'],
            'batas_lelang' => $validated['batas'],
            'status' => 'terbuka',
        ]);

        return redirect()->route('dashboard')->with('flash', ['Proyek berhasil dipasang.', 'ok']);
    }
}
