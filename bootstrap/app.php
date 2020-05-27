<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);


// Setup log
$app->configureMonologUsing(function ($monolog) use ($app) {
    // Add log file handler
    $path = $app->storagePath().'/logs/laravel.log';
    $days = $app->make('config')->get('app.log_max_files', 5);
    $level = $app->make('config')->get('app.log_level', 'debug');
    $fileHandler = new \Monolog\Handler\RotatingFileHandler($path, $days, $level);

    $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
    $formatter->includeStacktraces(true);
    $fileHandler->setFormatter($formatter);

    $monolog->pushHandler($fileHandler);

    // Add error log handler
    $errHandler = new \Monolog\Handler\ErrorLogHandler(\Monolog\Handler\ErrorLogHandler::OPERATING_SYSTEM, \Monolog\Logger::DEBUG);

    $formatter = new \Monolog\Formatter\LineFormatter('[%datetime%] %channel%.%level_name%: %message% %context% %extra%', null, false, true);
    $errHandler->setFormatter($formatter);

    $monolog->pushHandler($errHandler);
});

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
