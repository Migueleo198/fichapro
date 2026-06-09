<?php

class VehiculoModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll(): array {
        return $this->db->query(
            "SELECT v.*, e.nombre, e.apellidos FROM vehiculos v
             JOIN empleados e ON e.id = v.id_empleado
             ORDER BY e.nombre ASC, v.id DESC"
        )->fetchAll();
    }

    public function crear(array $d): int {
        $stmt = $this->db->prepare(
            "INSERT INTO vehiculos (id_empleado, matricula, marca, modelo, color) VALUES (?,?,?,?,?)"
        );
        $stmt->execute([
            (int)$d['id_empleado'], strtoupper(trim($d['matricula'])),
            $d['marca'] ?: null, $d['modelo'] ?: null, $d['color'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function eliminar(int $id): bool {
        return $this->db->prepare("DELETE FROM vehiculos WHERE id=?")->execute([$id]);
    }
}
