<?php

class EmpleadoModel {

    private $db;
    public function __construct($db) { $this->db = $db; }

    public function login(string $usuario, string $password): array|false {
        $stmt = $this->db->prepare("SELECT * FROM empleados WHERE usuario = ? AND activo = 1 LIMIT 1");
        $stmt->execute([$usuario]);
        $emp = $stmt->fetch();
        if ($emp && password_verify($password, $emp['password'])) return $emp;
        return false;
    }

    public function getAll(bool $soloActivos = false): array {
        $sql = "SELECT id, nombre, apellidos, usuario, dni, telefono, email, fecha_nacimiento, rol, activo, created_at
                FROM empleados";
        if ($soloActivos) $sql .= " WHERE activo = 1";
        $sql .= " ORDER BY activo DESC, nombre ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM empleados WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByEmail(string $email): array|false {
        $stmt = $this->db->prepare("SELECT * FROM empleados WHERE email = ? AND activo = 1 LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function crear(array $d): int {
        $stmt = $this->db->prepare(
            "INSERT INTO empleados (nombre, apellidos, usuario, password, dni, telefono, email, fecha_nacimiento, rol, activo)
             VALUES (?,?,?,?,?,?,?,?,?,1)"
        );
        $stmt->execute([
            $d['nombre'], $d['apellidos'] ?? '', $d['usuario'],
            password_hash($d['password'], PASSWORD_DEFAULT),
            $d['dni'], $d['telefono'] ?? null, $d['email'],
            $d['fecha_nacimiento'] ?: null,
            in_array($d['rol'] ?? '', ['admin','trabajador']) ? $d['rol'] : 'trabajador',
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool {
        $stmt = $this->db->prepare(
            "UPDATE empleados SET nombre=?, apellidos=?, usuario=?, dni=?, telefono=?, email=?,
                    fecha_nacimiento=?, rol=?, activo=? WHERE id=?"
        );
        return $stmt->execute([
            $d['nombre'], $d['apellidos'] ?? '', $d['usuario'], $d['dni'],
            $d['telefono'] ?? null, $d['email'], $d['fecha_nacimiento'] ?: null,
            in_array($d['rol'] ?? '', ['admin','trabajador']) ? $d['rol'] : 'trabajador',
            isset($d['activo']) ? (int)$d['activo'] : 1, $id,
        ]);
    }

    public function cambiarPassword(int $id, string $password): bool {
        return $this->db->prepare("UPDATE empleados SET password=? WHERE id=?")
                        ->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }

    /** Actualiza (o limpia con null) la ruta de la foto de perfil. */
    public function actualizarFoto(int $id, ?string $ruta): bool {
        return $this->db->prepare("UPDATE empleados SET foto=? WHERE id=?")->execute([$ruta, $id]);
    }

    /** Actualiza sólo los datos de perfil del propio usuario (sin rol ni activo). */
    public function actualizarPerfil(int $id, array $d): bool {
        $stmt = $this->db->prepare(
            "UPDATE empleados SET nombre=?, apellidos=?, email=?, telefono=?, fecha_nacimiento=? WHERE id=?"
        );
        return $stmt->execute([
            $d['nombre'], $d['apellidos'] ?? '', $d['email'],
            $d['telefono'] ?? null, $d['fecha_nacimiento'] ?: null, $id,
        ]);
    }

    /** Comprueba que la contraseña en claro coincide con la almacenada. */
    public function verificarPassword(int $id, string $password): bool {
        $stmt = $this->db->prepare("SELECT password FROM empleados WHERE id=?");
        $stmt->execute([$id]);
        $hash = $stmt->fetchColumn();
        return $hash && password_verify($password, $hash);
    }

    public function setActivo(int $id, int $activo): bool {
        return $this->db->prepare("UPDATE empleados SET activo=? WHERE id=?")->execute([$activo, $id]);
    }

    public function contar(): array {
        $row = $this->db->query("SELECT
            COUNT(*) AS total,
            SUM(activo=1) AS activos,
            SUM(rol='admin') AS admins
            FROM empleados")->fetch();
        return $row ?: ['total'=>0,'activos'=>0,'admins'=>0];
    }
}
