<?php

class AjusteModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM ajustes ORDER BY id ASC")->fetchAll();
    }

    public function guardar(array $valores): void {
        $stmt = $this->db->prepare("UPDATE ajustes SET valor = ? WHERE clave = ?");
        foreach ($valores as $clave => $valor) {
            $stmt->execute([$valor, $clave]);
        }
    }
}
