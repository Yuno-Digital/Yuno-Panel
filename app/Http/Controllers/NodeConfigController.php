<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NodeConfigController extends Controller
{
    /**
     * Return a node's daemon configuration. Called by `wings configure` with
     * the node token so the daemon receives (and keeps) a panel-owned token.
     */
    public function show(Request $request, Node $node): JsonResponse
    {
        $token = $request->bearerToken() ?? (string) $request->query('token');

        abort_unless(
            $node->daemon_token !== null && hash_equals($node->daemon_token, $token),
            403,
            'Invalid node token.',
        );

        return response()->json($node->daemonConfig());
    }
}
