<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Cart.php';

function handleRegisterSubmission(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!verifyCsrfToken()) {
        redirect('register.php');
    }

    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
        flash('error', 'All fields are required.');
        redirect('register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Please provide a valid email address.');
        redirect('register.php');
    }

    if (strlen($password) < 8) {
        flash('error', 'Password must be at least 8 characters.');
        redirect('register.php');
    }

    if ($password !== $confirmPassword) {
        flash('error', 'Passwords do not match.');
        redirect('register.php');
    }

    if (User::findByEmail($email)) {
        flash('error', 'An account with this email already exists.');
        redirect('register.php');
    }

    $userId = User::create($fullName, $email, $password);
    $newUser = User::findById($userId);

    if (!$newUser) {
        flash('error', 'Account creation failed. Please try again.');
        redirect('register.php');
    }

    setAuthSession($newUser);
    Cart::syncGuestCartToUser($userId);
    flash('success', 'Account created successfully.');
    redirect('account.php');
}

function handleLoginSubmission(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!verifyCsrfToken()) {
        redirect('login.php');
    }

    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        flash('error', 'Email and password are required.');
        redirect('login.php');
    }

    $user = User::findByEmail($email);
    if (!$user || !User::verifyPassword($password, (string)$user['password_hash'])) {
        flash('error', 'Invalid login credentials.');
        redirect('login.php');
    }

    setAuthSession($user);
    Cart::syncGuestCartToUser((int)$user['id']);
    flash('success', 'Welcome back, ' . $user['full_name'] . '.');

    if (($user['role'] ?? 'user') === 'admin') {
        redirect('admin/dashboard.php');
    }

    redirect('account.php');
}

function logoutCurrentUser(): void
{
    clearAuthSession();
    flash('success', 'You are now logged out.');
    redirect('login.php');
}
