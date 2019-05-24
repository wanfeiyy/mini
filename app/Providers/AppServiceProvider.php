<?php

namespace App\Providers;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\ServiceProvider;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $isProd = app()->environment('production');
        if (!$isProd || config('app.log_sql')) {
            \DB::listen(function (QueryExecuted $executed) use ($isProd) {
                if ($isProd && stripos($executed->sql, 'select') === 0) {
//                    生产环境不记录Select语句
                    return;
                }
                $bindings = $executed->bindings ?: [];
                foreach ($bindings as &$binding) {
                    if (is_string($binding)) {
                        $binding = addslashes($binding);
                        $binding = "'{$binding}'";
                    }
                }
                unset($binding);

                if (strpos($executed->sql, '%s') !== false
                    || strpos($executed->sql, '%d') !== false
                    || strpos($executed->sql, '%f') !== false
                ) {
                    $sql = sprintf('[Query] Time: %s, SQL: %s, Binds: %s', $executed->time, $executed->sql,
                        implode(',', $bindings));
                    //\Log::info($sql);
                } else {
                    $params = array_merge([str_replace('?', '%s', $executed->sql)], $bindings);
                    $sql = call_user_func_array('sprintf', $params);
                    $sql = sprintf('[Query] Time: %s, SQL: %s', $executed->time, $sql);
                    //\Log::info($sql);
                }

                $handler = new StreamHandler(storage_path('logs/sql-' . date('Y-m-d') . '.log'), Logger::DEBUG);
                $handler->setFormatter(new LineFormatter(null, null, true, true));
                $log = new Logger('sql');
                $log->pushHandler($handler);
                $log->addInfo($sql);
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
