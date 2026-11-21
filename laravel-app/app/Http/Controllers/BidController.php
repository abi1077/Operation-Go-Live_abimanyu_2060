<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Bid;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isDeveloper()) {
            abort(403, 'Hanya developer yang dapat mengajukan penawaran.');
        }

        $validated = $request->validate([
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'harga' => ['required', 'integer', 'min:1', 'max:999999999999'],
            'durasi' => ['required', 'integer', 'min:1', 'max:3650'],
            'proposal' => ['required', 'string', 'max:5000'],
        ], [
            'harga.min' => 'Harga harus bilangan bulat positif yang wajar.',
            'durasi.min' => 'Durasi harus 1 sampai 3650 hari.',
            'proposal.required' => 'Proposal wajib diisi (maksimal 5000 karakter).',
        ]);

        $project = Project::findOrFail($validated['project_id']);
        if (!$project->isOpen()) {
            return back()->with('flash', ['Lelang sudah ditutup; penawaran tidak dapat diajukan.', 'err']);
        }

        $existing = Bid::where('project_id', $project->id)->where('developer_id', $user->id)->first();
        if ($existing) {
            return back()->with('flash', ['Anda sudah mengajukan penawaran pada proyek ini.', 'err']);
        }

        Bid::create([
            'project_id' => $project->id,
            'developer_id' => $user->id,
            'harga' => $validated['harga'],
            'durasi' => $validated['durasi'],
            'proposal' => $validated['proposal'],
        ]);

        return redirect()->route('projects.show', $project->id)->with('flash', ['Penawaran berhasil dikirim.', 'ok']);
    }

    public function pickWinner(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isKlien()) {
            abort(403, 'Hanya klien yang dapat memilih pemenang.');
        }

        $projectId = (int)$request->input('project_id');
        $bidId = (int)$request->input('bid_id');

        try {
            DB::transaction(function () use ($user, $projectId, $bidId) {
                $project = Project::where('id', $projectId)->where('client_id', $user->id)->lockForUpdate()->first();

                if (!$project) {
                    throw new \Exception('Proyek tidak ditemukan atau bukan milik Anda.');
                }
                if ($project->status !== 'terbuka') {
                    throw new \Exception('Pemenang sudah dipilih atau lelang sudah ditutup.');
                }

                $bid = Bid::where('id', $bidId)->where('project_id', $projectId)->first();
                if (!$bid) {
                    throw new \Exception('Penawaran tidak valid untuk proyek ini.');
                }

                $bid->update(['is_winner' => true]);
                $project->update(['status' => 'ditutup']);
            });

            return redirect()->route('projects.show', $projectId)->with('flash', ['Pemenang berhasil dipilih.', 'ok']);
        } catch (\Exception $e) {
            return redirect()->route('projects.show', $projectId)->with('flash', [$e->getMessage(), 'err']);
        }
    }
}
