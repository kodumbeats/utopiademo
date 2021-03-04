<?php

if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
}

use Utopia\App;
use Utopia\Swoole\Request;
use Utopia\Swoole\Response;
use Utopia\Swoole\Files;
use Utopia\CLI\Console;
use Swoole\Http\Server;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;

$http = new Server("0.0.0.0", 8080);

Files::load(__DIR__ . '/../public'); // Static files location

App::init(function($response) {
    $response
        ->addHeader('Cache-control', 'no-cache, no-store, must-revalidate')
        ->addHeader('Expires', '-1')
        ->addHeader('Pragma', 'no-cache')
        ->addHeader('X-XSS-Protection', '1;mode=block');
}, ['response'], '*');

App::shutdown(function($request) {
    $date = new DateTime();
    Console::success($date->format('c').' '.$request->getURI());
}, ['request'], '*');

App::get('/')
    ->groups(['home'])
    ->label('scope', 'home')
    ->inject('request')
    ->inject('response')
    ->action(
        function($request, $response) {
            $response->send(Files::getFileContents('/index.html'));
        }
    );

App::get('/hello')
    ->groups(['api'])
    ->inject('request')
    ->inject('response')
    ->action(
        function($request, $response) {
            $response->json(['Hello' => 'World']);
        }
    );

App::get('/goodbye')
    ->groups(['api'])
    ->inject('request')
    ->inject('response')
    ->action(
        function($request, $response) {
            $response->json(['Goodbye' => 'World']);
        }
    );

$http->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) {

    $request = new Request($swooleRequest);
    $response = new Response($swooleResponse);
    $app = new App('America/Toronto');

    try {
        $app->run($request, $response);
    } catch (\Throwable $th) {
        Console::error('There\'s a problem with '.$request->getURI());
        $swooleResponse->end('500: Server Error');
    }
});

$http->start();
