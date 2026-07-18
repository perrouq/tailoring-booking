<?php
// Database credentials are now read from environment variables.
// Set these in Render's dashboard under your service's "Environment" tab.
$host     = trim(getenv('DB_HOST') ?: 'localhost');
$port     = trim(getenv('DB_PORT') ?: '3306');
$username = trim(getenv('DB_USER') ?: 'root');
$password = trim(getenv('DB_PASSWORD') ?: '');
$dbname   = trim(getenv('DB_NAME') ?: 'tailor');
$sslMode  = trim(getenv('SSL_MODE') ?: '');

// --- TEMPORARY DEBUG BLOCK: remove once the connection works ---
if (isset($_GET['debug'])) {
    echo "<pre>";
    echo "DB_HOST as read by PHP: [" . $host . "]\n";
    echo "DB_PORT as read by PHP: [" . $port . "]\n";
    echo "DB_USER as read by PHP: [" . $username . "]\n";
    echo "DB_NAME as read by PHP: [" . $dbname . "]\n";
    echo "SSL_MODE as read by PHP: [" . $sslMode . "]\n";
    echo "gethostbyname() result: [" . gethostbyname($host) . "]\n";
    echo "(if gethostbyname() result is the SAME string as the host, DNS lookup failed)\n";
    echo "</pre>";
    exit;
}
// --- END TEMPORARY DEBUG BLOCK ---

date_default_timezone_set('Africa/Lagos');

// Create connection (mysqli) - with SSL required by providers like Aiven
$conn = mysqli_init();
if ($sslMode === 'REQUIRED') {
    // No CA file supplied, so we verify encryption but not the CA chain.
    mysqli_ssl_set($conn, null, null, null, null, null);
    $conn->real_connect($host, $username, $password, $dbname, $port, null, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
} else {
    $conn->real_connect($host, $username, $password, $dbname, $port);
}
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// PDO connection (used alongside mysqli in this app)
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdoOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    if ($sslMode === 'REQUIRED') {
        $pdoOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }
    $pdo = new PDO($dsn, $username, $password, $pdoOptions);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>