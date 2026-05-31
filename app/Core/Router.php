<?php

namespace App\Core;

class Router
{
    private $routes = [];

    /**
     * Define a GET route
     */
    public function get($url, $action)
    {
        $this->routes['GET'][$url] = $action;
    }

    /**
     * Define a POST route
     */
    public function post($url, $action)
    {
        $this->routes['POST'][$url] = $action;
    }

    /**
     * Define a route that handles both GET and POST
     */
    public function any($url, $action)
    {
        $this->routes['GET'][$url] = $action;
        $this->routes['POST'][$url] = $action;
    }

    /**
     * Dispatch the current request to the matched route
     */
    public function dispatch($url)
    {
        $url = parse_url($url, PHP_URL_PATH) ?? $url;
        $url = trim($url, '/');
        if (empty($url)) {
            $url = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method][$url])) {
            $action = $this->routes[$method][$url];
            call_user_func($action);
        } elseif (isset($this->routes['GET'][$url]) && $method === 'HEAD') {
             call_user_func($this->routes['GET'][$url]);
        } else {
            // Handle 404
            http_response_code(404);
            if (is_ajax_request()) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => '404 Not Found']);
            } else {
                echo "<h1>404 Not Found</h1>";
            }
        }
    }
}
