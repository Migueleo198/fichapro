<?php

// ── URL / links ──────────────────────────────────────────────────────
function url(string $path = ''): string {
    return URL_BASE . '/' . ltrim($path, '/');
}

function redirect(string $path): void {
    header('Location: ' . url($path));
    exit;
}

// ── Auth ─────────────────────────────────────────────────────────────
function isLoggedIn(): bool {
    return isset($_SESSION['emp_id']);
}

function isAdmin(): bool {
    return ($_SESSION['emp_rol'] ?? '') === 'admin';
}

function requireAuth(): void {
    if (!isLoggedIn()) redirect('login');
}

function requireAdmin(): void {
    if (!isLoggedIn()) redirect('login');
    if (!isAdmin())    redirect('home');
}

function currentEmpId(): int {
    return (int)($_SESSION['emp_id'] ?? 0);
}

// ── Active nav helper ────────────────────────────────────────────────
function navActive(string $key): bool {
    $url = $_GET['url'] ?? '';
    $first = strtolower(explode('/', trim($url, '/'))[0] ?? '');
    return $first === strtolower($key);
}

// ── Formatting ───────────────────────────────────────────────────────
function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}

function fechaCorta(?string $fecha): string {
    if (!$fecha) return '—';
    $ts    = strtotime($fecha);
    $dias  = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
    $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return $dias[date('w', $ts)] . ' ' . date('d', $ts) . ' ' . $meses[(int)date('n', $ts)];
}

function fechaLarga(?string $fecha): string {
    return $fecha ? date('d/m/Y', strtotime($fecha)) : '—';
}

function hhmm(?string $hora): string {
    return $hora ? substr($hora, 0, 5) : '—';
}

/** Decimal hours (e.g. 7.5) -> "7h 30m" */
function horasLegibles($horas): string {
    if ($horas === null || $horas === '') return '—';
    $horas = (float)$horas;
    $h = (int)floor($horas);
    $m = (int)round(($horas - $h) * 60);
    if ($m === 60) { $h++; $m = 0; }
    return $m > 0 ? "{$h}h {$m}m" : "{$h}h";
}

/** Difference between two HH:MM:SS times as decimal hours. */
function difHoras(?string $inicio, ?string $fin): float {
    if (!$inicio || !$fin) return 0.0;
    $a = strtotime($inicio);
    $b = strtotime($fin);
    if ($b < $a) $b += 86400; // crossed midnight
    return round(($b - $a) / 3600, 2);
}

// ── Badges ───────────────────────────────────────────────────────────
function badge(string $text, string $bg, string $color): string {
    return "<span style='display:inline-block;padding:2px 9px;border-radius:99px;font-size:.72rem;font-weight:800;background:{$bg};color:{$color};'>" . e($text) . "</span>";
}

function estadoFichajeBadge(string $estado): string {
    return match ($estado) {
        'abierto'    => badge('En curso',   '#e0f2fe', '#0369a1'),
        'cerrado'    => badge('Cerrado',    '#f1f5f9', '#475569'),
        'incidencia' => badge('Incidencia', '#fff7ed', '#c2410c'),
        'validado'   => badge('Validado',   '#dcfce7', '#166534'),
        default      => badge($estado,      '#f1f5f9', '#475569'),
    };
}

function estadoSolicitudBadge(string $estado): string {
    return match ($estado) {
        'pendiente' => badge('Pendiente', '#fef9c3', '#854d0e'),
        'aprobada'  => badge('Aprobada',  '#dcfce7', '#166534'),
        'rechazada' => badge('Rechazada', '#fee2e2', '#b91c1c'),
        default     => badge($estado,     '#f1f5f9', '#475569'),
    };
}

function rolBadge(string $rol): string {
    return $rol === 'admin'
        ? badge('Administrador', '#1E3A8A', '#fff')
        : badge('Trabajador',    '#E0F9F8', '#1E3A8A');
}

// ── Avatar / foto de perfil ──────────────────────────────────────────
/** URL de la foto del usuario en sesión (o null si usa inicial). Carga 1 vez. */
function avatarUrl(): ?string {
    if (!isLoggedIn()) return null;
    if (!array_key_exists('emp_foto', $_SESSION)) {
        try {
            $db   = (new Database())->conectar();
            $stmt = $db->prepare("SELECT foto FROM empleados WHERE id = ?");
            $stmt->execute([currentEmpId()]);
            $_SESSION['emp_foto'] = $stmt->fetchColumn() ?: '';
        } catch (\Throwable $e) { $_SESSION['emp_foto'] = ''; }
    }
    $f = $_SESSION['emp_foto'] ?? '';
    return $f !== '' ? url($f) : null;
}

/** Devuelve el HTML del avatar: <img> con la foto o un círculo con la inicial. */
function avatarHtml(?string $fotoUrl, ?string $nombre, float $size, float $font, string $bg = '#1A649C'): string {
    $s = rtrim(rtrim(number_format($size, 3, '.', ''), '0'), '.');
    if ($fotoUrl) {
        return "<div style='width:{$s}rem;height:{$s}rem;border-radius:50%;overflow:hidden;flex-shrink:0;background:#fff;'>"
             . "<img src='" . e($fotoUrl) . "' alt='' style='width:100%;height:100%;object-fit:cover;display:block;'></div>";
    }
    $ini = strtoupper(mb_substr($nombre ?: 'U', 0, 1));
    $fs  = rtrim(rtrim(number_format($font, 3, '.', ''), '0'), '.');
    return "<div style='width:{$s}rem;height:{$s}rem;border-radius:50%;background:{$bg};display:flex;"
         . "align-items:center;justify-content:center;font-size:{$fs}rem;font-weight:700;color:#fff;flex-shrink:0;'>"
         . e($ini) . "</div>";
}

// ── Settings (cached) ────────────────────────────────────────────────
function ajuste(string $clave, $default = null) {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $db = (new Database())->conectar();
            foreach ($db->query("SELECT clave, valor FROM ajustes") as $r) {
                $cache[$r['clave']] = $r['valor'];
            }
        } catch (\Throwable $e) { /* table may not exist yet */ }
    }
    return $cache[$clave] ?? $default;
}
