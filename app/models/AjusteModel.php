<?php

class AjusteModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM ajustes ORDER BY id ASC")->fetchAll();
    }

    public function guardar(array $valores): void {
        $stmt = $this->db->prepare(
            "INSERT INTO ajustes (clave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)"
        );
        foreach ($valores as $clave => $valor) {
            $stmt->execute([$clave, $valor]);
        }
    }
}
