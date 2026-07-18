<?php
// ONE-TIME DATABASE SETUP SCRIPT
// Visit this page once with ?key=YOUR_SECRET to create all tables.
// DELETE THIS FILE FROM YOUR REPO AFTERWARD — it is not safe to leave live.

$setupSecret = getenv('SETUP_SECRET') ?: 'change-me';

if (!isset($_GET['key']) || $_GET['key'] !== $setupSecret) {
    http_response_code(403);
    die('Forbidden. Provide the correct ?key= to run setup.');
}

require_once __DIR__ . '/includes/config.php';

$sql = file_get_contents(__DIR__ . '/tailor_clean.sql');

if ($sql === false) {
    die('Could not read tailor_clean.sql. Make sure it was uploaded alongside this file.');
}

echo "<pre>";
echo "Running schema import...\n\n";

if ($pdo->exec($sql) !== false || true) {
    // multi_query via PDO doesn't return meaningful count for multi-statement DDL,
    // so we run it via mysqli's multi_query instead for reliability.
}

// Use mysqli multi_query since the file has multiple statements separated by ;
if (mysqli_multi_query($conn, $sql)) {
    do {
        if ($result = mysqli_store_result($conn)) {
            mysqli_free_result($result);
        }
    } while (mysqli_more_results($conn) && mysqli_next_result($conn));

    if (mysqli_errno($conn)) {
        echo "Completed with a warning on one statement: " . mysqli_error($conn) . "\n";
    } else {
        echo "SUCCESS: All tables created and sample data inserted.\n";
    }
} else {
    echo "ERROR: " . mysqli_error($conn) . "\n";
}

echo "\nDone. Now DELETE setup_database.php and tailor_clean.sql from your repo and redeploy.\n";
echo "</pre>";
