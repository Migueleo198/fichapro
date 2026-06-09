<?php

class InformeModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function porEmpleado(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT e.id, e.nombre, e.apellidos,
                    COUNT(f.id)                       AS dias,
                    COALESCE(SUM(f.total_horas),0)    AS horas,
                    COALESCE(SUM(f.horas_extra),0)    AS extra
             FROM empleados e
             LEFT JOIN fichajes f ON f.id_empleado = e.id
                  AND f.fecha BETWEEN ? AND ? AND f.estado != 'abierto'
             WHERE e.activo = 1
             GROUP BY e.id
             ORDER BY horas DESC"
        );
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll();
    }

    public function totales(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS fichajes,
                    COUNT(DISTINCT id_empleado) AS empleados,
                    COALESCE(SUM(total_horas),0) AS horas,
                    COALESCE(SUM(horas_extra),0) AS extra
             FROM fichajes WHERE fecha BETWEEN ? AND ? AND estado != 'abierto'"
        );
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetch() ?: ['fichajes'=>0,'empleados'=>0,'horas'=>0,'extra'=>0];
    }

    public function porDia(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT fecha, COALESCE(SUM(total_horas),0) AS horas
             FROM fichajes WHERE fecha BETWEEN ? AND ? AND estado != 'abierto'
             GROUP BY fecha ORDER BY fecha ASC"
        );
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll();
    }
}
