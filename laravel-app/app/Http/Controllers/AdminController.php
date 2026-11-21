<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function __construct()
    {
        // Admin authorization checked in methods
    }

    public function index()
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Anda tidak berhak mengakses halaman ini.');
        }

        $users = User::orderBy('id')->get();
        $projects = Project::with('client')->latest('id')->get();

        return view('admin.index', compact('users', 'projects'));
    }

    public function updateRole(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Anda tidak berhak mengakses halaman ini.');
        }

        $targetId = (int)$id;
        if ($targetId === $user->id) {
            return back()->with('flash', ['Anda tidak dapat mengubah peran akun sendiri.', 'err']);
        }

        $request->validate([
            'role' => ['required', 'in:klien,developer,admin'],
        ]);

        $target = User::findOrFail($targetId);
        $target->update(['role' => $request->input('role')]);

        return back()->with('flash', ['Peran user diubah.', 'ok']);
    }

    public function deleteUser(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Anda tidak berhak mengakses halaman ini.');
        }

        $targetId = (int)$id;
        if ($targetId === $user->id) {
            return back()->with('flash', ['Anda tidak dapat menghapus akun sendiri.', 'err']);
        }

        $target = User::findOrFail($targetId);
        $target->delete();

        return back()->with('flash', ['User dihapus.', 'ok']);
    }

    public function deleteProject(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->isAdmin()) {
            abort(403, 'Anda tidak berhak mengakses halaman ini.');
        }

        $project = Project::findOrFail((int)$id);
        $project->delete();

        return back()->with('flash', ['Proyek dihapus.', 'ok']);
    }
}
