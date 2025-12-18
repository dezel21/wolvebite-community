<?php
/**
 * Wolvebite Community - Cart Controller
 */
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'add':
            addToCart();
            break;
        case 'update':
            updateCartItem();
            break;
        case 'remove':
            removeCartItem();
            break;
        case 'clear':
            clearCart();
            break;
        default:
            setFlash('error', 'Aksi tidak valid.');
            header('Location: ../cart.php');
            exit;
    }
}

/**
 * Add item to cart
 */
function addToCart()
{
    global $conn, $user_id;

    $product_id = (int) ($_POST['product_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 1);

    if ($product_id <= 0 || $quantity <= 0) {
        setFlash('error', 'Data tidak valid.');
        header('Location: ../shop.php');
        exit;
    }

    // Check product exists and has stock
    $product = mysqli_query($conn, "SELECT * FROM products WHERE id = $product_id AND stock > 0");
    if (mysqli_num_rows($product) === 0) {
        setFlash('error', 'Produk tidak tersedia.');
        header('Location: ../shop.php');
        exit;
    }

    $productData = mysqli_fetch_assoc($product);

    // Check if quantity exceeds stock
    if ($quantity > $productData['stock']) {
        $quantity = $productData['stock'];
    }

    // Check if item already in cart
    $existing = mysqli_query($conn, "SELECT * FROM cart_items WHERE user_id = $user_id AND product_id = $product_id");

    if (mysqli_num_rows($existing) > 0) {
        // Update quantity
        $existingItem = mysqli_fetch_assoc($existing);
        $newQty = $existingItem['quantity'] + $quantity;

        // Don't exceed stock
        if ($newQty > $productData['stock']) {
            $newQty = $productData['stock'];
        }

        mysqli_query($conn, "UPDATE cart_items SET quantity = $newQty WHERE id = {$existingItem['id']}");
        setFlash('success', 'Jumlah produk diperbarui di keranjang.');
    } else {
        // Insert new item
        mysqli_query($conn, "INSERT INTO cart_items (user_id, product_id, quantity) VALUES ($user_id, $product_id, $quantity)");
        setFlash('success', 'Produk berhasil ditambahkan ke keranjang!');
    }

    // Redirect back
    $redirect = $_POST['redirect'] ?? '../shop.php';
    header("Location: $redirect");
    exit;
}

/**
 * Update cart item quantity
 */
function updateCartItem()
{
    global $conn, $user_id;

    $cart_id = (int) ($_POST['cart_id'] ?? 0);
    $quantity = (int) ($_POST['quantity'] ?? 1);

    if ($cart_id <= 0) {
        setFlash('error', 'Data tidak valid.');
        header('Location: ../cart.php');
        exit;
    }

    // Check item belongs to user
    $item = mysqli_query($conn, "SELECT ci.*, p.stock FROM cart_items ci JOIN products p ON ci.product_id = p.id WHERE ci.id = $cart_id AND ci.user_id = $user_id");
    if (mysqli_num_rows($item) === 0) {
        setFlash('error', 'Item tidak ditemukan.');
        header('Location: ../cart.php');
        exit;
    }

    $itemData = mysqli_fetch_assoc($item);

    // Validate quantity
    if ($quantity <= 0) {
        // Remove item if quantity is 0 or less
        mysqli_query($conn, "DELETE FROM cart_items WHERE id = $cart_id");
        setFlash('success', 'Item dihapus dari keranjang.');
    } else {
        // Don't exceed stock
        if ($quantity > $itemData['stock']) {
            $quantity = $itemData['stock'];
        }

        mysqli_query($conn, "UPDATE cart_items SET quantity = $quantity WHERE id = $cart_id");
        setFlash('success', 'Jumlah item diperbarui.');
    }

    header('Location: ../cart.php');
    exit;
}

/**
 * Remove item from cart
 */
function removeCartItem()
{
    global $conn, $user_id;

    $cart_id = (int) ($_POST['cart_id'] ?? 0);

    if ($cart_id <= 0) {
        setFlash('error', 'Data tidak valid.');
        header('Location: ../cart.php');
        exit;
    }

    // Check item belongs to user and delete
    $result = mysqli_query($conn, "DELETE FROM cart_items WHERE id = $cart_id AND user_id = $user_id");

    if (mysqli_affected_rows($conn) > 0) {
        setFlash('success', 'Item berhasil dihapus dari keranjang.');
    } else {
        setFlash('error', 'Gagal menghapus item.');
    }

    header('Location: ../cart.php');
    exit;
}

/**
 * Clear all items from cart
 */
function clearCart()
{
    global $conn, $user_id;

    mysqli_query($conn, "DELETE FROM cart_items WHERE user_id = $user_id");
    setFlash('success', 'Keranjang berhasil dikosongkan.');

    header('Location: ../cart.php');
    exit;
}
?>