<?php

namespace App\Core;

/**
 * Class ExceptionHandler
 *
 * Menangani semua exception yang tidak tertangkap secara terpusat.
 * Prinsip: Jangan tampilkan detail error ke user di production,
 * catat ke log, tampilkan pesan yang ramah.
 *
 * @package App\Core
 */
class ExceptionHandler
{
    /**
     * Daftarkan global exception & error handler.
     * Dipanggil SATU KALI di public/index.php.
     */
    public static function register(): void
    {
        // Tangani semua Throwable (Exception + Error) yang tidak di-catch
        set_exception_handler([self::class, 'handle']);

        // Konversi PHP Notice/Warning menjadi ErrorException agar bisa di-catch
        set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
            // Hanya ubah jika error_reporting mencakup severity ini
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        // Tangani fatal error yang tidak bisa di-catch (misal: out of memory)
        register_shutdown_function([self::class, 'handleFatal']);
    }

    /**
     * Handler utama untuk Exception dan Error.
     */
    public static function handle(\Throwable $e): void
    {
        // Log ke file (tidak tampilkan ke user di production)
        self::log($e);

        // Pilih tampilan berdasarkan tipe exception
        $statusCode = self::getStatusCode($e);
        http_response_code($statusCode);

        // Jika request adalah AJAX/API, return JSON
        if (self::isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => self::getUserMessage($e),
            ]);
            return;
        }

        // Render halaman error HTML
        self::renderErrorPage($e, $statusCode);
    }

    /**
     * Handler untuk fatal error PHP.
     */
    public static function handleFatal(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $e = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );
            self::handle($e);
        }
    }

    /**
     * Tulis error ke log file.
     */
    private static function log(\Throwable $e): void
    {
        $logDir  = defined('ROOT_PATH') ? ROOT_PATH . '/logs' : __DIR__ . '/../../../logs';
        $logFile = $logDir . '/app-' . date('Y-m-d') . '.log';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $entry = sprintf(
            "[%s] %s: %s in %s:%d\nTrace: %s\n%s\n",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
            str_repeat('-', 80)
        );

        file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    /**
     * HTTP status code berdasarkan tipe exception.
     */
    private static function getStatusCode(\Throwable $e): int
    {
        return match (true) {
            $e instanceof \InvalidArgumentException => 400,
            $e instanceof \RuntimeException         => 500,
            default                                  => 500,
        };
    }

    /**
     * Pesan ramah untuk user (tidak bocorkan detail teknis).
     */
    private static function getUserMessage(\Throwable $e): string
    {
        // RuntimeException boleh ditampilkan — pesan sudah dibuat user-friendly
        if ($e instanceof \RuntimeException || $e instanceof \InvalidArgumentException) {
            return $e->getMessage();
        }
        return 'Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.';
    }

    private static function isAjaxRequest(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Render halaman error yang rapi dengan Bootstrap.
     */
    private static function renderErrorPage(\Throwable $e, int $code): void
    {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $appName = defined('APP_NAME') ? APP_NAME : 'Sistem RS';
        $message = self::getUserMessage($e);
        $icon    = $code === 404 ? '🔍' : '⚠️';
        $title   = $code === 404 ? '404 — Halaman Tidak Ditemukan' : '500 — Kesalahan Sistem';

        echo <<<HTML
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>{$title} — {$appName}</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh">
                <div class="text-center" style="max-width:480px">
                    <div style="font-size:4rem">{$icon}</div>
                    <h2 class="fw-bold mt-3">{$title}</h2>
                    <p class="text-muted">{$message}</p>
                    <a href="{$baseUrl}/" class="btn btn-primary mt-2">
                        ← Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
