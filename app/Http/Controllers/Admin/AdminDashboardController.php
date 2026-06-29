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
        $stats = [
            'users' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'nodes' => Node::count(),
            'nodes_online' => Node::where('is_online', true)->count(),
            'servers' => Server::count(),
            'servers_running' => Server::where('status', 'running')->count(),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'update' => $updates->status(),
        ]);
    }
}
