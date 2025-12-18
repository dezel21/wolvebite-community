<?php
/** Wolvebite Community - Registration Page */
$pageTitle = 'Daftar';

// Redirect if already logged in
require_once 'includes/functions.php';
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Process registration form
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize inputs
    $username = escapeSQL($conn, $_POST['username'] ?? '');
    $email = escapeSQL($conn, $_POST['email'] ?? '');
    $phone = escapeSQL($conn, $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($username)) {
        $errors[] = 'Nama lengkap wajib diisi.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Nama minimal 3 karakter.';
    }

    if (empty($email)) {
        $errors[] = 'Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    } else {
        // Check if email already exists
        $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($checkEmail) > 0) {
            $errors[] = 'Email sudah terdaftar. Silakan gunakan email lain.';
        }
    }

    if (!empty($phone)) {
        $phone_clean = preg_replace('/[\s-]/', '', $phone);
        if (!preg_match('/^(08|\+62|62)[0-9]{8,12}$/', $phone_clean)) {
            $errors[] = 'Format nomor telepon tidak valid.';
        }
    }

    if (empty($password)) {
        $errors[] = 'Password wajib diisi.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Konfirmasi password tidak cocok.';
    }

    // If no errors, create user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, email, phone, password, role) 
                  VALUES ('$username', '$email', '$phone', '$hashed_password', 'member')";

        if (mysqli_query($conn, $query)) {
            $success = true;
            setFlash('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div style="font-size: 3rem; margin-bottom: var(--space-md);">🐺</div>
            <h1>Daftar Akun</h1>
            <p>Bergabung dengan Wolvebite Community</p>
        </div>

        <div class="auth-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo $error; ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <form id="registerForm" method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="username">Nama Lengkap</label>
                    <input type="text" id="username" name="username" class="form-control"
                        placeholder="Masukkan nama lengkap"
                        value="<?php echo isset($_POST['username']) ? sanitize($_POST['username']) : ''; ?>"
                        minlength="3" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="contoh@email.com"
                        value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="phone">Nomor Telepon</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="08xxxxxxxxxx"
                        value="<?php echo isset($_POST['phone']) ? sanitize($_POST['phone']) : ''; ?>">
                    <span class="form-hint">Opsional</span>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control"
                        placeholder="Minimal 6 karakter" minlength="6" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                        placeholder="Ulangi password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
            </form>
        </div>

        <div class="auth-footer">
            <p>Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>