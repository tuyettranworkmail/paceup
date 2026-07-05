<?php
namespace App\Core;

class Router {
    protected $routes = [];

    public function add($route, $controller, $action) {
        $this->routes[$route] = ['controller' => $controller, 'action' => $action];
    }

    // Hàm xử lý khi không tìm thấy trang (đã đưa vào hàm đúng quy tắc)
    public function handle404() {
        http_response_code(404);
        echo "<h1>404 Not Found</h1><p>Trang bạn truy cập không tồn tại.</p>";
        exit;
    }

    public function dispatch($url) {
        if (array_key_exists($url, $this->routes)) {
            $route = $this->routes[$url];
            $controllerName = 'App\\Controller\\' . $route['controller'];
            $action = $route['action'];

            if (class_exists($controllerName)) {
                $controller = new $controllerName();
                if (method_exists($controller, $action)) {
                    $controller->$action();
                    return;
                }
            }
        }
        
        $this->handle404();
    }
}