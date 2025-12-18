<?php
/**
 * Wolvebite Community - Login Page
 */
$pageTitle = 'Login';

// Redirect if already logged in
require_once 'includes/functions.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = escapeSQL($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email dan password wajib diisi.';
    } else {
        // Check user in database
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                setFlash('success', 'Selamat datang kembali, ' . $user['username'] . '!');

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin/index.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                $error = 'Password salah.';
            }
        } else {
            $error = 'Email tidak terdaftar.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div style="font-size: 3rem; margin-bottom: var(--space-md);">🐺</div>
            <h1>Selamat Datang</h1>
            <p>Login ke akun Wolvebite Anda</p>
        </div>

        <div class="auth-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form id="loginForm" method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="contoh@email.com"
                        value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div style="text-align: center; margin-top: var(--space-lg); color: var(--text-light);">
                <p>Demo Account:</p>
                <p><strong>Admin:</strong> admin@wolvebite.com / admin123</p>
            </div>
        </div>

        <div class="auth-footer">
            <p>Belum punya akun? <a href="register.php">Daftar sekarang</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>