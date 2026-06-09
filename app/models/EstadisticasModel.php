<?php

class EstadisticasModel {

    private $db;

    public function __construct($db) { $this->db = $db; }

    // ── Resumen general ──────────────────────────────────────────────
    public function getResumenGeneral(): array {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM empleados WHERE activo = 1) AS empleados_activos,
                    (SELECT COUNT(*) FROM fichajes WHERE fecha = CURDATE()) AS fichajes_hoy,
                    (SELECT COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE, hora_entrada, hora_salida)/60.0,2) ELSE 0 END),0) FROM fichajes WHERE fecha = CURDATE()) AS horas_hoy,
                    (SELECT COUNT(*) FROM fichajes WHERE fecha = CURDATE() AND hora_entrada > (SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(hora_entrada) BETWEEN 5 AND 12) AS retrasos_hoy,
                    (SELECT COUNT(*) FROM fichajes WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())) AS fichajes_mes,
                    (SELECT COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2) ELSE 0 END),0) FROM fichajes WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE())) AS horas_mes,
                    (SELECT COUNT(*) FROM fichajes WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE()) AND hora_entrada > (SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(hora_entrada) BETWEEN 5 AND 12) AS retrasos_mes,
                    (SELECT COALESCE(SUM(GREATEST(0,ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2)-7.5)),0) FROM fichajes WHERE MONTH(fecha)=MONTH(CURDATE()) AND YEAR(fecha)=YEAR(CURDATE()) AND hora_salida IS NOT NULL) AS horas_extra_mes";
        return $this->db->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function getFichajesPorDiaSemana(): array {
        $sql = "SELECT
                    DAYOFWEEK(fecha) AS dia_num,
                    CASE DAYOFWEEK(fecha)
                        WHEN 2 THEN 'Lunes' WHEN 3 THEN 'Martes' WHEN 4 THEN 'Miércoles'
                        WHEN 5 THEN 'Jueves' WHEN 6 THEN 'Viernes' WHEN 7 THEN 'Sábado' WHEN 1 THEN 'Domingo'
                    END AS dia,
                    COUNT(*) AS total
                FROM fichajes
                WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                GROUP BY DAYOFWEEK(fecha) ORDER BY DAYOFWEEK(fecha)";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFichajesUltimosMeses(int $meses = 6): array {
        $stmt = $this->db->prepare(
            "SELECT
                DATE_FORMAT(fecha,'%Y-%m') AS mes,
                DATE_FORMAT(fecha,'%b %Y') AS mes_label,
                COUNT(*) AS total_fichajes,
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2) ELSE 0 END),0) AS total_horas,
                COUNT(CASE WHEN hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS total_retrasos
             FROM fichajes
             WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL :m MONTH)
             GROUP BY DATE_FORMAT(fecha,'%Y-%m') ORDER BY mes ASC"
        );
        $stmt->execute([':m' => $meses]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopEmpleadosHoras(int $limite = 5): array {
        $stmt = $this->db->prepare(
            "SELECT e.nombre, e.apellidos,
                COALESCE(SUM(CASE WHEN f.hora_salida IS NOT NULL THEN LEAST(ROUND(TIMESTAMPDIFF(MINUTE,f.hora_entrada,f.hora_salida)/60.0,2),7.5) ELSE 0 END),0) AS horas_ordinarias,
                COALESCE(SUM(CASE WHEN f.hora_salida IS NOT NULL THEN GREATEST(0,ROUND(TIMESTAMPDIFF(MINUTE,f.hora_entrada,f.hora_salida)/60.0,2)-7.5) ELSE 0 END),0) AS horas_extra,
                COALESCE(SUM(CASE WHEN f.hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,f.hora_entrada,f.hora_salida)/60.0,2) ELSE 0 END),0) AS total_horas
             FROM empleados e
             LEFT JOIN fichajes f ON f.id_empleado=e.id AND MONTH(f.fecha)=MONTH(CURDATE()) AND YEAR(f.fecha)=YEAR(CURDATE())
             WHERE e.activo=1
             GROUP BY e.id ORDER BY total_horas DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Fichajes ────────────────────────────────────────────────────
    public function getResumenFichajes(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total,
                COUNT(CASE WHEN hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS retrasos,
                COUNT(CASE WHEN estado IN ('cerrado','validado') THEN 1 END) AS normales,
                COUNT(CASE WHEN estado='abierto' THEN 1 END) AS abiertos,
                AVG(CASE WHEN hora_salida IS NOT NULL THEN TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida) ELSE NULL END)/60 AS duracion_media_horas
             FROM fichajes WHERE fecha BETWEEN :d AND :h"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getFichajesPorEstado(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT estado, COUNT(*) AS total FROM fichajes WHERE fecha BETWEEN :d AND :h GROUP BY estado"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFichajesDiarios(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT fecha, COUNT(*) AS total_fichajes,
                COUNT(CASE WHEN hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS retrasos,
                COUNT(CASE WHEN estado IN ('cerrado','validado') THEN 1 END) AS completados
             FROM fichajes WHERE fecha BETWEEN :d AND :h GROUP BY fecha ORDER BY fecha ASC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFichajesPorEmpleado(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT e.nombre, e.apellidos,
                COUNT(f.id) AS total_fichajes,
                COUNT(CASE WHEN f.hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(f.hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS retrasos,
                COALESCE(SUM(CASE WHEN f.hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,f.hora_entrada,f.hora_salida)/60.0,2) ELSE 0 END),0) AS total_horas
             FROM empleados e
             LEFT JOIN fichajes f ON f.id_empleado=e.id AND f.fecha BETWEEN :d AND :h
             WHERE e.activo=1 GROUP BY e.id ORDER BY total_fichajes DESC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Horas ───────────────────────────────────────────────────────
    public function getResumenHoras(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN LEAST(ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2),7.5) ELSE 0 END),0) AS total_ordinarias,
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN GREATEST(0,ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2)-7.5) ELSE 0 END),0) AS total_extra,
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2) ELSE 0 END),0) AS total_horas,
                COALESCE(AVG(CASE WHEN hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2) ELSE NULL END),0) AS media_diaria
             FROM fichajes WHERE fecha BETWEEN :d AND :h"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getHorasPorDia(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT fecha,
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN LEAST(ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2),7.5) ELSE 0 END),0) AS ordinarias,
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN GREATEST(0,ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2)-7.5) ELSE 0 END),0) AS extra,
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2) ELSE 0 END),0) AS total
             FROM fichajes WHERE fecha BETWEEN :d AND :h GROUP BY fecha ORDER BY fecha ASC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHorasPorEmpleado(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT e.nombre, e.apellidos,
                COALESCE(SUM(CASE WHEN f.hora_salida IS NOT NULL THEN LEAST(ROUND(TIMESTAMPDIFF(MINUTE,f.hora_entrada,f.hora_salida)/60.0,2),7.5) ELSE 0 END),0) AS ordinarias,
                COALESCE(SUM(CASE WHEN f.hora_salida IS NOT NULL THEN GREATEST(0,ROUND(TIMESTAMPDIFF(MINUTE,f.hora_entrada,f.hora_salida)/60.0,2)-7.5) ELSE 0 END),0) AS extra,
                COALESCE(SUM(CASE WHEN f.hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,f.hora_entrada,f.hora_salida)/60.0,2) ELSE 0 END),0) AS total,
                COUNT(f.id) AS dias_trabajados
             FROM empleados e
             LEFT JOIN fichajes f ON f.id_empleado=e.id AND f.fecha BETWEEN :d AND :h
             WHERE e.activo=1 GROUP BY e.id ORDER BY total DESC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Retrasos ────────────────────────────────────────────────────
    public function getResumenRetrasos(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total_fichajes,
                COUNT(CASE WHEN hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS total_retrasos,
                ROUND(COUNT(CASE WHEN hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(hora_entrada) BETWEEN 5 AND 12 THEN 1 END)*100.0/NULLIF(COUNT(*),0),1) AS pct_retraso,
                (SELECT e2.nombre FROM empleados e2 INNER JOIN fichajes f2 ON f2.id_empleado=e2.id
                 WHERE f2.hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada')
                   AND HOUR(f2.hora_entrada) BETWEEN 5 AND 12 AND f2.fecha BETWEEN :d2 AND :h2
                 GROUP BY f2.id_empleado ORDER BY COUNT(*) DESC LIMIT 1) AS empleado_mas_retrasos
             FROM fichajes WHERE fecha BETWEEN :d AND :h"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta, ':d2' => $desde, ':h2' => $hasta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRetrasosPorDia(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT fecha,
                COUNT(CASE WHEN hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS retrasos,
                COUNT(*) AS total_fichajes
             FROM fichajes WHERE fecha BETWEEN :d AND :h GROUP BY fecha ORDER BY fecha ASC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRetrasosPorEmpleado(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT e.nombre, e.apellidos,
                COUNT(f.id) AS total_fichajes,
                COUNT(CASE WHEN f.hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(f.hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS retrasos,
                ROUND(COUNT(CASE WHEN f.hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(f.hora_entrada) BETWEEN 5 AND 12 THEN 1 END)*100.0/NULLIF(COUNT(f.id),0),1) AS pct_retraso
             FROM empleados e
             LEFT JOIN fichajes f ON f.id_empleado=e.id AND f.fecha BETWEEN :d AND :h
             WHERE e.activo=1 GROUP BY e.id ORDER BY retrasos DESC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHoraEntradaMedia(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT e.nombre, e.apellidos,
                TIME_FORMAT(SEC_TO_TIME(AVG(TIME_TO_SEC(f.hora_entrada))),'%H:%i') AS hora_media_entrada,
                COUNT(CASE WHEN f.hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(f.hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS retrasos
             FROM empleados e
             INNER JOIN fichajes f ON f.id_empleado=e.id AND f.fecha BETWEEN :d AND :h
             WHERE e.activo=1 GROUP BY e.id ORDER BY hora_media_entrada ASC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Actividad ───────────────────────────────────────────────────
    public function getResumenActividad(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT COUNT(DISTINCT id_empleado) AS empleados_registrados,
                COUNT(DISTINCT fecha) AS dias_con_actividad,
                COUNT(*) AS total_fichajes,
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2) ELSE 0 END),0) AS total_horas
             FROM fichajes WHERE fecha BETWEEN :d AND :h"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getActividadDiaria(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT fecha, COUNT(DISTINCT id_empleado) AS empleados_activos,
                COUNT(*) AS total_fichajes,
                COALESCE(SUM(CASE WHEN hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,hora_entrada,hora_salida)/60.0,2) ELSE 0 END),0) AS horas_totales
             FROM fichajes WHERE fecha BETWEEN :d AND :h GROUP BY fecha ORDER BY fecha ASC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActividadPorHora(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT HOUR(hora_entrada) AS hora, COUNT(*) AS entradas
             FROM fichajes WHERE fecha BETWEEN :d AND :h AND hora_entrada IS NOT NULL
             GROUP BY HOUR(hora_entrada) ORDER BY hora ASC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAusenciasYVacaciones(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT
                (SELECT COUNT(*) FROM ausencias WHERE fecha_inicio BETWEEN :d AND :h) AS total_ausencias,
                (SELECT COUNT(*) FROM vacaciones WHERE fecha_inicio BETWEEN :d2 AND :h2 AND estado='aprobada') AS vacaciones_aprobadas,
                (SELECT COUNT(*) FROM vacaciones WHERE fecha_inicio BETWEEN :d3 AND :h3 AND estado='pendiente') AS vacaciones_pendientes"
        );
        $stmt->execute([':d'=>$desde,':h'=>$hasta,':d2'=>$desde,':h2'=>$hasta,':d3'=>$desde,':h3'=>$hasta]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getPresenciaPorEmpleado(string $desde, string $hasta): array {
        $stmt = $this->db->prepare(
            "SELECT e.nombre, e.apellidos,
                COUNT(f.id) AS dias_presentes,
                COALESCE(SUM(CASE WHEN f.hora_salida IS NOT NULL THEN ROUND(TIMESTAMPDIFF(MINUTE,f.hora_entrada,f.hora_salida)/60.0,2) ELSE 0 END),0) AS horas_totales,
                COUNT(CASE WHEN f.hora_entrada>(SELECT ADDTIME(valor,SEC_TO_TIME((SELECT valor FROM ajustes WHERE clave='umbral_retraso_minutos')*60)) FROM ajustes WHERE clave='hora_inicio_jornada') AND HOUR(f.hora_entrada) BETWEEN 5 AND 12 THEN 1 END) AS retrasos
             FROM empleados e
             LEFT JOIN fichajes f ON f.id_empleado=e.id AND f.fecha BETWEEN :d AND :h
             WHERE e.activo=1 GROUP BY e.id ORDER BY dias_presentes DESC"
        );
        $stmt->execute([':d' => $desde, ':h' => $hasta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
