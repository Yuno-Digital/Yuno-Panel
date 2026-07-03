<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\Server;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the panel overview with high-level stats.
     */
    public function __invoke(): View
    {
        $user = Auth::user();

        $servers = Server::query()
            ->when(! $user->is_admin, fn ($query) => $query->where('owner_id', $user->id))
            ->get();

        $stats = [
            'servers' => $servers->count(),
            // The running count is computed live in the browser from each node
            // daemon; this stored-column value is only an initial fallback.
            'running' => $servers->where('status', 'running')->count(),
            'nodes' => $user->is_admin ? Node::count() : null,
        ];

        return view('dashboard', compact('stats', 'servers'));
    }
}
