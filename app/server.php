<?php

if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
}

use Utopia\App;
use Utopia\Swoole\Request;
use Utopia\Swoole\Response;
use Utopia\Swoole\Files;
use Swoole\Http\Server;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

$http = new Server("0.0.0.0", 8080);

App::get('/hello')
    ->inject('request')
    ->inject('response')
    ->action(
        function($request, $response) {
            $response
                ->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->addHeader('Expires', '0')
                ->addHeader('Pragma', 'no-cache')
                ->json(['Hello' => 'World']);
        }
    );

App::get('/goodbye')
    ->inject('request')
    ->inject('response')
    ->action(
        function($request, $response) {
            $response
                ->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->addHeader('Expires', '0')
                ->addHeader('Pragma', 'no-cache')
                ->json(['Goodbye' => 'World']);
        }
    );


$http->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) {

    $request = new Request($swooleRequest);
    $response = new Response($swooleResponse);
    $app = new App('America/Toronto');
    
    try {
        $app->run($request, $response);
    } catch (\Throwable $th) {
        $swooleResponse->end('500: Server Error');
    }
});

$http->start();
