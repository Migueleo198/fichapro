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
