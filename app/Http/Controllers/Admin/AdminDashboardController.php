<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Node;
use App\Models\Server;
use App\Models\User;
use App\Services\UpdateChecker;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin overview with system-wide stats and update status.
     */
    public function __invoke(UpdateChecker $updates): View
    {
        $servers = Server::get(['id']);

        $stats = [
            'users' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'nodes' => Node::count(),
            'nodes_online' => Node::where('is_online', true)->count(),
            'servers' => $servers->count(),
            // Fallback only; the real running count is computed live in the browser.
            'servers_running' => Server::where('status', 'running')->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'servers' => $servers,
            'update' => $updates->status(),
        ]);
    }
}
