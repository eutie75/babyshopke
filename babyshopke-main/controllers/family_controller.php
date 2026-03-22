<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../models/Family.php';
require_once __DIR__ . '/../models/User.php';

function handleAccountActions(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        return;
    }

    if (!verifyCsrfToken() || !isLoggedIn()) {
        redirect('account.php');
    }

    $action = (string)($_POST['account_action'] ?? '');
    $userId = (int)currentUserId();

    switch ($action) {
        case 'update_profile':
            $fullName = trim((string)($_POST['full_name'] ?? ''));
            if ($fullName === '') {
                flash('error', 'Name cannot be empty.');
                break;
            }
            User::updateName($userId, $fullName);
            $_SESSION['user_name'] = $fullName;
            flash('success', 'Profile updated.');
            break;

        case 'create_family':
            $familyName = trim((string)($_POST['family_name'] ?? ''));
            if ($familyName === '') {
                flash('error', 'Family name is required.');
                break;
            }

            $existing = Family::getUserFamily($userId);
            if ($existing) {
                flash('error', 'You already belong to a family.');
                break;
            }

            Family::create($familyName, $userId);
            flash('success', 'Family account created.');
            break;

        case 'add_child':
            $family = Family::getUserFamily($userId);
            if (!$family) {
                flash('error', 'Create a family account first.');
                break;
            }

            $childName = trim((string)($_POST['child_name'] ?? ''));
            $dob = (string)($_POST['dob'] ?? '');

            if ($childName === '' || $dob === '') {
                flash('error', 'Child name and date of birth are required.');
                break;
            }

            $dobTime = strtotime($dob);
            if ($dobTime === false || $dobTime > time()) {
                flash('error', 'Date of birth is invalid.');
                break;
            }

            Family::addChild((int)$family['id'], $childName, $dob);
            flash('success', 'Child profile added.');
            break;

        case 'set_active_child':
            $childId = (int)($_POST['child_id'] ?? 0);
            $child = Family::getChildForUser($childId, $userId);

            if (!$child) {
                flash('error', 'Invalid child profile.');
                break;
            }

            $_SESSION['active_child_id'] = (int)$child['id'];
            $_SESSION['active_child_age_months'] = Family::childAgeMonths((string)$child['dob']);
            $_SESSION['active_child_name'] = (string)$child['child_name'];
            flash('success', $child['child_name'] . ' is now the active child profile.');
            break;
    }

    redirect('account.php');
}
