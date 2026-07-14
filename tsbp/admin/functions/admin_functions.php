<?php
/**
 * Admin functions for the Indigene Certificate Application System
 */

/**
 * Get dashboard statistics
 * 
 * @param mysqli $conn Database connection
 * @return array Statistics data
 */
function getDashboardStats($conn) {
    $stats = [
        'total_applications' => 0,
        'pending_applications' => 0,
        'under_review_applications' => 0,
        'approved_applications' => 0,
        'rejected_applications' => 0,
        'total_users' => 0,
        'total_certificates' => 0
    ];
    
    // Get application stats
    $query = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'under_review' THEN 1 ELSE 0 END) as under_review,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
              FROM applications";
    
    $result = $conn->query($query);
    if($result && $row = $result->fetch_assoc()) {
        $stats['total_applications'] = $row['total'];
        $stats['pending_applications'] = $row['pending'];
        $stats['under_review_applications'] = $row['under_review'];
        $stats['approved_applications'] = $row['approved'];
        $stats['rejected_applications'] = $row['rejected'];
    }
    
    // Get user count
    $query = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($query);
    if($result && $row = $result->fetch_assoc()) {
        $stats['total_users'] = $row['total'];
    }
    
    // Get certificate count
    $query = "SELECT COUNT(*) as total FROM certificates";
    $result = $conn->query($query);
    if($result && $row = $result->fetch_assoc()) {
        $stats['total_certificates'] = $row['total'];
    }
    
    return $stats;
}

/**
 * Get recent applications
 * 
 * @param mysqli $conn Database connection
 * @param int $limit Number of applications to retrieve
 * @return array Recent applications
 */
function getRecentApplications($conn, $limit = 5) {
    $applications = [];
    
    $query = "SELECT 
                a.*, 
                u.fullname, 
                u.email, 
                u.phone
              FROM applications a
              JOIN users u ON a.user_id = u.user_id
              ORDER BY a.application_date DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
    }
    
    return $applications;
}

/**
 * Get recent activity logs
 * 
 * @param mysqli $conn Database connection
 * @param int $limit Number of logs to retrieve
 * @return array Recent logs
 */
function getRecentActivityLogs($conn, $limit = 5) {
    $logs = [];
    
    $query = "SELECT 
                l.*,
                COALESCE(u.fullname, a.fullname, 'System') as actor_name
              FROM activity_logs l
              LEFT JOIN users u ON l.user_id = u.user_id
              LEFT JOIN admins a ON l.admin_id = a.admin_id
              ORDER BY l.timestamp DESC
              LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}

/**
 * Get applications with pagination and filtering
 * 
 * @param mysqli $conn Database connection
 * @param string $status Filter by status
 * @param string $search Search term
 * @param int $limit Items per page
 * @param int $offset Pagination offset
 * @return array Applications
 */
function getApplications($conn, $status = '', $search = '', $limit = 10, $offset = 0) {
    $applications = [];
    $params = [];
    $types = "";
    
    $query = "SELECT 
                a.*, 
                u.fullname, 
                u.email, 
                u.phone
              FROM applications a
              JOIN users u ON a.user_id = u.user_id
              WHERE 1=1";
    
    // Add status filter
    if(!empty($status)) {
        $query .= " AND a.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND (u.fullname LIKE ? OR a.application_number LIKE ? OR u.email LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $query .= " ORDER BY a.application_date DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
    }
    
    return $applications;
}

/**
 * Get total number of applications for pagination
 * 
 * @param mysqli $conn Database connection
 * @param string $status Filter by status
 * @param string $search Search term
 * @return int Total number of applications
 */
function getTotalApplications($conn, $status = '', $search = '') {
    $params = [];
    $types = "";
    
    $query = "SELECT COUNT(*) as total
              FROM applications a
              JOIN users u ON a.user_id = u.user_id
              WHERE 1=1";
    
    // Add status filter
    if(!empty($status)) {
        $query .= " AND a.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND (u.fullname LIKE ? OR a.application_number LIKE ? OR u.email LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    
    return 0;
}

/**
 * Get detailed application information
 * 
 * @param mysqli $conn Database connection
 * @param int $applicationId Application ID
 * @return array|false Application details or false if not found
 */
function getApplicationDetails($conn, $applicationId) {
    $query = "SELECT 
                a.*, 
                u.fullname, 
                u.email, 
                u.phone
              FROM applications a
              JOIN users u ON a.user_id = u.user_id
              WHERE a.application_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $applicationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get application documents
 * 
 * @param mysqli $conn Database connection
 * @param int $applicationId Application ID
 * @return array|false Document info or false if not found
 */
function getApplicationDocuments($conn, $applicationId) {
    // Join with applications table to get user_id
    $query = "SELECT d.*, a.user_id FROM documents d 
              JOIN applications a ON d.application_id = a.application_id 
              WHERE d.application_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $applicationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}
/**
 * Update application status
 * 
 * @param mysqli $conn Database connection
 * @param int $applicationId Application ID
 * @param string $status New status
 * @param string $notes Review notes
 * @param int $adminId Admin ID
 * @return bool True if successful, false otherwise
 */
function updateApplicationStatus($conn, $applicationId, $status, $notes, $adminId) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update application status
        $query = "UPDATE applications 
                  SET status = ?, 
                      review_notes = ?, 
                      reviewed_by = ?, 
                      reviewed_date = NOW() 
                  WHERE application_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssis", $status, $notes, $adminId, $applicationId);
        $stmt->execute();
        
        // Get application info for logging
        $application = getApplicationDetails($conn, $applicationId);
        
        // Log the activity
        $action = strtolower($status);
        $description = "Application #{$application['application_number']} {$action} by admin";
        logActivity($application['user_id'], $adminId, $action, $description);
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        return false;
    }
}

/**
 * Generate certificate for approved application
 * 
 * @param mysqli $conn Database connection
 * @param int $applicationId Application ID
 * @param int $adminId Admin ID
 * @return bool True if successful, false otherwise
 */
function generateCertificate($conn, $applicationId, $adminId) {
    // Get application details
    $application = getApplicationDetails($conn, $applicationId);
    
    if(!$application || $application['status'] !== 'approved') {
        return false;
    }
    
    // Generate unique certificate number
    $certificateNumber = 'CERT-' . date('Ym') . '-' . rand(1000, 9999);
    
    // Create certificate PDF file path
    $certificatePath = 'uploads/certificates/' . $certificateNumber . '.pdf';
    
    // Ensure directory exists
    if(!is_dir(dirname('../' . $certificatePath))) {
        mkdir(dirname('../' . $certificatePath), 0755, true);
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert certificate record
        $query = "INSERT INTO certificates (
                    application_id, 
                    certificate_number, 
                    issue_date, 
                    certificate_path, 
                    issued_by
                  ) VALUES (?, ?, NOW(), ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issi", $applicationId, $certificateNumber, $certificatePath, $adminId);
        $result = $stmt->execute();
        
        if(!$result) {
            throw new Exception("Failed to insert certificate record");
        }
        
        // Generate actual PDF certificate
        $success = generateCertificatePDF($application, $certificateNumber, '../' . $certificatePath);
        
        if(!$success) {
            throw new Exception("Failed to generate certificate PDF");
        }
        
        // Log the activity
        $description = "Certificate #{$certificateNumber} generated for application #{$application['application_number']}";
        logActivity($application['user_id'], $adminId, 'certificate', $description);
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Certificate generation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate certificate PDF with background image
 * 
 * @param array $application Application details
 * @param string $certificateNumber Certificate number
 * @param string $filePath File path to save PDF
 * @return bool True if successful, false otherwise
 */
function generateCertificatePDF($application, $certificateNumber, $filePath) {
    // Include custom PDF class
    require_once('fpdf/fpdf.php');
    
    try {
        // Create a custom PDF class that extends FPDF to handle header
        class CertificatePDF extends FPDF {
            // This ensures the template is only added once at the beginning
            public function Header() {
                // Intentionally left empty - we'll add the template manually
            }
        }
        
        // Create new PDF document with landscape orientation
        $pdf = new CertificatePDF('L', 'mm', 'A4');
        $pdf->AddPage();
        
        // Set document properties
        $pdf->SetTitle('Indigene Certificate');
        $pdf->SetAuthor('Bichi Local Government Authority');
        $pdf->SetCreator('Indigene Certificate Portal');
        
        // Add background image (the certificate template)
        $template_path = '../assets/images/certificate-template.jpg';
        if (file_exists($template_path)) {
            $pdf->Image($template_path, 0, 0, 297, 210); // A4 landscape size
        } else {
            error_log("Certificate template not found at: " . $template_path);
        } 
        
        // Set font for the certificate
        $pdf->SetFont('Times', '', 12);
        $pdf->SetTextColor(65, 105, 225); // Black color
        
        // Add certificate reference number (top left)
        $pdf->SetXY(40, 67);
        $pdf->Cell(50, 8, $certificateNumber, 0, 0, 'L');
        
        // Add issue date
      //  $pdf->SetXY(45, 59);
      //  $pdf->Cell(50, 8, date('d/m/Y'), 0, 0, 'L');
        
        // Add bearer's name
        $pdf->SetFont('Times', 'B', 12);
        $pdf->SetXY(90, 107);
        $pdf->Cell(150, 8, strtoupper($application['fullname']), 0, 0, 'L');
        
        // Add address
        $pdf->SetXY(140, 118);
        $pdf->Cell(100, 8, $application['address'], 0, 0, 'L');
        
        // Add place of origin
        $pdf->SetXY(230, 131);
        $pdf->Cell(80, 8, strtoupper($application['place_of_origin']), 0, 0, 'L');
        
        // Add LGA 
        $pdf->SetXY(100, 141);
        $pdf->Cell(50, 8, strtoupper($application['lga']), 0, 0, 'L');
        
        global $conn;
        if (!isset($conn)) {
            require_once('includes/config.php');
        }
        
        $documents = getApplicationDocuments($conn, $application['application_id']);
        
        // Debug: Log what documents are returned
        error_log("Documents returned: " . print_r($documents, true));
        
        if ($documents) {
            // Construct the correct path for the passport photo
            $user_id = $documents['user_id'];
            $app_id = $application['application_id'];
            $passport_filename = $documents['passport_photo'];
            
            // Construct the full path as it's used in the view page
            $passport_path = "../uploads/{$user_id}/{$app_id}/{$passport_filename}";
            
            error_log("Trying to load passport photo from: " . $passport_path);
            
            if (file_exists($passport_path)) {
                // Position passport photo in top right area
                $pdf->Image($passport_path, 238, 26, 30, 38);
                error_log("Successfully loaded passport photo");
            } else {
                error_log("Passport photo file not found at: " . $passport_path);
                
                // Try alternative direct filename approach as fallback
                $alternative_paths = [
                    "../uploads/{$user_id}/{$app_id}/passport_1745823005.png",
                    "../uploads/{$user_id}/{$app_id}/passport.jpg",
                    "../uploads/{$user_id}/{$app_id}/passport.png",
                    "../uploads/{$user_id}/{$app_id}/passport.jpeg"
                ];
                
                $loaded = false;
                foreach ($alternative_paths as $alt_path) {
                    if (file_exists($alt_path)) {
                        $pdf->Image($alt_path, 238, 23, 30, 38);
                        error_log("Successfully loaded passport photo from alternative path: " . $alt_path);
                        $loaded = true;
                        break;
                    }
                }
                
                if (!$loaded) {
                    error_log("Could not find passport photo in any expected location");
                }
            }
        }
        
        // Add official stamp/seal if available
        $seal_path = '../assets/images/official-seal.png';
        if (file_exists($seal_path)) {
            $pdf->Image($seal_path, 220, 160, 30, 30);
        }
        
        // Create directory if it doesn't exist
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Save PDF to file
        $pdf->Output($filePath, 'F');
        return true;
    } catch (Exception $e) {
        error_log("PDF Generation Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Regenerate certificate for an application
 * 
 * @param mysqli $conn Database connection
 * @param int $certificateId Certificate ID
 * @param int $adminId Admin ID
 * @return bool True if successful, false otherwise
 */
function regenerateCertificate($conn, $certificateId, $adminId) {
    // Get certificate details
    $query = "SELECT c.*, a.* FROM certificates c 
              JOIN applications a ON c.application_id = a.application_id
              WHERE c.certificate_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $certificateId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if(!$result || $result->num_rows == 0) {
        return false;
    }
    
    $certificate = $result->fetch_assoc();
    
    // Get application details
    $application = getApplicationDetails($conn, $certificate['application_id']);
    
    if(!$application) {
        return false;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update the certificate record
        $query = "UPDATE certificates SET 
                  issue_date = NOW(),
                  issued_by = ? 
                  WHERE certificate_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $adminId, $certificateId);
        $result = $stmt->execute();
        
        if(!$result) {
            throw new Exception("Failed to update certificate record");
        }
        
        // Regenerate actual PDF certificate
        $success = generateCertificatePDF(
            $application, 
            $certificate['certificate_number'],
            '../' . $certificate['certificate_path']
        );
        
        if(!$success) {
            throw new Exception("Failed to regenerate certificate PDF");
        }
        
        // Log the activity
        $description = "Certificate #{$certificate['certificate_number']} regenerated for application #{$application['application_number']}";
        logActivity($application['user_id'], $adminId, 'certificate', $description);
        
        // Commit transaction
        $conn->commit();
        return true;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Certificate regeneration failed: " . $e->getMessage());
        return false;
    }
}

function getCertificateByApplicationId($conn, $applicationId) {
    $query = "SELECT * FROM certificates WHERE application_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $applicationId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get admin name by ID
 * 
 * @param mysqli $conn Database connection
 * @param int $adminId Admin ID
 * @return string Admin name
 */
function getAdminName($conn, $adminId) {
    $query = "SELECT fullname FROM admins WHERE admin_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $row = $result->fetch_assoc()) {
        return $row['fullname'];
    }
    
    return 'Unknown';
}

/**
 * Get users with pagination and filtering
 * 
 * @param mysqli $conn Database connection
 * @param string $status Filter by status
 * @param string $search Search term
 * @param int $limit Items per page
 * @param int $offset Pagination offset
 * @return array Users
 */
function getUsers($conn, $status = '', $search = '', $limit = 10, $offset = 0) {
    $users = [];
    $params = [];
    $types = "";
    
    $query = "SELECT * FROM users WHERE 1=1";
    
    // Add status filter
    if(!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $query .= " ORDER BY registration_date DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

/**
 * Get total number of users for pagination
 * 
 * @param mysqli $conn Database connection
 * @param string $status Filter by status
 * @param string $search Search term
 * @return int Total number of users
 */
function getTotalUsers($conn, $status = '', $search = '') {
    $params = [];
    $types = "";
    
    $query = "SELECT COUNT(*) as total FROM users WHERE 1=1";
    
    // Add status filter
    if(!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    
    return 0;
}

/**
 * Get detailed user information
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return array|false User details or false if not found
 */
function getUserDetails($conn, $userId) {
    $query = "SELECT * FROM users WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Update user status
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param string $status New status
 * @return bool True if successful, false otherwise
 */
function updateUserStatus($conn, $userId, $status) {
    $query = "UPDATE users SET status = ? WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $userId);
    $result = $stmt->execute();
    
    if($result) {
        // Get user info
        $user = getUserDetails($conn, $userId);
        
        // Log the activity
        $description = "User {$user['fullname']} status changed to {$status}";
        logActivity($userId, $_SESSION['admin_id'], 'update', $description);
    }
    
    return $result;
}

/**
 * Get applications by user ID
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return array User applications
 */
function getUserApplications($conn, $userId) {
    $applications = [];
    
    $query = "SELECT * FROM applications WHERE user_id = ? ORDER BY application_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $applications[] = $row;
        }
    }
    
    return $applications;
}

/**
 * Get activity logs by user ID
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @param int $limit Number of logs to retrieve
 * @return array User activity logs
 */
function getUserActivityLogs($conn, $userId, $limit = 10) {
    $logs = [];
    
    $query = "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY timestamp DESC LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}

/**
 * Verify user email
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return bool True if successful, false otherwise
 */
function verifyUserEmail($conn, $userId) {
    $query = "UPDATE users SET is_email_verified = 1 WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $result = $stmt->execute();
    
    if($result) {
        // Get user info
        $user = getUserDetails($conn, $userId);
        
        // Log the activity
        $description = "User {$user['fullname']} email verified by admin";
        logActivity($userId, $_SESSION['admin_id'], 'verify', $description);
    }
    
    return $result;
}


/**
 * Get certificates with pagination and filtering
 * 
 * @param mysqli $conn Database connection
 * @param string $search Search term
 * @param int $limit Items per page
 * @param int $offset Pagination offset
 * @return array Certificates
 */
function getCertificates($conn, $search = '', $limit = 10, $offset = 0) {
    $certificates = [];
    $params = [];
    $types = "";
    
    $query = "SELECT 
                c.*,
                a.application_number,
                u.fullname,
                admin.fullname as admin_name
              FROM certificates c
              JOIN applications a ON c.application_id = a.application_id
              JOIN users u ON a.user_id = u.user_id
              JOIN admins admin ON c.issued_by = admin.admin_id
              WHERE 1=1";
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND (u.fullname LIKE ? OR c.certificate_number LIKE ? OR a.application_number LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $query .= " ORDER BY c.issue_date DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $certificates[] = $row;
        }
    }
    
    return $certificates;
}

/**
 * Get total number of certificates for pagination
 * 
 * @param mysqli $conn Database connection
 * @param string $search Search term
 * @return int Total number of certificates
 */
function getTotalCertificates($conn, $search = '') {
    $params = [];
    $types = "";
    
    $query = "SELECT COUNT(*) as total 
              FROM certificates c
              JOIN applications a ON c.application_id = a.application_id
              JOIN users u ON a.user_id = u.user_id
              WHERE 1=1";
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND (u.fullname LIKE ? OR c.certificate_number LIKE ? OR a.application_number LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    
    return 0;
}

/**
 * Get admins with pagination and filtering
 * 
 * @param mysqli $conn Database connection
 * @param string $status Filter by status
 * @param string $search Search term
 * @param int $limit Items per page
 * @param int $offset Pagination offset
 * @return array Admins
 */
function getAdmins($conn, $status = '', $search = '', $limit = 10, $offset = 0) {
    $admins = [];
    $params = [];
    $types = "";
    
    $query = "SELECT * FROM admins WHERE 1=1";
    
    // Add status filter
    if(!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
    }
    
    return $admins;
}

/**
 * Get total number of admins for pagination
 * 
 * @param mysqli $conn Database connection
 * @param string $status Filter by status
 * @param string $search Search term
 * @return int Total number of admins
 */
function getTotalAdmins($conn, $status = '', $search = '') {
    $params = [];
    $types = "";
    
    $query = "SELECT COUNT(*) as total FROM admins WHERE 1=1";
    
    // Add status filter
    if(!empty($status)) {
        $query .= " AND status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND (fullname LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $types .= "sss";
    }
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    
    return 0;
}

/**
 * Update admin status
 * 
 * @param mysqli $conn Database connection
 * @param int $adminId Admin ID
 * @param string $status New status
 * @return bool True if successful, false otherwise
 */
function updateAdminStatus($conn, $adminId, $status) {
    $query = "UPDATE admins SET status = ? WHERE admin_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $adminId);
    $result = $stmt->execute();
    
    if($result) {
        // Get admin info for logging
        $adminQuery = "SELECT fullname, email FROM admins WHERE admin_id = ?";
        $adminStmt = $conn->prepare($adminQuery);
        $adminStmt->bind_param("i", $adminId);
        $adminStmt->execute();
        $adminResult = $adminStmt->get_result();
        $adminInfo = $adminResult->fetch_assoc();
        
        // Log the activity
        $actionDescription = "Admin status updated for {$adminInfo['email']} to {$status}";
        logActivity(null, $_SESSION['admin_id'], 'update_admin', $actionDescription);
    }
    
    return $result;
}

/**
 * Get activity logs with pagination and filtering
 * 
 * @param mysqli $conn Database connection
 * @param string $action Filter by action type
 * @param int $admin Filter by admin ID
 * @param int $user Filter by user ID
 * @param string $date_from Filter from date
 * @param string $date_to Filter to date
 * @param string $search Search term in description
 * @param int $limit Items per page
 * @param int $offset Pagination offset
 * @return array Activity logs
 */
function getActivityLogs($conn, $action = '', $admin = 0, $user = 0, $date_from = '', $date_to = '', $search = '', $limit = 20, $offset = 0) {
    $logs = [];
    $params = [];
    $types = "";
    
    $query = "SELECT * FROM activity_logs WHERE 1=1";
    
    // Add action filter
    if(!empty($action)) {
        $query .= " AND action = ?";
        $params[] = $action;
        $types .= "s";
    }
    
    // Add admin filter
    if(!empty($admin)) {
        $query .= " AND admin_id = ?";
        $params[] = $admin;
        $types .= "i";
    }
    
    // Add user filter
    if(!empty($user)) {
        $query .= " AND user_id = ?";
        $params[] = $user;
        $types .= "i";
    }
    
    // Add date from filter
    if(!empty($date_from)) {
        $query .= " AND DATE(timestamp) >= ?";
        $params[] = $date_from;
        $types .= "s";
    }
    
    // Add date to filter
    if(!empty($date_to)) {
        $query .= " AND DATE(timestamp) <= ?";
        $params[] = $date_to;
        $types .= "s";
    }
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND description LIKE ?";
        $params[] = $search;
        $types .= "s";
    }
    
    $query .= " ORDER BY timestamp DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}

/**
 * Get total number of activity logs for pagination
 * 
 * @param mysqli $conn Database connection
 * @param string $action Filter by action type
 * @param int $admin Filter by admin ID
 * @param int $user Filter by user ID
 * @param string $date_from Filter from date
 * @param string $date_to Filter to date
 * @param string $search Search term in description
 * @return int Total number of logs matching filters
 */
function getTotalActivityLogs($conn, $action = '', $admin = 0, $user = 0, $date_from = '', $date_to = '', $search = '') {
    $params = [];
    $types = "";
    
    $query = "SELECT COUNT(*) as total FROM activity_logs WHERE 1=1";
    
    // Add action filter
    if(!empty($action)) {
        $query .= " AND action = ?";
        $params[] = $action;
        $types .= "s";
    }
    
    // Add admin filter
    if(!empty($admin)) {
        $query .= " AND admin_id = ?";
        $params[] = $admin;
        $types .= "i";
    }
    
    // Add user filter
    if(!empty($user)) {
        $query .= " AND user_id = ?";
        $params[] = $user;
        $types .= "i";
    }
    
    // Add date from filter
    if(!empty($date_from)) {
        $query .= " AND DATE(timestamp) >= ?";
        $params[] = $date_from;
        $types .= "s";
    }
    
    // Add date to filter
    if(!empty($date_to)) {
        $query .= " AND DATE(timestamp) <= ?";
        $params[] = $date_to;
        $types .= "s";
    }
    
    // Add search
    if(!empty($search)) {
        $search = "%$search%";
        $query .= " AND description LIKE ?";
        $params[] = $search;
        $types .= "s";
    }
    
    $stmt = $conn->prepare($query);
    
    if(!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $row = $result->fetch_assoc()) {
        return $row['total'];
    }
    
    return 0;
}

/**
 * Get list of common activity types for filtering
 * 
 * @param mysqli $conn Database connection
 * @return array List of activity types
 */
function getCommonActivityTypes($conn) {
    $types = [];
    
    $query = "SELECT DISTINCT action FROM activity_logs ORDER BY action ASC";
    $result = $conn->query($query);
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $types[] = $row['action'];
        }
    }
    
    return $types;
}

/**
 * Get all admins for filter dropdown
 * 
 * @param mysqli $conn Database connection
 * @return array List of admins
 */
function getAllAdmins($conn) {
    $admins = [];
    
    $query = "SELECT admin_id, fullname FROM admins WHERE status = 'active' ORDER BY fullname ASC";
    $result = $conn->query($query);
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $admins[] = $row;
        }
    }
    
    return $admins;
}

/**
 * Get user name by ID
 * 
 * @param mysqli $conn Database connection
 * @param int $userId User ID
 * @return string User name
 */
function getUserName($conn, $userId) {
    $query = "SELECT fullname FROM users WHERE user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $row = $result->fetch_assoc()) {
        return $row['fullname'];
    }
    
    return 'Unknown User';
}

/**
 * Get CSS class for activity type icon
 * 
 * @param string $action Action type
 * @return string CSS class
 */
function getActivityIconClass($action) {
    $classes = [
        'login' => 'info',
        'logout' => 'info',
        'registration' => 'success',
        'password_reset' => 'warning',
        'application_submit' => 'primary',
        'approved' => 'success',
        'rejected' => 'danger',
        'under_review' => 'warning',
        'certificate' => 'success',
        'update_profile' => 'info',
        'update_admin' => 'warning',
        'delete' => 'danger'
    ];
    
    return isset($classes[$action]) ? $classes[$action] : 'secondary';
}

/**
 * Log activity
 * 
 * @param int|null $userId User ID or null
 * @param int|null $adminId Admin ID or null
 * @param string $action Action type
 * @param string $description Description
 * @return bool True if successful, false otherwise
 */
function logActivity($userId, $adminId, $action, $description) {
    global $conn;
    
    // Get IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Get user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $query = "INSERT INTO activity_logs (
                user_id, 
                admin_id, 
                action, 
                description, 
                ip_address, 
                user_agent
              ) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissss", $userId, $adminId, $action, $description, $ipAddress, $userAgent);
    
    return $stmt->execute();
}



/**
 * Get admin details by ID
 * 
 * @param mysqli $conn Database connection
 * @param int $adminId Admin ID
 * @return array|false Admin info or false if not found
 */
function getAdminDetails($conn, $adminId) {
    $query = "SELECT * FROM admins WHERE admin_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get admin activity logs
 * 
 * @param mysqli $conn Database connection
 * @param int $adminId Admin ID
 * @param int $limit Number of logs to retrieve
 * @return array Admin activity logs
 */
function getAdminActivityLogs($conn, $adminId, $limit = 10) {
    $logs = [];
    
    $query = "SELECT * FROM activity_logs WHERE admin_id = ? ORDER BY timestamp DESC LIMIT ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $adminId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result) {
        while($row = $result->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    
    return $logs;
}

/**
 * Get activity icon class based on action
 * 
 * @param string $action Action name
 * @return string Icon class
 */


/**
 * Get activity icon based on action
 * 
 * @param string $action Action name
 * @return string Icon name
 */
