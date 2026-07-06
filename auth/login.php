<?php
require_once '../config/database.php';

if (isLoggedIn()) redirect('/index.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
    $stmt = $conn->prepare("SELECT id, username, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        redirect('/index.php');
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$page_title = 'Login';
include '../includes/header.php';
?>

<div class="container">
    <div class="form-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Login to continue your learning journey</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-input" placeholder="your@email.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%;">Login</button>
        </form>

        <div class="form-footer">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
