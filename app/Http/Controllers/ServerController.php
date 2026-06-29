<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ServerController extends Controller
{
    /**
     * List the servers visible to the current user.
     * Admins see every server; regular users only their own.
     */
    public function index(): View
    {
        $user = Auth::user();

        $servers = Server::with(['node', 'owner'])
            ->when(! $user->is_admin, fn ($query) => $query->where('owner_id', $user->id))
            ->latest()
            ->get();

        return view('servers.index', compact('servers'));
    }
}
