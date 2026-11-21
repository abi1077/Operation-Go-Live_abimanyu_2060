<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Bid;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isAdmin()) {
            return redirect()->route('admin.index');
        }

        if ($user->isKlien()) {
            $projects = Project::where('client_id', $user->id)->withCount('bids')->latest('id')->get();
            return view('dashboard.client', compact('projects'));
        }

        $bids = Bid::with(['project.client'])->where('developer_id', $user->id)->latest('id')->get();
        return view('dashboard.developer', compact('bids'));
    }
}
