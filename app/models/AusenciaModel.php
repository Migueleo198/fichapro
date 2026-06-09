<?php

class AusenciaModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll(?string $estado = null): array {
        $sql = "SELECT a.*, e.nombre, e.apellidos, t.nombre AS tipo_nombre, t.tipo AS tipo_cat
                FROM ausencias a
                JOIN empleados e ON e.id = a.id_empleado
                LEFT JOIN tipos_ausencia t ON t.id = a.id_tipo";
        $p = [];
        if ($estado) { $sql .= " WHERE a.estado = ?"; $p[] = $estado; }
        $sql .= " ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($sql); $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getByEmpleado(int $empId): array {
        $stmt = $this->db->prepare(
            "SELECT a.*, t.nombre AS tipo_nombre FROM ausencias a
             LEFT JOIN tipos_ausencia t ON t.id = a.id_tipo
             WHERE a.id_empleado = ? ORDER BY a.created_at DESC"
        );
        $stmt->execute([$empId]);
        return $stmt->fetchAll();
    }

    public function crear(int $empId, array $d): int {
        $rem = 1;
        if (!empty($d['id_tipo'])) {
            $stmt = $this->db->prepare("SELECT remunerada FROM tipos_ausencia WHERE id = ?");
            $stmt->execute([(int)$d['id_tipo']]);
            $rem = (int)($stmt->fetchColumn() ?: 1);
        }
        $stmt = $this->db->prepare(
            "INSERT INTO ausencias (id_empleado, id_tipo, motivo_personalizado, fecha_inicio, fecha_fin, horas, remunerada, observaciones)
             VALUES (?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $empId, !empty($d['id_tipo']) ? (int)$d['id_tipo'] : null,
            $d['motivo_personalizado'] ?: null, $d['fecha_inicio'], $d['fecha_fin'] ?: null,
            $d['horas'] !== '' ? $d['horas'] : null, $rem, $d['observaciones'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function setEstado(int $id, string $estado): bool {
        if (!in_array($estado, ['pendiente','aprobada','rechazada'])) return false;
        return $this->db->prepare("UPDATE ausencias SET estado=? WHERE id=?")->execute([$estado, $id]);
    }

    public function eliminar(int $id): bool {
        return $this->db->prepare("DELETE FROM ausencias WHERE id=?")->execute([$id]);
    }

    // ── Tipos de ausencia (catálogo) ─────────────────────────────────
    public function getTipos(bool $soloActivos = true): array {
        $sql = "SELECT * FROM tipos_ausencia";
        if ($soloActivos) $sql .= " WHERE activo = 1";
        $sql .= " ORDER BY nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function crearTipo(array $d): int {
        $stmt = $this->db->prepare(
            "INSERT INTO tipos_ausencia (nombre, descripcion, tipo, remunerada, dias_estimados)
             VALUES (?,?,?,?,?)"
        );
        $stmt->execute([
            $d['nombre'], $d['descripcion'] ?? null,
            in_array($d['tipo'] ?? '', ['baja','permiso','personal','otro']) ? $d['tipo'] : 'permiso',
            (int)($d['remunerada'] ?? 1), (int)($d['dias_estimados'] ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function eliminarTipo(int $id): bool {
        return $this->db->prepare("UPDATE tipos_ausencia SET activo=0 WHERE id=?")->execute([$id]);
    }
}
