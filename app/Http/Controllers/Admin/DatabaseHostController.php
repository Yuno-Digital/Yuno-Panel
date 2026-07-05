<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DatabaseHost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Throwable;

class DatabaseHostController extends Controller
{
    public function index(): View
    {
        return view('admin.database-hosts.index', [
            'hosts' => DatabaseHost::withCount('databases')->latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.database-hosts.create', ['host' => new DatabaseHost(['port' => 3306])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $host = DatabaseHost::create($this->validated($request));

        return redirect()->route('admin.database-hosts.index')->with($this->test($host));
    }

    public function edit(DatabaseHost $databaseHost): View
    {
        return view('admin.database-hosts.edit', ['host' => $databaseHost]);
    }

    public function update(Request $request, DatabaseHost $databaseHost): RedirectResponse
    {
        $data = $this->validated($request);
        // Keep the existing password if the field was left blank on edit.
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $databaseHost->update($data);

        return redirect()->route('admin.database-hosts.index')->with($this->test($databaseHost));
    }

    public function destroy(DatabaseHost $databaseHost): RedirectResponse
    {
        $databaseHost->delete();

        return redirect()->route('admin.database-hosts.index')->with('status', __('Database host deleted.'));
    }

    /**
     * Try to connect to the host so admins get immediate feedback.
     *
     * @return array<string, string>
     */
    private function test(DatabaseHost $host): array
    {
        try {
            $name = 'dbhost_test_'.$host->id;
            Config::set("database.connections.{$name}", [
                'driver' => 'mysql', 'host' => $host->host, 'port' => $host->port,
                'database' => null, 'username' => $host->username, 'password' => $host->password,
                'options' => [\PDO::ATTR_TIMEOUT => 5],
            ]);
            DB::purge($name);
            DB::connection($name)->select('SELECT 1');

            return ['status' => __('Saved — connection to the database host works.')];
        } catch (Throwable $e) {
            return ['error' => __('Saved, but the host could not be reached: :m', ['m' => $e->getMessage()])];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'linked_host' => ['nullable', 'string', 'max:255'],
            'max_databases' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
