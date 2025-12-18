<?php
/** * Wolvebite Academy - Functions 
* Extended helper functions for Academy features*/

// Include parent functions
require_once __DIR__ . '/../../includes/functions.php';

/**
 * Get all active programs
 */
function getPrograms($limit = null)
{
    global $conn;
    $sql = "SELECT p.*, c.name as coach_name 
            FROM academy_programs p 
            LEFT JOIN academy_coaches c ON p.coach_id = c.id 
            WHERE p.status = 'active' 
            ORDER BY p.created_at DESC";
    if ($limit) {
        $sql .= " LIMIT $limit";
    }
    return mysqli_query($conn, $sql);
}

/**
 * Get program by ID or slug
 */
function getProgram($identifier)
{
    global $conn;
    $identifier = escapeSQL($conn, $identifier);
    $sql = "SELECT p.*, c.name as coach_name, c.photo as coach_photo, c.specialization, c.bio as coach_bio
            FROM academy_programs p 
            LEFT JOIN academy_coaches c ON p.coach_id = c.id 
            WHERE p.id = '$identifier' OR p.slug = '$identifier'";
    $result = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($result);
}

/**
 * Get all active coaches
 */
function getCoaches()
{
    global $conn;
    return mysqli_query($conn, "SELECT * FROM academy_coaches WHERE status = 'active' ORDER BY name ASC");
}

/**
 * Get coach by ID
 */
function getCoach($id)
{
    global $conn;
    $id = (int) $id;
    $result = mysqli_query($conn, "SELECT * FROM academy_coaches WHERE id = $id");
    return mysqli_fetch_assoc($result);
}

/**
 * Get schedule for a program
 */
function getProgramSchedule($program_id)
{
    global $conn;
    $program_id = (int) $program_id;
    return mysqli_query($conn, "SELECT s.*, c.name as coach_name 
                                FROM academy_schedule s 
                                LEFT JOIN academy_coaches c ON s.coach_id = c.id 
                                WHERE s.program_id = $program_id AND s.status = 'active'
                                ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time");
}

/**
 * Get all schedules
 */
function getAllSchedules()
{
    global $conn;
    return mysqli_query($conn, "SELECT s.*, p.name as program_name, c.name as coach_name 
                                FROM academy_schedule s 
                                JOIN academy_programs p ON s.program_id = p.id
                                LEFT JOIN academy_coaches c ON s.coach_id = c.id 
                                WHERE s.status = 'active'
                                ORDER BY FIELD(s.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), s.start_time");
}

/**
 * Check if user is enrolled in a program
 */
function isEnrolled($user_id, $program_id)
{
    global $conn;
    $user_id = (int) $user_id;
    $program_id = (int) $program_id;
    $result = mysqli_query($conn, "SELECT id FROM academy_enrollments 
                                   WHERE user_id = $user_id AND program_id = $program_id 
                                   AND status IN ('pending', 'approved')");
    return mysqli_num_rows($result) > 0;
}

/**
 * Get user's enrollments
 */
function getUserEnrollments($user_id)
{
    global $conn;
    $user_id = (int) $user_id;
    return mysqli_query($conn, "SELECT e.*, p.name as program_name, p.image, p.price, c.name as coach_name
                                FROM academy_enrollments e
                                JOIN academy_programs p ON e.program_id = p.id
                                LEFT JOIN academy_coaches c ON p.coach_id = c.id
                                WHERE e.user_id = $user_id
                                ORDER BY e.created_at DESC");
}

/**
 * Get user's bookings
 */
function getUserAcademyBookings($user_id)
{
    global $conn;
    $user_id = (int) $user_id;
    return mysqli_query($conn, "SELECT b.*, s.day_of_week, s.start_time, s.end_time, s.location, 
                                       p.name as program_name, c.name as coach_name
                                FROM academy_bookings b
                                JOIN academy_schedule s ON b.schedule_id = s.id
                                JOIN academy_programs p ON s.program_id = p.id
                                LEFT JOIN academy_coaches c ON s.coach_id = c.id
                                WHERE b.user_id = $user_id
                                ORDER BY b.booking_date DESC");
}

/**
 * Get modules for a program
 */
function getProgramModules($program_id, $user_id = null)
{
    global $conn;
    $program_id = (int) $program_id;

    // Check if user is enrolled
    $enrolled = $user_id ? isEnrolled($user_id, $program_id) : false;

    $sql = "SELECT * FROM academy_modules WHERE program_id = $program_id";
    if (!$enrolled && !isAdmin()) {
        $sql .= " AND is_public = TRUE";
    }
    $sql .= " ORDER BY module_order ASC, created_at ASC";

    return mysqli_query($conn, $sql);
}

/**
 * Get enrollment count for a program
 */
function getEnrollmentCount($program_id)
{
    global $conn;
    $program_id = (int) $program_id;
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM academy_enrollments 
                                   WHERE program_id = $program_id AND status IN ('approved', 'pending')");
    $row = mysqli_fetch_assoc($result);
    return $row['count'];
}

/**
 * Format day of week to Indonesian
 */
function formatDay($day)
{
    $days = [
        'monday' => 'Senin',
        'tuesday' => 'Selasa',
        'wednesday' => 'Rabu',
        'thursday' => 'Kamis',
        'friday' => 'Jumat',
        'saturday' => 'Sabtu',
        'sunday' => 'Minggu'
    ];
    return $days[$day] ?? $day;
}

/**
 * Format level to Indonesian
 */
function formatLevel($level)
{
    $levels = [
        'beginner' => 'Pemula',
        'intermediate' => 'Menengah',
        'advanced' => 'Lanjutan',
        'elite' => 'Elite'
    ];
    return $levels[$level] ?? $level;
}

/**
 * Get level badge class
 */
function getLevelBadge($level)
{
    $badges = [
        'beginner' => 'badge-success',
        'intermediate' => 'badge-info',
        'advanced' => 'badge-warning',
        'elite' => 'badge-primary'
    ];
    return $badges[$level] ?? 'badge-secondary';
}
?>