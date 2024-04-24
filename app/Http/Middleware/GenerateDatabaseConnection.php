<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AccountDatabase;

class GenerateDatabaseConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $connections = AccountDatabase::all();
        foreach($connections as $connection) {
            $schema = $connection->database_name;
            \Config::set('database.connections.'.$connection->connection_name, [
                'driver' => 'mysql',
                'url' => NULL,
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => $schema,
                'username' => 'root',
                'password' => '',
                'unix_socket' => '',
                'charset' => 'utf8',
                'collation' => 'utf8_general_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => 'InnoDB',
                'pool' => [
                    'min_connections' => 1,
                    'max_connections' => 10,
                    'max_idle_time' => 30,
                ],
            ]);
        }

        return $next($request);
    }
}
