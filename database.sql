-- ========================================
-- Wolvebite Community Database Schema
-- ========================================
-- Jalankan file ini di phpMyAdmin atau MySQL CLI

-- Buat database
CREATE DATABASE IF NOT EXISTS wolvebite_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE wolvebite_db;

-- ========================================
-- Tabel: users
-- ========================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'member') DEFAULT 'member',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================
-- Tabel: products
-- ========================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(12, 2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================
-- Tabel: cart_items
-- ========================================
CREATE TABLE cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart_item (user_id, product_id)
) ENGINE=InnoDB;

-- ========================================
-- Tabel: orders
-- ========================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(50) NOT NULL UNIQUE,
    total_amount DECIMAL(12, 2) NOT NULL,
    status ENUM('pending', 'paid', 'processing', 'shipped', 'completed', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_status ENUM('unpaid', 'paid', 'failed', 'refunded') DEFAULT 'unpaid',
    shipping_address TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================================
-- Tabel: order_items
-- ========================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(12, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================================
-- Tabel: bookings
-- ========================================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    court_name VARCHAR(50) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================================
-- Tabel: uploads
-- ========================================
CREATE TABLE uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uploaded_by INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    description TEXT,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========================================
-- Insert Default Admin User
-- Password: admin123 (hashed with password_hash)
-- ========================================
INSERT INTO users (username, email, password, phone, role) VALUES 
('Admin Wolvebite', 'admin@wolvebite.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '081234567890', 'admin');

-- ========================================
-- Insert Sample Products
-- ========================================
INSERT INTO products (name, description, price, stock, image, category) VALUES
('Bola Basket Spalding', 'Bola basket official size 7, cocok untuk latihan dan pertandingan. Material kulit sintetis berkualitas tinggi.', 450000, 25, 'spalding-ball.jpg', 'Bola'),
('Sepatu Basket Nike Air Jordan', 'Sepatu basket premium dengan teknologi Air cushioning untuk kenyamanan maksimal saat bermain.', 2500000, 10, 'nike-jordan.jpg', 'Sepatu'),
('Jersey Tim Wolvebite', 'Jersey resmi komunitas Wolvebite, bahan dry-fit yang nyaman dan menyerap keringat.', 175000, 50, 'wolvebite-jersey.jpg', 'Apparel'),
('Celana Basket Pro', 'Celana basket dengan bahan breathable, dilengkapi kantong samping.', 125000, 40, 'basketball-shorts.jpg', 'Apparel'),
('Tas Olahraga Wolvebite', 'Tas gym dengan kapasitas besar, kompartemen khusus sepatu, dan logo Wolvebite.', 285000, 20, 'gym-bag.jpg', 'Aksesoris'),
('Handuk Olahraga', 'Handuk microfiber quick-dry dengan logo Wolvebite.', 75000, 100, 'sport-towel.jpg', 'Aksesoris');

-- ========================================
-- Insert Sample Bookings
-- ========================================
INSERT INTO bookings (user_id, booking_date, start_time, end_time, court_name, status, notes) VALUES
(1, CURDATE() + INTERVAL 1 DAY, '09:00:00', '11:00:00', 'Court A', 'approved', 'Latihan tim inti'),
(1, CURDATE() + INTERVAL 3 DAY, '14:00:00', '16:00:00', 'Court B', 'pending', 'Latihan pemula');

-- ========================================
-- Insert Sample Uploads
-- ========================================
INSERT INTO uploads (uploaded_by, filename, original_name, file_type, file_size, description, category) VALUES
(1, 'jadwal-latihan-2024.pdf', 'Jadwal Latihan 2024.pdf', 'application/pdf', 245000, 'Jadwal latihan rutin komunitas Wolvebite untuk tahun 2024', 'Jadwal'),
(1, 'teknik-dasar-basket.pdf', 'Teknik Dasar Basket.pdf', 'application/pdf', 1520000, 'Panduan lengkap teknik dasar bermain basket untuk pemula', 'Materi');
