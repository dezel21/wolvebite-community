-- =====================================================
-- Wolvebite Academy - Database Extension
-- Extends the wolvebite_db database with Academy tables
-- =====================================================

USE wolvebite_db;

-- =====================================================
-- Table: academy_coaches (Pelatih)
-- =====================================================
CREATE TABLE IF NOT EXISTS academy_coaches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    specialization VARCHAR(100),
    bio TEXT,
    photo VARCHAR(255),
    experience_years INT DEFAULT 0,
    certifications TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: academy_programs (Program Latihan)
-- =====================================================
CREATE TABLE IF NOT EXISTS academy_programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE,
    description TEXT,
    level ENUM('beginner', 'intermediate', 'advanced', 'elite') DEFAULT 'beginner',
    age_min INT DEFAULT 0,
    age_max INT DEFAULT 99,
    duration_weeks INT DEFAULT 12,
    sessions_per_week INT DEFAULT 2,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    max_participants INT DEFAULT 20,
    coach_id INT,
    image VARCHAR(255),
    status ENUM('active', 'inactive', 'full') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (coach_id) REFERENCES academy_coaches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: academy_schedule (Jadwal Latihan)
-- =====================================================
CREATE TABLE IF NOT EXISTS academy_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    coach_id INT,
    day_of_week ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    location VARCHAR(100) DEFAULT 'Court A',
    max_capacity INT DEFAULT 20,
    notes TEXT,
    status ENUM('active', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES academy_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (coach_id) REFERENCES academy_coaches(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: academy_enrollments (Pendaftaran Program)
-- =====================================================
CREATE TABLE IF NOT EXISTS academy_enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    program_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    start_date DATE,
    end_date DATE,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    payment_amount DECIMAL(12,2) DEFAULT 0,
    payment_date DATETIME,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (program_id) REFERENCES academy_programs(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, program_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: academy_bookings (Booking Kelas per Sesi)
-- =====================================================
CREATE TABLE IF NOT EXISTS academy_bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    schedule_id INT NOT NULL,
    booking_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'attended', 'absent', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (schedule_id) REFERENCES academy_schedule(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking (user_id, schedule_id, booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Table: academy_modules (Modul Latihan)
-- =====================================================
CREATE TABLE IF NOT EXISTS academy_modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INT DEFAULT 0,
    module_order INT DEFAULT 0,
    is_public BOOLEAN DEFAULT FALSE,
    uploaded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES academy_programs(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================
-- Sample Data: Coaches
-- =====================================================
INSERT INTO academy_coaches (name, email, phone, specialization, bio, experience_years, status) VALUES
('Coach Andi Pratama', 'andi@wolvebite.com', '081234567891', 'Offensive Skills', 'Mantan pemain profesional dengan pengalaman 10 tahun melatih tim junior dan senior.', 10, 'active'),
('Coach Budi Santoso', 'budi@wolvebite.com', '081234567892', 'Defensive Tactics', 'Spesialis strategi pertahanan dan conditioning fisik.', 8, 'active'),
('Coach Citra Dewi', 'citra@wolvebite.com', '081234567893', 'Youth Development', 'Fokus pada pengembangan skill dasar untuk usia muda.', 5, 'active');

-- =====================================================
-- Sample Data: Programs
-- =====================================================
INSERT INTO academy_programs (name, slug, description, level, age_min, age_max, duration_weeks, sessions_per_week, price, max_participants, coach_id, status) VALUES
('Junior Wolves', 'junior-wolves', 'Program pelatihan dasar untuk anak usia 8-12 tahun. Fokus pada fundamental basketball: dribbling, passing, shooting, dan teamwork.', 'beginner', 8, 12, 12, 2, 1500000, 20, 3, 'active'),
('Teen Wolves', 'teen-wolves', 'Program untuk remaja usia 13-17 tahun. Pengembangan skill intermediate dan pengenalan strategi permainan.', 'intermediate', 13, 17, 12, 3, 2000000, 15, 1, 'active'),
('Elite Wolves', 'elite-wolves', 'Program intensif untuk pemain serius usia 16+. Latihan kompetitif, strategi advanced, dan persiapan turnamen.', 'advanced', 16, 30, 16, 4, 3500000, 12, 2, 'active'),
('Weekend Warriors', 'weekend-warriors', 'Program santai untuk dewasa yang ingin tetap aktif bermain basket di akhir pekan.', 'beginner', 18, 50, 8, 1, 1000000, 25, 1, 'active');

-- =====================================================
-- Sample Data: Schedule
-- =====================================================
INSERT INTO academy_schedule (program_id, coach_id, day_of_week, start_time, end_time, location, max_capacity) VALUES
(1, 3, 'tuesday', '15:00:00', '17:00:00', 'Court A', 20),
(1, 3, 'thursday', '15:00:00', '17:00:00', 'Court A', 20),
(2, 1, 'monday', '16:00:00', '18:00:00', 'Court B', 15),
(2, 1, 'wednesday', '16:00:00', '18:00:00', 'Court B', 15),
(2, 1, 'friday', '16:00:00', '18:00:00', 'Court B', 15),
(3, 2, 'monday', '18:00:00', '20:30:00', 'Court A', 12),
(3, 2, 'tuesday', '18:00:00', '20:30:00', 'Court A', 12),
(3, 2, 'thursday', '18:00:00', '20:30:00', 'Court A', 12),
(3, 2, 'saturday', '09:00:00', '12:00:00', 'Court A', 12),
(4, 1, 'saturday', '14:00:00', '16:00:00', 'Court B', 25);

-- =====================================================
-- Sample Data: Modules
-- =====================================================
INSERT INTO academy_modules (program_id, title, description, filename, original_name, file_type, is_public, uploaded_by) VALUES
(1, 'Panduan Dribbling Dasar', 'Modul teknik dribbling untuk pemula', 'module_dribbling_101.pdf', 'Dribbling 101.pdf', 'application/pdf', TRUE, 1),
(1, 'Teknik Passing Fundamental', 'Jenis-jenis passing dan cara melakukannya', 'module_passing_basics.pdf', 'Passing Basics.pdf', 'application/pdf', TRUE, 1),
(2, 'Strategi Pick and Roll', 'Panduan lengkap pick and roll offense', 'module_pick_roll.pdf', 'Pick and Roll Strategy.pdf', 'application/pdf', FALSE, 1),
(3, 'Advanced Defensive Schemes', 'Zona defense dan man-to-man advanced', 'module_defense_adv.pdf', 'Advanced Defense.pdf', 'application/pdf', FALSE, 1);
