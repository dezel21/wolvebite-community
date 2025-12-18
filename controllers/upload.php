<?php
/**
 * Wolvebite Community - Upload Controller
 */
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'upload':
            handleUpload();
            break;
        case 'delete':
            handleDelete();
            break;
        default:
            setFlash('error', 'Aksi tidak valid.');
            header('Location: ../admin/uploads.php');
            exit;
    }
}

/**
 * Handle file upload
 */
function handleUpload()
{
    global $conn;

    $file = $_FILES['file'] ?? null;
    $description = escapeSQL($conn, $_POST['description'] ?? '');
    $category = escapeSQL($conn, $_POST['category'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        setFlash('error', 'Tidak ada file yang diupload atau terjadi kesalahan.');
        header('Location: ../admin/uploads.php');
        exit;
    }

    // Validate file
    $allowed_types = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];
    $max_size = 10 * 1024 * 1024; // 10MB

    $errors = validateUpload($file, $allowed_types, $max_size);

    if (!empty($errors)) {
        setFlash('error', implode(' ', $errors));
        header('Location: ../admin/uploads.php');
        exit;
    }

    // Generate unique filename
    $original_name = $file['name'];
    $filename = generateFilename($original_name);
    $file_type = $file['type'];
    $file_size = $file['size'];

    // Create uploads directory if not exists
    $upload_dir = __DIR__ . '/../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Move uploaded file
    $destination = $upload_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        // Save to database
        $query = "INSERT INTO uploads (uploaded_by, filename, original_name, file_type, file_size, description, category) 
                  VALUES ($user_id, '$filename', '$original_name', '$file_type', $file_size, '$description', '$category')";

        if (mysqli_query($conn, $query)) {
            setFlash('success', 'File berhasil diupload!');
        } else {
            // Delete file if database insert fails
            unlink($destination);
            setFlash('error', 'Gagal menyimpan data file.');
        }
    } else {
        setFlash('error', 'Gagal mengupload file.');
    }

    header('Location: ../admin/uploads.php');
    exit;
}

/**
 * Handle file deletion
 */
function handleDelete()
{
    global $conn;

    $file_id = (int) ($_POST['file_id'] ?? 0);

    if ($file_id <= 0) {
        setFlash('error', 'ID file tidak valid.');
        header('Location: ../admin/uploads.php');
        exit;
    }

    // Get file info
    $file = mysqli_query($conn, "SELECT * FROM uploads WHERE id = $file_id");

    if (mysqli_num_rows($file) === 0) {
        setFlash('error', 'File tidak ditemukan.');
        header('Location: ../admin/uploads.php');
        exit;
    }

    $fileData = mysqli_fetch_assoc($file);
    $filepath = __DIR__ . '/../uploads/' . $fileData['filename'];

    // Delete from database
    if (mysqli_query($conn, "DELETE FROM uploads WHERE id = $file_id")) {
        // Delete physical file
        if (file_exists($filepath)) {
            unlink($filepath);
        }
        setFlash('success', 'File berhasil dihapus!');
    } else {
        setFlash('error', 'Gagal menghapus file.');
    }

    header('Location: ../admin/uploads.php');
    exit;
}
?>