<?php

class TareaModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getByEmpleado(int $empId, int $limit = 100): array {
        $stmt = $this->db->prepare(
            "SELECT t.*, tt.nombre AS tipo_nombre FROM tareas t
             LEFT JOIN tipos_tarea tt ON tt.id = t.id_tipo
             WHERE t.id_empleado = ? ORDER BY t.fecha DESC, t.id DESC LIMIT {$limit}"
        );
        $stmt->execute([$empId]);
        return $stmt->fetchAll();
    }

    public function getAll(): array {
        return $this->db->query(
            "SELECT t.*, tt.nombre AS tipo_nombre, e.nombre, e.apellidos
             FROM tareas t
             LEFT JOIN tipos_tarea tt ON tt.id = t.id_tipo
             JOIN empleados e ON e.id = t.id_empleado
             ORDER BY t.fecha DESC, t.id DESC LIMIT 300"
        )->fetchAll();
    }

    /** Fichaje abierto de hoy (para asociar la tarea), o el último de hoy. */
    public function fichajeDeHoy(int $empId): ?int {
        $stmt = $this->db->prepare(
            "SELECT id FROM fichajes WHERE id_empleado = ? AND fecha = CURDATE() ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$empId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    public function crear(int $empId, array $d): int {
        $idFichaje = $this->fichajeDeHoy($empId);
        if (!$idFichaje) throw new \RuntimeException('Debes fichar la entrada antes de registrar tareas');
        $tot = difHoras($d['hora_inicio'] ?? null, $d['hora_fin'] ?? null);
        $stmt = $this->db->prepare(
            "INSERT INTO tareas (id_fichaje, id_empleado, id_tipo, titulo, descripcion, hora_inicio, hora_fin, total_horas, estado, fecha)
             VALUES (?,?,?,?,?,?,?,?,?,CURDATE())"
        );
        $stmt->execute([
            $idFichaje, $empId, !empty($d['id_tipo']) ? (int)$d['id_tipo'] : null,
            $d['titulo'], $d['descripcion'] ?? null,
            $d['hora_inicio'] ?: null, $d['hora_fin'] ?: null, $tot ?: null,
            in_array($d['estado'] ?? '', ['pendiente','en_progreso','finalizada']) ? $d['estado'] : 'pendiente',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function setEstado(int $id, string $estado): bool {
        if (!in_array($estado, ['pendiente','en_progreso','finalizada'])) return false;
        return $this->db->prepare("UPDATE tareas SET estado=? WHERE id=?")->execute([$estado, $id]);
    }

    public function eliminar(int $id): bool {
        return $this->db->prepare("DELETE FROM tareas WHERE id=?")->execute([$id]);
    }

    public function tipos(): array {
        return $this->db->query("SELECT * FROM tipos_tarea ORDER BY nombre")->fetchAll();
    }

    public function addTipo(string $nombre): int {
        $stmt = $this->db->prepare("INSERT INTO tipos_tarea (nombre) VALUES (?)");
        $stmt->execute([$nombre]);
        return (int)$this->db->lastInsertId();
    }

    public function editTipo(int $id, string $nombre): bool {
        return $this->db->prepare("UPDATE tipos_tarea SET nombre=? WHERE id=?")->execute([$nombre, $id]);
    }

    public function deleteTipo(int $id): bool {
        return $this->db->prepare("DELETE FROM tipos_tarea WHERE id=?")->execute([$id]);
    }
}
