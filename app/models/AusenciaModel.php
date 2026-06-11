<?php

class AusenciaModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll(?string $estado = null): array {
        $sql = "SELECT a.*, e.nombre, e.apellidos, t.nombre AS tipo_nombre, t.tipo AS tipo_cat,
                       t.limite_horas_anual AS tipo_limite
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
            "SELECT a.*, t.nombre AS tipo_nombre, t.limite_horas_anual AS tipo_limite
             FROM ausencias a
             LEFT JOIN tipos_ausencia t ON t.id = a.id_tipo
             WHERE a.id_empleado = ? ORDER BY a.created_at DESC"
        );
        $stmt->execute([$empId]);
        return $stmt->fetchAll();
    }

    /**
     * Crea una ausencia aplicando el límite anual de horas remuneradas del tipo.
     * Las horas que superan el límite (por empleado, tipo y año) no se remuneran.
     *
     * @return array ['id','horas','horas_remuneradas','remunerada','limite','limite_superado']
     */
    public function crear(int $empId, array $d): array {
        $idTipo  = !empty($d['id_tipo']) ? (int)$d['id_tipo'] : null;
        $tipoRem = 1; $limite = 0;
        if ($idTipo) {
            $stmt = $this->db->prepare("SELECT remunerada, COALESCE(limite_horas_anual,0) AS lim FROM tipos_ausencia WHERE id = ?");
            $stmt->execute([$idTipo]);
            if ($t = $stmt->fetch()) { $tipoRem = (int)$t['remunerada']; $limite = (int)$t['lim']; }
        }

        $horasDia = (float) ajuste('horas_jornada_defecto', defined('DEF_HORAS_DIA') ? DEF_HORAS_DIA : 7.5);
        $horas    = $this->calcularHoras($d['fecha_inicio'], $d['fecha_fin'] ?? null, $d['horas'] ?? null, $horasDia);

        // Reparto de horas remuneradas según el límite anual del tipo.
        $horasRem = 0.0; $limiteSuperado = false;
        if ($tipoRem === 1) {
            if ($idTipo && $limite > 0) {
                $anio      = (int) date('Y', strtotime($d['fecha_inicio']));
                $consumido = $this->horasRemuneradasAnio($empId, $idTipo, $anio);
                $restante  = max(0.0, $limite - $consumido);
                $horasRem  = min($horas, $restante);
                if ($horasRem < $horas) $limiteSuperado = true;   // parte (o todo) no se remunera
            } else {
                $horasRem = $horas;   // remunerada sin límite
            }
        }
        $remunerada = $horasRem > 0 ? 1 : 0;

        $stmt = $this->db->prepare(
            "INSERT INTO ausencias (id_empleado, id_tipo, motivo_personalizado, fecha_inicio, fecha_fin, horas, horas_remuneradas, remunerada, observaciones)
             VALUES (?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $empId, $idTipo,
            ($d['motivo_personalizado'] ?? '') !== '' ? $d['motivo_personalizado'] : null,
            $d['fecha_inicio'], ($d['fecha_fin'] ?? '') !== '' ? $d['fecha_fin'] : null,
            $horas, $horasRem, $remunerada, $d['observaciones'] ?? null,
        ]);

        return [
            'id'                => (int)$this->db->lastInsertId(),
            'horas'             => $horas,
            'horas_remuneradas' => $horasRem,
            'remunerada'        => $remunerada,
            'limite'            => $limite,
            'limite_superado'   => $limiteSuperado,
        ];
    }

    /** Horas ya remuneradas (no rechazadas) de un empleado para un tipo y año. */
    private function horasRemuneradasAnio(int $empId, int $idTipo, int $anio, ?int $excluir = null): float {
        $sql = "SELECT COALESCE(SUM(horas_remuneradas),0) FROM ausencias
                WHERE id_empleado = ? AND id_tipo = ? AND estado <> 'rechazada' AND YEAR(fecha_inicio) = ?";
        $p = [$empId, $idTipo, $anio];
        if ($excluir) { $sql .= " AND id <> ?"; $p[] = $excluir; }
        $stmt = $this->db->prepare($sql); $stmt->execute($p);
        return (float)$stmt->fetchColumn();
    }

    /** Horas de la ausencia: usa el valor indicado o estima (días hábiles × horas/día). */
    private function calcularHoras(?string $ini, ?string $fin, $horasOverride, float $horasDia): float {
        if ($horasOverride !== null && $horasOverride !== '' && (float)$horasOverride > 0) {
            return round((float)$horasOverride, 2);
        }
        if (!$ini) return 0.0;
        $a = new DateTime($ini);
        $b = new DateTime($fin ?: $ini);
        if ($b < $a) $b = clone $a;
        $b->modify('+1 day');
        $dias = 0;
        foreach (new DatePeriod($a, new DateInterval('P1D'), $b) as $dt) {
            if ((int)$dt->format('N') < 6) $dias++;   // lunes a viernes
        }
        return round(max(1, $dias) * $horasDia, 2);
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
        $remunerada = (int)($d['remunerada'] ?? 1);
        // Sólo los tipos remunerados pueden tener límite anual de horas.
        $limite = $remunerada ? max(0, (int)($d['limite_horas_anual'] ?? 0)) : 0;
        $stmt = $this->db->prepare(
            "INSERT INTO tipos_ausencia (nombre, descripcion, tipo, remunerada, dias_estimados, limite_horas_anual)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->execute([
            $d['nombre'], $d['descripcion'] ?? null,
            in_array($d['tipo'] ?? '', ['baja','permiso','personal','otro']) ? $d['tipo'] : 'permiso',
            $remunerada, (int)($d['dias_estimados'] ?? 0), $limite,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /** Actualiza el límite anual de horas remuneradas de un tipo (0 = sin límite). */
    public function actualizarLimite(int $id, int $horas): bool {
        return $this->db->prepare(
            "UPDATE tipos_ausencia SET limite_horas_anual = ? WHERE id = ? AND remunerada = 1"
        )->execute([max(0, $horas), $id]);
    }

    public function eliminarTipo(int $id): bool {
        return $this->db->prepare("UPDATE tipos_ausencia SET activo=0 WHERE id=?")->execute([$id]);
    }
}
