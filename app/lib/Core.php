<?php

/**
 * Front controller / router.
 * Maps  /controller/method/p1/p2  ->  Controller::method(p1, p2)
 * Defaults to Login::index when no controller is given.
 */
class Core {

    protected $controller = 'Login';
    protected $method     = 'index';
    protected $params     = [];

    public function __construct() {
        $url = $this->parseUrl();

        if (!empty($url[0]) && file_exists(RUTA_APP . '/controllers/' . ucfirst($url[0]) . '.php')) {
            $this->controller = ucfirst($url[0]);
            unset($url[0]);
        }

        require_once RUTA_APP . '/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        $this->params = $url ? array_values($url) : [];
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    private function parseUrl(): array {
        if (!isset($_GET['url'])) return [];
        $url = rtrim($_GET['url'], '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return explode('/', $url);
    }
}
