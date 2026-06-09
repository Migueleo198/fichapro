<?php

class JornadaModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll(): array {
        return $this->db->query(
            "SELECT j.*, e.nombre, e.apellidos
             FROM jornadas j JOIN empleados e ON e.id = j.id_empleado
             ORDER BY e.nombre ASC, j.fecha_inicio DESC"
        )->fetchAll();
    }

    public function crear(array $d): int {
        $stmt = $this->db->prepare(
            "INSERT INTO jornadas (id_empleado, horas_dia, horas_semana, fecha_inicio, fecha_fin)
             VALUES (?,?,?,?,?)"
        );
        $stmt->execute([
            (int)$d['id_empleado'], (float)$d['horas_dia'], (float)$d['horas_semana'],
            $d['fecha_inicio'] ?: null, $d['fecha_fin'] ?: null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function eliminar(int $id): bool {
        return $this->db->prepare("DELETE FROM jornadas WHERE id=?")->execute([$id]);
    }
}
