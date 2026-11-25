<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isKlien()) {
            abort(403, 'Hanya klien yang dapat memberikan rating.');
        }

        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'skor' => ['required', 'integer', 'min:1', 'max:5'],
            'ulasan' => ['nullable', 'string', 'max:1000'],
        ]);

        $project = Project::with('bids')->findOrFail($validated['project_id']);
        if ($project->client_id !== $user->id) {
            return back()->with('flash', ['Proyek bukan milik Anda.', 'err']);
        }

        $winnerBid = $project->bids()->where('is_winner', true)->first();
        if (!$winnerBid) {
            return back()->with('flash', ['Belum ada pemenang pada proyek ini.', 'err']);
        }

        $existing = Rating::where('project_id', $project->id)->where('client_id', $user->id)->first();
        if ($existing) {
            return back()->with('flash', ['Anda sudah memberikan rating untuk proyek ini.', 'err']);
        }

        Rating::create([
            'project_id' => $project->id,
            'client_id' => $user->id,
            'developer_id' => $winnerBid->developer_id,
            'skor' => $validated['skor'],
            'ulasan' => $validated['ulasan'],
        ]);

        return back()->with('flash', ['Terima kasih, rating berhasil dikirim!', 'ok']);
    }
}
