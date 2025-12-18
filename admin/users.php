<?php
/**
 * Wolvebite Community - Admin Users Management
 */
$pageTitle = 'Kelola Users';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete user
if (isset($_GET['delete'])) {
    $delete_id = (int) $_GET['delete'];

    // Don't allow deleting self
    if ($delete_id === $_SESSION['user_id']) {
        setFlash('error', 'Tidak dapat menghapus akun sendiri.');
    } else {
        if (mysqli_query($conn, "DELETE FROM users WHERE id = $delete_id")) {
            setFlash('success', 'User berhasil dihapus.');
        } else {
            setFlash('error', 'Gagal menghapus user.');
        }
    }
    header('Location: users.php');
    exit;
}

// Handle add/edit user
$editUser = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $edit_id");
    $editUser = mysqli_fetch_assoc($result);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int) ($_POST['id'] ?? 0);
    $username = escapeSQL($conn, $_POST['username']);
    $email = escapeSQL($conn, $_POST['email']);
    $phone = escapeSQL($conn, $_POST['phone'] ?? '');
    $role = escapeSQL($conn, $_POST['role']);
    $password = $_POST['password'] ?? '';

    if ($id > 0) {
        // Update user
        $query = "UPDATE users SET username = '$username', email = '$email', phone = '$phone', role = '$role'";
        if (!empty($password)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $query .= ", password = '$hashed'";
        }
        $query .= " WHERE id = $id";

        if (mysqli_query($conn, $query)) {
            setFlash('success', 'User berhasil diperbarui.');
        } else {
            setFlash('error', 'Gagal memperbarui user.');
        }
    } else {
        // Add new user
        if (empty($password)) {
            setFlash('error', 'Password wajib diisi untuk user baru.');
        } else {
            // Check email exists
            $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
            if (mysqli_num_rows($check) > 0) {
                setFlash('error', 'Email sudah terdaftar.');
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO users (username, email, phone, password, role) VALUES ('$username', '$email', '$phone', '$hashed', '$role')";

                if (mysqli_query($conn, $query)) {
                    setFlash('success', 'User berhasil ditambahkan.');
                } else {
                    setFlash('error', 'Gagal menambahkan user.');
                }
            }
        }
    }
    header('Location: users.php');
    exit;
}

// Get all users
$users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
$pendingBookings = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'"))['count'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Wolvebite Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <a href="../index.php" class="nav-logo">
                <span class="logo-icon">🐺</span>
                <span class="logo-text">Wolvebite</span>
            </a>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="users.php" class="admin-nav-link active"><i class="fas fa-users"></i> Kelola User</a>
                <a href="products.php" class="admin-nav-link"><i class="fas fa-box"></i> Kelola Produk</a>
                <a href="orders.php" class="admin-nav-link"><i class="fas fa-shopping-bag"></i> Pesanan</a>
                <a href="bookings.php" class="admin-nav-link">
                    <i class="fas fa-calendar-check"></i> Booking
                    <?php if ($pendingBookings > 0): ?><span class="badge badge-warning"
                            style="margin-left: auto;"><?php echo $pendingBookings; ?></span><?php endif; ?>
                </a>
                <a href="uploads.php" class="admin-nav-link"><i class="fas fa-file-upload"></i> Upload File</a>
                <div style="margin-top: auto; padding-top: var(--space-xl);">
                    <a href="../index.php" class="admin-nav-link"><i class="fas fa-home"></i> Ke Website</a>
                    <a href="../logout.php" class="admin-nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Kelola Users</h1>
                <button class="btn btn-primary" onclick="document.getElementById('userModal').classList.add('active')">
                    <i class="fas fa-plus"></i> Tambah User
                </button>
            </div>

            <?php displayFlash(); ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Role</th>
                                    <th>Tgl Daftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($user = mysqli_fetch_assoc($users)): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td><?php echo sanitize($user['username']); ?></td>
                                        <td><?php echo sanitize($user['email']); ?></td>
                                        <td><?php echo sanitize($user['phone'] ?? '-'); ?></td>
                                        <td>
                                            <span
                                                class="badge <?php echo $user['role'] === 'admin' ? 'badge-primary' : 'badge-info'; ?>">
                                                <?php echo ucfirst($user['role']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($user['created_at']); ?></td>
                                        <td>
                                            <a href="users.php?edit=<?php echo $user['id']; ?>"
                                                class="btn btn-sm btn-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                                <a href="users.php?delete=<?php echo $user['id']; ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Yakin ingin menghapus user ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- User Modal -->
    <div class="modal-overlay" id="userModal" <?php echo $editUser ? 'style="opacity:1;visibility:visible;"' : ''; ?>>
        <div class="modal">
            <div class="modal-header">
                <h3><?php echo $editUser ? 'Edit User' : 'Tambah User'; ?></h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $editUser['id'] ?? ''; ?>">

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="username" class="form-control"
                            value="<?php echo sanitize($editUser['username'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control"
                            value="<?php echo sanitize($editUser['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Telepon</label>
                        <input type="tel" name="phone" class="form-control"
                            value="<?php echo sanitize($editUser['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password
                            <?php echo $editUser ? '(kosongkan jika tidak diubah)' : ''; ?></label>
                        <input type="password" name="password" class="form-control" <?php echo $editUser ? '' : 'required'; ?>>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control" required>
                            <option value="member" <?php echo ($editUser['role'] ?? '') === 'member' ? 'selected' : ''; ?>>Member</option>
                            <option value="admin" <?php echo ($editUser['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>
                                Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function closeModal() {
            document.getElementById('userModal').classList.remove('active');
            window.location.href = 'users.php';
        }
    </script>
</body>

</html>