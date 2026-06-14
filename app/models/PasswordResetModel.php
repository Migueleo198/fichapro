<?php

/**
 * Tokens de recuperación de contraseña.
 * En la BD se guarda solo el hash del token (el original viaja en el enlace).
 */
class PasswordResetModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    /** Crea un token para el empleado y devuelve el token en claro (para el enlace). */
    public function crear(int $empId, int $minutos = 60): string {
        // Invalida los tokens anteriores no usados del mismo empleado
        $this->db->prepare("UPDATE password_resets SET usado = 1 WHERE id_empleado = ? AND usado = 0")
                 ->execute([$empId]);

        $token  = bin2hex(random_bytes(32));
        $hash   = hash('sha256', $token);
        $expira = date('Y-m-d H:i:s', time() + $minutos * 60);

        $this->db->prepare("INSERT INTO password_resets (id_empleado, token, expira) VALUES (?,?,?)")
                 ->execute([$empId, $hash, $expira]);
        return $token;
    }

    /** Devuelve el id del empleado si el token es válido (sin usar y sin caducar), o null. */
    public function empleadoPorToken(string $token): ?int {
        if ($token === '') return null;
        $stmt = $this->db->prepare(
            "SELECT id_empleado FROM password_resets
             WHERE token = ? AND usado = 0 AND expira >= NOW()
             ORDER BY id DESC LIMIT 1"
        );
        $stmt->execute([hash('sha256', $token)]);
        $id = $stmt->fetchColumn();
        return $id !== false ? (int)$id : null;
    }

    /** Marca el token como usado. */
    public function marcarUsado(string $token): void {
        $this->db->prepare("UPDATE password_resets SET usado = 1 WHERE token = ?")
                 ->execute([hash('sha256', $token)]);
    }
}
