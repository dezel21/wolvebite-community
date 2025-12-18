<?php
/**
 * Wolvebite Community - User Profile Page
 */
$pageTitle = 'Profil Saya';
require_once 'includes/header.php';
requireLogin();

$user = getCurrentUser();
$errors = [];
$success = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // Validation
        if (empty($username)) {
            $errors[] = 'Nama wajib diisi.';
        }

        if (empty($errors)) {
            $username_escaped = escapeSQL($conn, $username);
            $phone_escaped = escapeSQL($conn, $phone);
            $address_escaped = escapeSQL($conn, $address);
            $user_id = $_SESSION['user_id'];

            $query = "UPDATE users SET username = '$username_escaped', phone = '$phone_escaped', address = '$address_escaped' WHERE id = $user_id";

            if (mysqli_query($conn, $query)) {
                $_SESSION['username'] = $username;
                setFlash('success', 'Profil berhasil diperbarui!');
                header('Location: profile.php');
                exit;
            } else {
                $errors[] = 'Gagal memperbarui profil.';
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            $errors[] = 'Semua field password wajib diisi.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Password baru minimal 6 karakter.';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'Konfirmasi password tidak cocok.';
        } elseif (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Password lama salah.';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $user_id = $_SESSION['user_id'];

            if (mysqli_query($conn, "UPDATE users SET password = '$hashed_password' WHERE id = $user_id")) {
                setFlash('success', 'Password berhasil diubah!');
                header('Location: profile.php');
                exit;
            } else {
                $errors[] = 'Gagal mengubah password.';
            }
        }
    }
}
?>

<div class="container">
    <div class="section-header">
        <h1 class="section-title"><i class="fas fa-user-circle"></i> Profil Saya</h1>
        <p class="section-subtitle">Kelola informasi akun Anda</p>
    </div>

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

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: var(--space-xl);">
        <!-- Profile Info -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: var(--space-xl);">Informasi Profil</h3>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" class="form-control"
                            value="<?php echo sanitize($user['email']); ?>" readonly disabled>
                        <span class="form-hint">Email tidak dapat diubah</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">Nama Lengkap</label>
                        <input type="text" id="username" name="username" class="form-control"
                            value="<?php echo sanitize($user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                            value="<?php echo sanitize($user['phone'] ?? ''); ?>" placeholder="08xxxxxxxxxx">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address">Alamat</label>
                        <textarea id="address" name="address" class="form-control"
                            placeholder="Alamat lengkap"><?php echo sanitize($user['address'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card">
            <div class="card-body">
                <h3 style="margin-bottom: var(--space-xl);">Ubah Password</h3>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label" for="current_password">Password Lama</label>
                        <input type="password" id="current_password" name="current_password" class="form-control"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">Password Baru</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" minlength="6"
                            required>
                        <span class="form-hint">Minimal 6 karakter</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control"
                            required>
                    </div>

                    <button type="submit" name="change_password" class="btn btn-secondary">
                        <i class="fas fa-key"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Account Info -->
    <div class="card" style="margin-top: var(--space-xl);">
        <div class="card-body">
            <h3 style="margin-bottom: var(--space-lg);">Informasi Akun</h3>

            <div
                style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: var(--space-lg);">
                <div>
                    <p style="color: var(--text-light); font-size: var(--font-size-sm);">Role</p>
                    <span class="badge <?php echo $user['role'] === 'admin' ? 'badge-primary' : 'badge-info'; ?>">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                </div>
                <div>
                    <p style="color: var(--text-light); font-size: var(--font-size-sm);">Member Sejak</p>
                    <p style="font-weight: 500;"><?php echo formatDate($user['created_at']); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>