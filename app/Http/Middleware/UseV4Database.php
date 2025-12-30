<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class UseV4Database
{
    public function handle($request, Closure $next)
    {
        // // Example: dynamically switch database connection config
        // // You can customize the connection name or configuration as needed
        // $connectionName = 'mysql_v4';

        // // Set default DB connection dynamically
        // Config::set('database.default', $connectionName);

        // // Optionally, you can disconnect and reconnect to apply the new config
        // DB::purge($connectionName);
        // DB::reconnect($connectionName);

        // // Continue request processing
        // return $next($request);

        $connectionName = 'mysql_v4';

        // Store original connection
        $originalConnection = Config::get('database.default');

        // Store connection name in request for explicit use
        $request->attributes->set('v4_connection', $connectionName);

        // Note: We're NOT switching the default connection here
        // because it breaks Sanctum authentication
        // Controllers should use ->on('mysql_v4') explicitly

        // If you MUST switch the default, do it conditionally
        // but this is NOT recommended as it can break Sanctum
        // Config::set('database.default', $connectionName);

        return $next($request);
    }
}
