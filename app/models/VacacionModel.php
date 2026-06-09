<?php

class VacacionModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll(?string $estado = null): array {
        $sql = "SELECT v.*, e.nombre, e.apellidos FROM vacaciones v JOIN empleados e ON e.id = v.id_empleado";
        $p = [];
        if ($estado) { $sql .= " WHERE v.estado = ?"; $p[] = $estado; }
        $sql .= " ORDER BY v.created_at DESC";
        $stmt = $this->db->prepare($sql); $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getByEmpleado(int $empId): array {
        $stmt = $this->db->prepare("SELECT * FROM vacaciones WHERE id_empleado = ? ORDER BY created_at DESC");
        $stmt->execute([$empId]);
        return $stmt->fetchAll();
    }

    public function crear(int $empId, array $d): int {
        $dias = $this->diasHabiles($d['fecha_inicio'], $d['fecha_fin']);
        $stmt = $this->db->prepare(
            "INSERT INTO vacaciones (id_empleado, fecha_inicio, fecha_fin, dias, comentario)
             VALUES (?,?,?,?,?)"
        );
        $stmt->execute([$empId, $d['fecha_inicio'], $d['fecha_fin'], $dias, $d['comentario'] ?? null]);
        return (int)$this->db->lastInsertId();
    }

    public function setEstado(int $id, string $estado): bool {
        if (!in_array($estado, ['pendiente','aprobada','rechazada'])) return false;
        return $this->db->prepare("UPDATE vacaciones SET estado=? WHERE id=?")->execute([$estado, $id]);
    }

    public function eliminar(int $id): bool {
        return $this->db->prepare("DELETE FROM vacaciones WHERE id=?")->execute([$id]);
    }

    private function diasHabiles(string $ini, string $fin): int {
        $a = new DateTime($ini); $b = new DateTime($fin); $b->modify('+1 day');
        $n = 0;
        foreach (new DatePeriod($a, new DateInterval('P1D'), $b) as $d) {
            if ((int)$d->format('N') < 6) $n++;  // Mon-Fri
        }
        return max(1, $n);
    }
}
