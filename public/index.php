<?php

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// Sanitize request
preg_replace('/[^-a-zA-Z0-9_]/', '', $_REQUEST['params']);
$params = explode('/', $_REQUEST['params']);

// gebruik models -_-

if ($params[0] == 'posts' || empty($params[0])) {
    $controller = new PostController(R::findAll('posts'));
    decideMethod($controller, $params[1] ?? 'index', $_GET['param'] ?? null, $_POST);
} elseif ($params[0] == 'user') {
    $controller = new UserController();
    decideMethod($controller, $params[1] ?? 'index', $_GET['param'] ?? null, $_POST);
} else {
    error(404, 'Pagina niet gevonden');
}

R::close();
