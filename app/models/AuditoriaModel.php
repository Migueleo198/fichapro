<?php

class AuditoriaModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function registrar(?int $empId, string $tabla, ?int $idReg, string $accion, ?string $detalle = null): void {
        $this->db->prepare(
            "INSERT INTO auditoria (id_empleado, tabla, id_registro, accion, detalle) VALUES (?,?,?,?,?)"
        )->execute([$empId, $tabla, $idReg, $accion, $detalle]);
    }

    public function getAll(int $limit = 200): array {
        return $this->db->query(
            "SELECT a.*, e.nombre, e.apellidos FROM auditoria a
             LEFT JOIN empleados e ON e.id = a.id_empleado
             ORDER BY a.fecha DESC LIMIT {$limit}"
        )->fetchAll();
    }
}
