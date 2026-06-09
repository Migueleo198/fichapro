<?php

class IncidenciaModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll(): array {
        return $this->db->query(
            "SELECT i.*, f.fecha, f.hora_entrada, e.nombre, e.apellidos
             FROM incidencias i
             JOIN fichajes f ON f.id = i.id_fichaje
             JOIN empleados e ON e.id = f.id_empleado
             ORDER BY i.created_at DESC"
        )->fetchAll();
    }

    public function getByEmpleado(int $empId): array {
        $stmt = $this->db->prepare(
            "SELECT i.*, f.fecha, f.hora_entrada
             FROM incidencias i
             JOIN fichajes f ON f.id = i.id_fichaje
             WHERE f.id_empleado = ? ORDER BY i.created_at DESC"
        );
        $stmt->execute([$empId]);
        return $stmt->fetchAll();
    }

    /** Fichajes recientes del empleado para asociar la incidencia. */
    public function fichajesRecientes(int $empId, int $dias = 14): array {
        $stmt = $this->db->prepare(
            "SELECT id, fecha, hora_entrada, hora_salida FROM fichajes
             WHERE id_empleado = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             ORDER BY fecha DESC, id DESC"
        );
        $stmt->execute([$empId, $dias]);
        return $stmt->fetchAll();
    }

    /** Incidencias de un fichaje concreto. */
    public function getByFichaje(int $idFichaje): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM incidencias WHERE id_fichaje = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$idFichaje]);
        return $stmt->fetchAll();
    }

    /** Empleado dueño de un fichaje (para comprobar permisos), o null. */
    public function fichajeDe(int $idFichaje): ?int {
        $stmt = $this->db->prepare("SELECT id_empleado FROM fichajes WHERE id = ?");
        $stmt->execute([$idFichaje]);
        $v = $stmt->fetchColumn();
        return $v !== false ? (int)$v : null;
    }

    public function crear(int $idFichaje, string $mensaje): int {
        // mark the related fichaje as having an incidence
        $this->db->prepare("UPDATE fichajes SET estado='incidencia' WHERE id=? AND estado!='validado'")->execute([$idFichaje]);
        $stmt = $this->db->prepare("INSERT INTO incidencias (id_fichaje, mensaje) VALUES (?,?)");
        $stmt->execute([$idFichaje, $mensaje]);
        return (int)$this->db->lastInsertId();
    }

    public function responder(int $id, string $respuesta): bool {
        return $this->db->prepare("UPDATE incidencias SET respuesta=?, estado='resuelta' WHERE id=?")
                        ->execute([$respuesta, $id]);
    }

    public function eliminar(int $id): bool {
        return $this->db->prepare("DELETE FROM incidencias WHERE id=?")->execute([$id]);
    }
}
