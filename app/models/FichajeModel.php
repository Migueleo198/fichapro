<?php

class FichajeModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    /** Current open clock-in for an employee, or false. */
    public function getAbierto(int $empId): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM fichajes WHERE id_empleado = ? AND estado = 'abierto' ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$empId]);
        return $stmt->fetch();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare(
            "SELECT f.*, e.nombre, e.apellidos
             FROM fichajes f JOIN empleados e ON e.id = f.id_empleado WHERE f.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Clock in: create an open fichaje (one per employee at a time). */
    public function abrir(int $empId): int {
        if ($this->getAbierto($empId)) {
            throw new \RuntimeException('Ya tienes un fichaje abierto');
        }
        $stmt = $this->db->prepare(
            "INSERT INTO fichajes (id_empleado, fecha, hora_entrada, estado)
             VALUES (?, CURDATE(), CURTIME(), 'abierto')"
        );
        $stmt->execute([$empId]);
        return (int)$this->db->lastInsertId();
    }

    /** Clock out: close any open break, set exit time and compute hours. */
    public function cerrar(int $fichajeId): bool {
        $this->finalizarDescanso($fichajeId); // auto-close a dangling break

        $f = $this->getById($fichajeId);
        if (!$f) return false;

        $salida = date('H:i:s');
        $bruto  = difHoras($f['hora_entrada'], $salida);
        $pausas = $this->totalDescansos($fichajeId);
        $total  = max(0, round($bruto - $pausas, 2));

        $jornada = $this->horasJornada((int)$f['id_empleado']);
        $ord     = min($total, $jornada);
        $extra   = max(0, round($total - $jornada, 2));

        return $this->db->prepare(
            "UPDATE fichajes SET hora_salida=?, total_horas=?, horas_ordinarias=?, horas_extra=?, estado='cerrado'
             WHERE id=?"
        )->execute([$salida, $total, $ord, $extra, $fichajeId]);
    }

    private function horasJornada(int $empId): float {
        $stmt = $this->db->prepare(
            "SELECT horas_dia FROM jornadas WHERE id_empleado = ? ORDER BY fecha_inicio DESC LIMIT 1"
        );
        $stmt->execute([$empId]);
        $h = $stmt->fetchColumn();
        return $h !== false ? (float)$h : (float)DEF_HORAS_DIA;
    }

    // ── Breaks ───────────────────────────────────────────────────────
    public function getDescansoAbierto(int $fichajeId): array|false {
        $stmt = $this->db->prepare(
            "SELECT * FROM descansos WHERE id_fichaje = ? AND hora_fin IS NULL ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([$fichajeId]);
        return $stmt->fetch();
    }

    public function iniciarDescanso(int $fichajeId, string $motivo): int {
        if ($this->getDescansoAbierto($fichajeId)) {
            throw new \RuntimeException('Ya hay un descanso en curso');
        }
        $motivos = ['Descanso','Comida','Pausa personal','Fumar','Gestión laboral','Otro'];
        if (!in_array($motivo, $motivos)) $motivo = 'Descanso';
        $stmt = $this->db->prepare(
            "INSERT INTO descansos (id_fichaje, hora_inicio, motivo) VALUES (?, CURTIME(), ?)"
        );
        $stmt->execute([$fichajeId, $motivo]);
        return (int)$this->db->lastInsertId();
    }

    public function finalizarDescanso(int $fichajeId): bool {
        $d = $this->getDescansoAbierto($fichajeId);
        if (!$d) return false;
        return $this->db->prepare("UPDATE descansos SET hora_fin = CURTIME() WHERE id = ?")
                        ->execute([$d['id']]);
    }

    public function getDescansos(int $fichajeId): array {
        $stmt = $this->db->prepare("SELECT * FROM descansos WHERE id_fichaje = ? ORDER BY id ASC");
        $stmt->execute([$fichajeId]);
        return $stmt->fetchAll();
    }

    private function totalDescansos(int $fichajeId): float {
        $sum = 0.0;
        foreach ($this->getDescansos($fichajeId) as $d) {
            if ($d['hora_fin']) $sum += difHoras($d['hora_inicio'], $d['hora_fin']);
        }
        return round($sum, 2);
    }

    // ── Listings ─────────────────────────────────────────────────────
    public function getByEmpleado(int $empId, ?string $desde = null, ?string $hasta = null): array {
        $sql = "SELECT * FROM fichajes WHERE id_empleado = ?";
        $p = [$empId];
        if ($desde) { $sql .= " AND fecha >= ?"; $p[] = $desde; }
        if ($hasta) { $sql .= " AND fecha <= ?"; $p[] = $hasta; }
        $sql .= " ORDER BY fecha DESC, hora_entrada DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function getAll(array $f = []): array {
        $sql = "SELECT fi.*, e.nombre, e.apellidos
                FROM fichajes fi JOIN empleados e ON e.id = fi.id_empleado WHERE 1=1";
        $p = [];
        if (!empty($f['empleado'])) { $sql .= " AND fi.id_empleado = ?"; $p[] = $f['empleado']; }
        if (!empty($f['desde']))    { $sql .= " AND fi.fecha >= ?"; $p[] = $f['desde']; }
        if (!empty($f['hasta']))    { $sql .= " AND fi.fecha <= ?"; $p[] = $f['hasta']; }
        if (!empty($f['estado']))   { $sql .= " AND fi.estado = ?"; $p[] = $f['estado']; }
        $sql .= " ORDER BY fi.fecha DESC, fi.hora_entrada DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    public function validar(int $id): bool {
        return $this->db->prepare("UPDATE fichajes SET estado='validado' WHERE id=?")->execute([$id]);
    }

    public function eliminar(int $id): bool {
        return $this->db->prepare("DELETE FROM fichajes WHERE id=?")->execute([$id]);
    }

    // ── Dashboard stats ──────────────────────────────────────────────
    public function trabajandoAhora(): array {
        return $this->db->query(
            "SELECT fi.id, fi.hora_entrada, e.nombre, e.apellidos,
                    (SELECT COUNT(*) FROM descansos d WHERE d.id_fichaje = fi.id AND d.hora_fin IS NULL) AS en_descanso
             FROM fichajes fi JOIN empleados e ON e.id = fi.id_empleado
             WHERE fi.estado = 'abierto' ORDER BY fi.hora_entrada ASC"
        )->fetchAll();
    }

    public function statsHoy(): array {
        $row = $this->db->query(
            "SELECT
                COUNT(*) AS fichajes_hoy,
                SUM(estado='abierto') AS activos,
                COALESCE(SUM(total_horas),0) AS horas_hoy
             FROM fichajes WHERE fecha = CURDATE()"
        )->fetch();
        return $row ?: ['fichajes_hoy'=>0,'activos'=>0,'horas_hoy'=>0];
    }

    public function horasMes(int $empId, string $mes): float {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(total_horas),0) FROM fichajes
             WHERE id_empleado = ? AND DATE_FORMAT(fecha,'%Y-%m') = ?"
        );
        $stmt->execute([$empId, $mes]);
        return (float)$stmt->fetchColumn();
    }
}
