<?php

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/config.php';
require ROOT_PATH . '/autoload.php';

use App\Core\Router;
use App\Core\Auth;
use App\Controllers\HomeController;
use App\Controllers\PasienController;
use App\Controllers\DokterController;
use App\Controllers\PendaftaranController;
use App\Controllers\AuthController;

// Wajib dipanggil sebelum apapun
Auth::startSession();

$router = new Router();

// ── Auth ──────────────────────────────────────────
$router->get('/login',  [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'processLogin']);
$router->get('/logout', [AuthController::class, 'logout']);

// ── User Management (admin only) ──────────────────
$router->get('/users',              [AuthController::class, 'userIndex']);
$router->post('/users/store',       [AuthController::class, 'userStore']);
$router->post('/users/toggle/{id}', [AuthController::class, 'userToggle']);

// ── Dashboard ─────────────────────────────────────
$router->get('/', [HomeController::class, 'index']);

// ── Pasien ────────────────────────────────────────
$router->get('/pasien',              [PasienController::class, 'index']);
$router->get('/pasien/create',       [PasienController::class, 'create']);
$router->post('/pasien/store',       [PasienController::class, 'store']);
$router->get('/pasien/edit/{id}',    [PasienController::class, 'edit']);
$router->post('/pasien/update/{id}', [PasienController::class, 'update']);
$router->post('/pasien/delete/{id}', [PasienController::class, 'destroy']);

// ── Dokter ────────────────────────────────────────
$router->get('/dokter',              [DokterController::class, 'index']);
$router->get('/dokter/create',       [DokterController::class, 'create']);
$router->post('/dokter/store',       [DokterController::class, 'store']);
$router->get('/dokter/edit/{id}',    [DokterController::class, 'edit']);
$router->post('/dokter/update/{id}', [DokterController::class, 'update']);
$router->post('/dokter/delete/{id}', [DokterController::class, 'destroy']);

// ── Pendaftaran ───────────────────────────────────
$router->get('/pendaftaran',              [PendaftaranController::class, 'index']);
$router->get('/pendaftaran/create',       [PendaftaranController::class, 'create']);
$router->post('/pendaftaran/store',       [PendaftaranController::class, 'store']);
$router->get('/pendaftaran/show/{id}',    [PendaftaranController::class, 'show']);
$router->get('/pendaftaran/edit/{id}',    [PendaftaranController::class, 'edit']);
$router->post('/pendaftaran/update/{id}', [PendaftaranController::class, 'update']);
$router->post('/pendaftaran/delete/{id}', [PendaftaranController::class, 'destroy']);
$router->post('/pendaftaran/cek-nik',          [PendaftaranController::class, 'cekNik']);
$router->post('/pendaftaran/get-dokter',       [PendaftaranController::class, 'getDokterAjax']);

$router->dispatch();
