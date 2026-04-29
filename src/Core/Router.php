<?php

namespace App\Core;

/**
 * Class Router
 *
 * Menangkap URL request dan mendispatch ke Controller + Method yang tepat.
 * Format URL yang didukung: /controller/method/param
 *
 * @package App\Core
 */
class Router
{
    /** @var array<string, array> Daftar route yang terdaftar */
    private array $routes = [];

    /**
     * Mendaftarkan route GET.
     *
     * @param string   $path     Path URL, misal '/pasien/create'
     * @param callable $callback Fungsi atau [Controller::class, 'method']
     */
    public function get(string $path, callable|array $callback): void
    {
        $this->routes['GET'][$path] = $callback;
    }

    /**
     * Mendaftarkan route POST.
     *
     * @param string   $path
     * @param callable $callback
     */
    public function post(string $path, callable|array $callback): void
    {
        $this->routes['POST'][$path] = $callback;
    }

    /**
     * Menjalankan router: cocokkan URL dengan route terdaftar.
     *
     * @return void
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];

        // Ambil path dari URL, hilangkan query string
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Normalisasi: hilangkan base path /public dari URI
        $basePath = '/hospital-management/public';
        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = '/' . trim($uri, '/'); // pastikan selalu diawali /

        // Exact match dulu
        if (isset($this->routes[$method][$uri])) {
            $this->call($this->routes[$method][$uri]);
            return;
        }

        // Wildcard match: cocokkan pola dengan parameter dinamis
        // Contoh route: /pasien/edit/{id}
        foreach ($this->routes[$method] ?? [] as $pattern => $callback) {
            $regex   = preg_replace('/\{[a-z_]+\}/', '([^/]+)', $pattern);
            $regex   = '@^' . $regex . '$@';
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches); // buang full match
                $this->call($callback, $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        echo '<h1>404 - Halaman Tidak Ditemukan</h1>';
    }

    /**
     * Memanggil callback (controller method atau closure).
     *
     * @param callable|array $callback
     * @param array          $params
     */
    private function call(callable|array $callback, array $params = []): void
    {
        if (is_array($callback)) {
            [$controllerClass, $method] = $callback;
            $controller = new $controllerClass();
            call_user_func_array([$controller, $method], $params);
        } else {
            call_user_func_array($callback, $params);
        }
    }
}
