<?php

/**
 * Base controller: view loading + shared helpers.
 */
class Controller {

    protected function model(string $model) {
        require_once RUTA_APP . '/models/' . $model . '.php';
        $db = (new Database())->conectar();
        return new $model($db);
    }

    protected function view(string $view, array $data = []) {
        extract($data);
        $file = RUTA_APP . '/views/' . $view . '.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            http_response_code(404);
            echo "Vista no encontrada: " . htmlspecialchars($view);
        }
    }

    /** Read JSON body (AJAX) or fall back to POST. */
    protected function input(): array {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        return is_array($json) ? $json : $_POST;
    }

    protected function json($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
