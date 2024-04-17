<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AccountBranch;

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
        $branches = AccountBranch::all();
        foreach($branches as $branch) {
            $schema = 'kojiesanadmin_sto_online_'.$branch->account_id.'_db';
            \Config::set('database.connections.account_'.$branch->account_id.'_db', [
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
