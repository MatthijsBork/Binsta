<?php

require_once('rb.php');

const DB_NAME = 'yff';
const DB_USER = 'bit_academy';
const DB_PSWRD = 'bit_academy';

R::setup("mysql:host=localhost;dbname=" . DB_NAME, DB_USER, DB_PSWRD);

/**
 * Sets message and message type in $_SESSION['message]
 * uses composer package twig/twig
 */
function showTemplate($template, $variables): void
{
    $loader = new \Twig\Loader\FilesystemLoader('../views');
    $twig = new \Twig\Environment($loader, ['debug' => true]);
    $twig->addGlobal('session', $_SESSION);
    if (isset($_SESSION['user'])) {
        $twig->addGlobal('user', $_SESSION['user']->getData());
    }
    unset($_SESSION['message']);

    $template = $twig->load($template);
    echo $template->render(['vars' => $variables]);
}

/**
 * Get a bean by its type(table) and ID using redbean
 */
function getBeanById(string $typeOfBean, int $queryStringKey)
{
    $bean = R::findOne($typeOfBean, ' id = ? ', [$queryStringKey]);
    if (!$bean) {
        error(404, $typeOfBean . ' met ID ' . $queryStringKey . ' niet gevonden');
    }
    return $bean;
}

/**
 * Shows an error page with error response code
 */
function error(int $number, string $message): void
{
    http_response_code($number);
    showTemplate('/error.twig', ['errmsg' => $message, 'errnr' => $number]);
    die();
}

/**
 * Redirects to a page by setting url
 */
function redirect($to): void
{
    header('location:' . $to);
}

/**
 * Decides what method to call based on query
 */
function decideMethod(object $controller, string $method, $param, array $post): void
{
    if (method_exists($controller, $method) && $method) {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            if (isset($param)) {
                $controller->$method($param);
            } else {
                $controller->$method();
            }
        } else {
            $controller->$method($post);
        }
    } else {
        error(404, 'Not found');
    }
}

/**
 * Sets message and message type in $_SESSION['message]
 * @param string $type types: danger, warning, info, success
 */
function message(string $type, string $message): void
{
    $_SESSION['message'] = [
        'type' => $type,
        'msg' => $message
    ];
}

/**
 * Moves image to set path and returns its filename. 
 * Uses composer package codeguy/upload
 * @param string $path path to uploaded file
 * @param string $name name of uploaded image. e.g. 'default.jpg'
 */
function uploadImage(string $path, string $name, array &$errors): string
{
    $storage = new \Upload\Storage\FileSystem($path);
    $file = new \Upload\File($name, $storage);

    $file->setName($_SESSION['user']->getUsername() . uniqid());

    $file->addValidations(array(
        new \Upload\Validation\Mimetype(array('image/jpg', 'image/png', 'image/jpeg')),
        new \Upload\Validation\Size('5M')
    ));

    try {
        $file->upload();
    } catch (\Exception $e) {
        $errors = $file->getErrors();
    }

    return $file->getNameWithExtension();
}