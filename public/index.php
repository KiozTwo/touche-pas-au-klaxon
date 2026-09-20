<?php

declare(strict_types=1);

use App\Controllers\AppController;
use App\Repositories\AgencyRepository;
use App\Repositories\TripRepository;
use App\Repositories\UserRepository;
use App\Support\Database;

require dirname(__DIR__) . '/vendor/autoload.php';
$settings = Database::settings(dirname(__DIR__));
date_default_timezone_set($settings['APP_TIMEZONE'] ?? 'Europe/Paris');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
try {
    $db = Database::connect(dirname(__DIR__));
    $controller = new AppController(new TripRepository($db), new AgencyRepository($db), new UserRepository($db));
    $controller->handle($_SERVER['REQUEST_METHOD'] ?? 'GET', parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(500);
    echo 'Une erreur interne est survenue.';
}
