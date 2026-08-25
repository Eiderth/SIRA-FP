<?php
header('Content-Type: application/json; charset=utf8');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__. '/backend/controllers/index.php';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$controller = $_GET['controller'];
$action = $_GET['action'];

$Router = new Router();
$Router->run($controller, $action, $input);


?>

