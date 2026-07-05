<?php

namespace App\Services;

use App\Models\DatabaseHost;
use App\Models\Server;
use App\Models\ServerDatabase;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Provisions and removes real MySQL databases/users on a DatabaseHost using its
 * stored admin credentials.
 */
class DatabaseManager
{
    /**
     * A connection to the host as its admin user (no default database).
     */
    private function connection(DatabaseHost $host): Connection
    {
        $name = 'dbhost_'.$host->id;

        Config::set("database.connections.{$name}", [
            'driver' => 'mysql',
            'host' => $host->host,
            'port' => $host->port,
            'database' => null,
            'username' => $host->username,
            'password' => $host->password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [\PDO::ATTR_TIMEOUT => 5],
        ]);
        DB::purge($name);

        return DB::connection($name);
    }

    /**
     * Create a database + user for the server. Throws on failure (e.g. the host
     * is unreachable or the admin credentials are wrong).
     */
    public function create(Server $server, DatabaseHost $host, string $remote = '%'): ServerDatabase
    {
        $database = 's'.$server->id.'_'.Str::lower(Str::random(8));
        $username = 'u'.$server->id.'_'.Str::lower(Str::random(8));
        $password = Str::random(24);

        $conn = $this->connection($host);
        $conn->statement("CREATE DATABASE IF NOT EXISTS `{$database}`");
        $conn->statement("CREATE USER '{$username}'@'{$remote}' IDENTIFIED BY '{$password}'");
        $conn->statement("GRANT ALL PRIVILEGES ON `{$database}`.* TO '{$username}'@'{$remote}'");
        $conn->statement('FLUSH PRIVILEGES');

        return $server->databases()->create([
            'database_host_id' => $host->id,
            'database' => $database,
            'username' => $username,
            'password' => $password,
            'remote' => $remote,
        ]);
    }

    /**
     * Rotate a database user's password.
     */
    public function rotate(ServerDatabase $db): void
    {
        $password = Str::random(24);
        $conn = $this->connection($db->host);
        $conn->statement("ALTER USER '{$db->username}'@'{$db->remote}' IDENTIFIED BY '{$password}'");
        $conn->statement('FLUSH PRIVILEGES');
        $db->update(['password' => $password]);
    }

    /**
     * Drop the database + user, then remove the record.
     */
    public function drop(ServerDatabase $db): void
    {
        $conn = $this->connection($db->host);
        $conn->statement("DROP USER IF EXISTS '{$db->username}'@'{$db->remote}'");
        $conn->statement("DROP DATABASE IF EXISTS `{$db->database}`");
        $conn->statement('FLUSH PRIVILEGES');
        $db->delete();
    }
}
