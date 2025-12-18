<?php
/**
 * Wolvebite Community - Authentication Controller
 * Handles login, register, and session management
 */
require_once __DIR__ . '/../includes/functions.php';

/**
 * Process login request
 */
function processLogin($email, $password)
{
    global $conn;

    $email = escapeSQL($conn, $email);

    // Get user by email
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Login berhasil!'
            ];
        }

        return [
            'success' => false,
            'message' => 'Password salah.'
        ];
    }

    return [
        'success' => false,
        'message' => 'Email tidak terdaftar.'
    ];
}

/**
 * Process registration request
 */
function processRegister($data)
{
    global $conn;

    $username = escapeSQL($conn, $data['username']);
    $email = escapeSQL($conn, $data['email']);
    $phone = escapeSQL($conn, $data['phone'] ?? '');
    $password = $data['password'];

    // Check if email exists
    $checkEmail = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($checkEmail) > 0) {
        return [
            'success' => false,
            'message' => 'Email sudah terdaftar.'
        ];
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user
    $query = "INSERT INTO users (username, email, phone, password, role) 
              VALUES ('$username', '$email', '$phone', '$hashed_password', 'member')";

    if (mysqli_query($conn, $query)) {
        return [
            'success' => true,
            'user_id' => mysqli_insert_id($conn),
            'message' => 'Registrasi berhasil!'
        ];
    }

    return [
        'success' => false,
        'message' => 'Gagal membuat akun. Silakan coba lagi.'
    ];
}

/**
 * Logout user
 */
function processLogout()
{
    session_unset();
    session_destroy();

    return [
        'success' => true,
        'message' => 'Logout berhasil!'
    ];
}

/**
 * Update user profile
 */
function updateProfile($user_id, $data)
{
    global $conn;

    $username = escapeSQL($conn, $data['username']);
    $phone = escapeSQL($conn, $data['phone'] ?? '');
    $address = escapeSQL($conn, $data['address'] ?? '');

    $query = "UPDATE users SET 
              username = '$username',
              phone = '$phone',
              address = '$address'
              WHERE id = $user_id";

    if (mysqli_query($conn, $query)) {
        // Update session
        $_SESSION['username'] = $data['username'];

        return [
            'success' => true,
            'message' => 'Profil berhasil diperbarui!'
        ];
    }

    return [
        'success' => false,
        'message' => 'Gagal memperbarui profil.'
    ];
}

/**
 * Change password
 */
function changePassword($user_id, $current_password, $new_password)
{
    global $conn;

    // Get current user
    $result = mysqli_query($conn, "SELECT password FROM users WHERE id = $user_id");
    $user = mysqli_fetch_assoc($result);

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        return [
            'success' => false,
            'message' => 'Password lama salah.'
        ];
    }

    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
    $query = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";

    if (mysqli_query($conn, $query)) {
        return [
            'success' => true,
            'message' => 'Password berhasil diubah!'
        ];
    }

    return [
        'success' => false,
        'message' => 'Gagal mengubah password.'
    ];
}

// Handle AJAX requests or form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'login':
            $result = processLogin($_POST['email'], $_POST['password']);
            if ($result['success']) {
                setFlash('success', $result['message']);
                header('Location: ../index.php');
            } else {
                setFlash('error', $result['message']);
                header('Location: ../login.php');
            }
            exit;

        case 'register':
            $result = processRegister($_POST);
            if ($result['success']) {
                setFlash('success', $result['message']);
                header('Location: ../login.php');
            } else {
                setFlash('error', $result['message']);
                header('Location: ../register.php');
            }
            exit;

        case 'update_profile':
            requireLogin();
            $result = updateProfile($_SESSION['user_id'], $_POST);
            setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: ../profile.php');
            exit;

        case 'change_password':
            requireLogin();
            $result = changePassword($_SESSION['user_id'], $_POST['current_password'], $_POST['new_password']);
            setFlash($result['success'] ? 'success' : 'error', $result['message']);
            header('Location: ../profile.php');
            exit;
    }
}
?>