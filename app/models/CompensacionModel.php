<?php

/**
 * Compensación de horas extra: pagadas (en nómina) o recuperadas (descanso).
 */
class CompensacionModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    /** Listado con datos del empleado, opcionalmente filtrado por rango/empleado. */
    public function getAll(?string $desde = null, ?string $hasta = null, ?int $empId = null): array {
        $sql = "SELECT c.*, e.nombre, e.apellidos
                FROM compensaciones c JOIN empleados e ON e.id = c.id_empleado";
        $where = []; $p = [];
        if ($desde) { $where[] = "c.fecha >= ?"; $p[] = $desde; }
        if ($hasta) { $where[] = "c.fecha <= ?"; $p[] = $hasta; }
        if ($empId) { $where[] = "c.id_empleado = ?"; $p[] = $empId; }
        if ($where) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY c.fecha DESC, c.id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($p);
        return $stmt->fetchAll();
    }

    /** Compensaciones de un empleado dentro de un rango. */
    public function getByEmpleado(int $empId, string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM compensaciones
             WHERE id_empleado = ? AND fecha BETWEEN ? AND ?
             ORDER BY fecha ASC, id ASC"
        );
        $stmt->execute([$empId, $desde, $hasta]);
        return $stmt->fetchAll();
    }

    /** Mapa id_empleado => ['pagadas'=>x,'recuperadas'=>y,'total'=>x+y] en el rango. */
    public function mapaPorEmpleado(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT id_empleado,
                    COALESCE(SUM(CASE WHEN tipo='pagada'     THEN horas END),0) AS pagadas,
                    COALESCE(SUM(CASE WHEN tipo='recuperada' THEN horas END),0) AS recuperadas
             FROM compensaciones
             WHERE fecha BETWEEN ? AND ?
             GROUP BY id_empleado"
        );
        $stmt->execute([$desde, $hasta]);
        $mapa = [];
        foreach ($stmt->fetchAll() as $r) {
            $mapa[(int)$r['id_empleado']] = [
                'pagadas'     => (float)$r['pagadas'],
                'recuperadas' => (float)$r['recuperadas'],
                'total'       => (float)$r['pagadas'] + (float)$r['recuperadas'],
            ];
        }
        return $mapa;
    }

    /** Totales globales del rango. */
    public function totales(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(CASE WHEN tipo='pagada'     THEN horas END),0) AS pagadas,
                    COALESCE(SUM(CASE WHEN tipo='recuperada' THEN horas END),0) AS recuperadas
             FROM compensaciones WHERE fecha BETWEEN ? AND ?"
        );
        $stmt->execute([$desde, $hasta]);
        $r = $stmt->fetch() ?: ['pagadas'=>0,'recuperadas'=>0];
        return [
            'pagadas'     => (float)$r['pagadas'],
            'recuperadas' => (float)$r['recuperadas'],
            'total'       => (float)$r['pagadas'] + (float)$r['recuperadas'],
        ];
    }

    public function crear(array $d): int {
        $tipo = in_array($d['tipo'] ?? '', ['pagada','recuperada'], true) ? $d['tipo'] : 'pagada';
        $stmt = $this->db->prepare(
            "INSERT INTO compensaciones (id_empleado, fecha, horas, tipo, concepto, id_creador)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->execute([
            (int)$d['id_empleado'],
            $d['fecha'],
            (float)$d['horas'],
            $tipo,
            ($d['concepto'] ?? '') !== '' ? trim($d['concepto']) : null,
            $d['id_creador'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function eliminar(int $id): bool {
        return $this->db->prepare("DELETE FROM compensaciones WHERE id=?")->execute([$id]);
    }
}
