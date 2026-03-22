<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Family.php';
require_once __DIR__ . '/../controllers/family_controller.php';

handleAccountActions();

$user = User::findById((int)currentUserId());
$family = Family::getUserFamily((int)currentUserId());
$members = $family ? Family::getMembers((int)$family['id']) : [];
$children = $family ? Family::getChildren((int)$family['id']) : [];

$pageTitle = 'My Account';
include __DIR__ . '/../includes/header.php';
?>
<section class="container section">
    <h1>My Account</h1>

    <div class="tabs">
        <a href="#profile">Profile</a>
        <a href="#family">Family Account</a>
        <a href="#children">Children Profiles</a>
        <a href="<?= e(siteUrl('logout.php')) ?>">Logout</a>
    </div>

    <div id="profile" class="card section-card">
        <h2>Profile</h2>
        <form method="POST" class="form-grid">
            <?= csrfField() ?>
            <input type="hidden" name="account_action" value="update_profile">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?= e($user['full_name'] ?? '') ?>" required>

            <label>Email</label>
            <input type="email" value="<?= e($user['email'] ?? '') ?>" readonly>

            <button type="submit" class="btn btn-primary">Save Profile</button>
        </form>
    </div>

    <div id="family" class="card section-card">
        <h2>Family Account</h2>
        <?php if (!$family): ?>
            <form method="POST" class="form-grid">
                <?= csrfField() ?>
                <input type="hidden" name="account_action" value="create_family">
                <label>Family Name</label>
                <input type="text" name="family_name" placeholder="e.g. The Njogu Family" required>
                <button type="submit" class="btn btn-accent">Create Family</button>
            </form>
        <?php else: ?>
            <p><strong>Family Name:</strong> <?= e($family['family_name']) ?></p>
            <h3>Members</h3>
            <?php if (empty($members)): ?>
                <p>No members yet.</p>
            <?php else: ?>
                <ul class="simple-list">
                    <?php foreach ($members as $member): ?>
                        <li>
                            <?= e($member['full_name']) ?> (<?= e($member['email']) ?>)
                            <span class="pill"><?= e($member['member_role']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div id="children" class="card section-card">
        <h2>Children Profiles</h2>
        <?php if (!$family): ?>
            <p>Create a family account first, then add child profiles.</p>
        <?php else: ?>
            <form method="POST" class="form-grid">
                <?= csrfField() ?>
                <input type="hidden" name="account_action" value="add_child">
                <label>Child Name</label>
                <input type="text" name="child_name" required>
                <label>Date of Birth</label>
                <input type="date" name="dob" required max="<?= date('Y-m-d') ?>">
                <button type="submit" class="btn btn-primary">Add Child</button>
            </form>

            <?php if (empty($children)): ?>
                <p class="muted">No children added yet.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>DOB</th>
                            <th>Age</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($children as $child): ?>
                        <?php
                        $isActive = ((int)($_SESSION['active_child_id'] ?? 0) === (int)$child['id']);
                        $ageMonths = Family::childAgeMonths((string)$child['dob']);
                        ?>
                        <tr>
                            <td><?= e($child['child_name']) ?></td>
                            <td><?= e($child['dob']) ?></td>
                            <td><?= $ageMonths ?> months</td>
                            <td>
                                <form method="POST">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="account_action" value="set_active_child">
                                    <input type="hidden" name="child_id" value="<?= (int)$child['id'] ?>">
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        <?= $isActive ? 'Active' : 'Set Active' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="section">
        <a class="btn btn-danger" href="<?= e(siteUrl('logout.php')) ?>">Logout</a>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
