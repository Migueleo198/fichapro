<?php

class NotificacionesModel {

    private $db;

    public function __construct($db) { $this->db = $db; }

    public function getParaAdmin(): array {
        $items = [];

        $n = (int)$this->db->query("SELECT COUNT(*) FROM incidencias WHERE estado='pendiente'")->fetchColumn();
        if ($n > 0) {
            $items[] = ['tipo'=>'danger','icono'=>'bi-exclamation-triangle-fill',
                'mensaje'=>$n.' incidencia'.($n!==1?'s':'').' pendiente'.($n!==1?'s':''),
                'url'=>'/incidencias','count'=>$n];
        }

        $n = (int)$this->db->query("SELECT COUNT(*) FROM vacaciones WHERE estado='pendiente'")->fetchColumn();
        if ($n > 0) {
            $items[] = ['tipo'=>'warning','icono'=>'bi-airplane-fill',
                'mensaje'=>$n.' solicitud'.($n!==1?'es':'').' de vacaciones',
                'url'=>'/vacaciones','count'=>$n];
        }

        $n = (int)$this->db->query("SELECT COUNT(*) FROM ausencias WHERE estado='pendiente'")->fetchColumn();
        if ($n > 0) {
            $items[] = ['tipo'=>'warning','icono'=>'bi-person-dash-fill',
                'mensaje'=>$n.' ausencia'.($n!==1?'s':'').' pendiente'.($n!==1?'s':''),
                'url'=>'/ausencias','count'=>$n];
        }

        $n = (int)$this->db->query(
            "SELECT COUNT(*) FROM fichajes WHERE estado='abierto'
             AND (fecha < CURDATE() OR (fecha=CURDATE() AND hora_entrada < SUBTIME(CURTIME(),'12:00:00')))"
        )->fetchColumn();
        if ($n > 0) {
            $items[] = ['tipo'=>'danger','icono'=>'bi-clock-history',
                'mensaje'=>$n.' fichaje'.($n!==1?'s':'').' sin cerrar (+12 h)',
                'url'=>'/fichajes','count'=>$n];
        }

        return $items;
    }

    public function getParaTrabajador(int $empId): array {
        $items = [];

        $stmt = $this->db->prepare("SELECT id FROM fichajes WHERE id_empleado=? AND estado='abierto' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$empId]);
        if ($stmt->fetch()) {
            $items[] = ['tipo'=>'primary','icono'=>'bi-clock-fill',
                'mensaje'=>'Tienes un fichaje activo abierto','url'=>'/fichar','count'=>1];
        }

        $stmt = $this->db->prepare(
            "SELECT estado, COUNT(*) AS c FROM vacaciones
             WHERE id_empleado=? AND estado IN ('aprobada','rechazada')
               AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY estado"
        );
        $stmt->execute([$empId]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $n = (int)$row['c'];
            $items[] = [
                'tipo'   => $row['estado']==='aprobada'?'success':'danger',
                'icono'  => $row['estado']==='aprobada'?'bi-check-circle-fill':'bi-x-circle-fill',
                'mensaje'=> $n.' vacacion'.($n!==1?'es':'').' '.$row['estado'].($n!==1?'s':''),
                'url'    => '/vacaciones','count'=>$n,
            ];
        }

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM incidencias i
             JOIN fichajes f ON f.id=i.id_fichaje
             WHERE f.id_empleado=? AND i.estado='resuelta' AND i.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        $stmt->execute([$empId]);
        $n = (int)$stmt->fetchColumn();
        if ($n > 0) {
            $items[] = ['tipo'=>'success','icono'=>'bi-check-circle-fill',
                'mensaje'=>$n.' incidencia'.($n!==1?'s':'').' resuelta'.($n!==1?'s':''),
                'url'=>'/incidencias','count'=>$n];
        }

        return $items;
    }
}
