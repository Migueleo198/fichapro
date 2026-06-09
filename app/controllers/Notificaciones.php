<?php

class Notificaciones extends Controller {

    public function getNotificaciones() {
        header('Content-Type: application/json; charset=utf-8');

        if (!isLoggedIn()) {
            echo json_encode(['success'=>false,'total'=>0,'items'=>[]]);
            exit;
        }

        try {
            $modelo  = $this->model('NotificacionesModel');
            $esAdmin = isAdmin();
            $items   = $esAdmin
                ? $modelo->getParaAdmin()
                : $modelo->getParaTrabajador(currentEmpId());
            $total   = array_sum(array_column($items, 'count'));
            echo json_encode(['success'=>true,'total'=>$total,'items'=>$items]);
        } catch (Throwable $e) {
            error_log('Notificaciones error: '.$e->getMessage());
            echo json_encode(['success'=>false,'total'=>0,'items'=>[]]);
        }

        exit;
    }
}
