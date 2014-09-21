<?php

namespace p33rs\Blastoff;
use p33rs\Blastoff\Controller\BoardController;
use \Exception;
include 'vendor/autoload.php';

try {

    Config::read(__DIR__.'/config.local.php', true);

    $authName = Config::get('authAdapter');
    $controller = new BoardController(new $authName());

    $command = empty($_POST['command']) ? null : $_POST['command'];
    if (!is_callable([$controller, $command])) {
        throw new Exception('Invalid action specified');
    }

    echo json_encode(Response::success($controller->$command()));

} catch (Exception $e) {

    header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
    echo json_encode(Response::fail($e->getMessage()));

}