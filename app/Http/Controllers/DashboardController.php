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

        $serverQuery = Server::query()
            ->when(! $user->is_admin, fn ($query) => $query->where('owner_id', $user->id));

        $stats = [
            'servers' => (clone $serverQuery)->count(),
            'running' => (clone $serverQuery)->where('status', 'running')->count(),
            'nodes' => $user->is_admin ? Node::count() : null,
        ];

        return view('dashboard', compact('stats'));
    }
}
