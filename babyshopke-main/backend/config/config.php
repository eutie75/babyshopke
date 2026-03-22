<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

define('SITE_NAME', 'Baby Shop KE');
define('SITE_URL', 'http://localhost/babyshopke/public');
define('CURRENCY', 'KSH');

define('COLOR_PRIMARY', '#2EC4B6');
define('COLOR_ACCENT', '#FF6B8A');
define('COLOR_BG', '#FFF7F2');
define('COLOR_TEXT', '#1F2933');

if (session_status() === PHP_SESSION_NONE) {
    $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function e(?string $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function siteUrl(string $path = ''): string
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function rootUrl(): string
{
    return rtrim((string)preg_replace('#/public/?$#', '', SITE_URL), '/');
}

function assetUrl(string $assetPath): string
{
    return rootUrl() . '/assets/' . ltrim($assetPath, '/');
}

function redirect(string $path): void
{
    $target = preg_match('/^https?:\/\//i', $path) ? $path : siteUrl($path);
    header('Location: ' . $target);
    exit;
}

function flash(string $type, string $message): void
{
    if (!isset($_SESSION['flashes']) || !is_array($_SESSION['flashes'])) {
        $_SESSION['flashes'] = [];
    }
    $_SESSION['flashes'][] = ['type' => $type, 'message' => $message];
}

function pullFlashes(): array
{
    $messages = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return is_array($messages) ? $messages : [];
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['user_role'] ?? 'user') === 'admin';
}

function currentUserId(): ?int
{
    if (!isLoggedIn()) {
        return null;
    }
    return (int)$_SESSION['user_id'];
}

function setAuthSession(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
}

function clearAuthSession(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', (bool)$params['secure'], (bool)$params['httponly']);
    }
    session_destroy();
    session_start();
}

function getAgeFilterMap(): array
{
    return [
        '0-3' => [0, 3],
        '3-6' => [3, 6],
        '6-12' => [6, 12],
        '12-18' => [12, 18],
        '24-48' => [24, 48],
    ];
}

function getActiveAgeRange(): ?array
{
    $age = trim((string)($_GET['age'] ?? ''));
    $map = getAgeFilterMap();
    if ($age !== '' && isset($map[$age])) {
        return $map[$age];
    }

    if (!empty($_SESSION['active_child_age_months'])) {
        $months = (int)$_SESSION['active_child_age_months'];
        return [$months, $months];
    }

    return null;
}

function sessionCartCount(): int
{
    $items = $_SESSION['guest_cart'] ?? [];
    if (!is_array($items)) {
        return 0;
    }

    $count = 0;
    foreach ($items as $qty) {
        $count += max(0, (int)$qty);
    }
    return $count;
}

function cartCount(): int
{
    if (!isLoggedIn()) {
        return sessionCartCount();
    }

    $db = getDB();
    $sql = 'SELECT COALESCE(SUM(ci.qty), 0) AS count
            FROM carts c
            LEFT JOIN cart_items ci ON ci.cart_id = c.id
            WHERE c.user_id = :user_id';
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => currentUserId()]);

    return (int)($stmt->fetchColumn() ?: 0);
}

function wishlistCount(): int
{
    if (!isLoggedIn()) {
        return 0;
    }

    $db = getDB();
    $sql = 'SELECT COUNT(wi.id)
            FROM wishlists w
            LEFT JOIN wishlist_items wi ON wi.wishlist_id = w.id
            WHERE w.user_id = :user_id';
    $stmt = $db->prepare($sql);
    $stmt->execute([':user_id' => currentUserId()]);

    return (int)($stmt->fetchColumn() ?: 0);
}
