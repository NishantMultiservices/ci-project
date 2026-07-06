<?php
require_once '../config/database.php';

if (!isLoggedIn()) redirect('/auth/login.php');
if (isAdmin()) redirect('/admin/index.php');

$user = getUser($conn, $_SESSION['user_id']);
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $education = trim($_POST['education'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, education = ?, bio = ? WHERE id = ?");
    $stmt->bind_param("sssi", $full_name, $education, $bio, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $success = 'Profile updated successfully!';
        $user = getUser($conn, $_SESSION['user_id']);
    } else {
        $error = 'Failed to update profile.';
    }
}

$page_title = 'Profile';
include '../includes/header.php';
?>

<section class="section" style="padding-top:40px;">
    <div class="container" style="max-width:800px;">
        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($user['full_name'] ?: $user['username'], 0, 2)); ?>
            </div>
            <h2><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
            <?php if ($user['education']): ?>
                <p style="color:var(--gray-500);margin-top:4px;"><?php echo htmlspecialchars($user['education']); ?></p>
            <?php endif; ?>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-details">
            <h3 style="margin-bottom:24px;">Account Information</h3>
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-input" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background:var(--gray-100);">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background:var(--gray-100);">
                </div>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" placeholder="Enter your full name">
                </div>
                <div class="form-group">
                    <label class="form-label">Education</label>
                    <input type="text" name="education" class="form-input" value="<?php echo htmlspecialchars($user['education'] ?? ''); ?>" placeholder="e.g., B.Tech Computer Science">
                </div>
                <div class="form-group">
                    <label class="form-label">Bio</label>
                    <textarea name="bio" class="form-textarea" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>

        <div class="profile-details" style="margin-top:24px;">
            <h3 style="margin-bottom:16px;">Account Details</h3>
            <div class="detail-row">
                <span class="label">Member Since</span>
                <span class="value"><?php echo date('d F Y', strtotime($user['created_at'])); ?></span>
            </div>
            <div class="detail-row">
                <span class="label">Last Updated</span>
                <span class="value"><?php echo timeAgo($user['updated_at']); ?></span>
            </div>
        </div>

        <?php
        $profile_stats = $conn->query("
            SELECT COUNT(*) as total, SUM(CASE WHEN status='passed' THEN 1 ELSE 0 END) as passed
            FROM exam_results WHERE user_id = {$_SESSION['user_id']}
        ")->fetch_assoc();
        ?>
        <?php if ($profile_stats['total'] > 0): ?>
            <div class="profile-details" style="margin-top:24px;">
                <h3 style="margin-bottom:16px;">Exam Statistics</h3>
                <div class="detail-row">
                    <span class="label">Exams Taken</span>
                    <span class="value"><?php echo $profile_stats['total']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Exams Passed</span>
                    <span class="value" style="color:var(--success);"><?php echo $profile_stats['passed']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Exams Failed</span>
                    <span class="value" style="color:var(--danger);"><?php echo $profile_stats['total'] - $profile_stats['passed']; ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
