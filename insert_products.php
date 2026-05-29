<?php
/**
 * insert_products.php
 * Receives a JSON array of products via AJAX POST and inserts them into MySQL using PDO.
 *
 * SETUP: Update the DB credentials and table name below.
 */

// ─── DATABASE CONFIGURATION ──────────────────────────────────────────────────
$host   = 'localhost';       // Your MySQL host
$dbname = 'your_database';   // Your database name
$user   = 'your_username';   // Your MySQL username
$pass   = 'your_password';   // Your MySQL password
$table  = 'products';        // Your table name
// ─────────────────────────────────────────────────────────────────────────────

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Read and decode the JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['products']) || !is_array($input['products'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or missing products array']);
    exit;
}

$products = $input['products'];

// Validate each product has the required fields
foreach ($products as $index => $product) {
    if (empty($product['product_name']) || !isset($product['price']) || !isset($product['quantity'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => "Product at index $index is missing required fields (product_name, price, quantity)"
        ]);
        exit;
    }
}

try {
    // Connect to MySQL via PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    // Prepare the INSERT statement (uses prepared statements for security)
    $stmt = $pdo->prepare(
        "INSERT INTO `$table` (product_name, price, quantity) VALUES (:product_name, :price, :quantity)"
    );

    // Use a transaction so all inserts succeed or none do
    $pdo->beginTransaction();

    $inserted = 0;
    foreach ($products as $product) {
        $stmt->execute([
            ':product_name' => $product['product_name'],
            ':price'        => (float) $product['price'],
            ':quantity'     => (int) $product['quantity'],
        ]);
        $inserted++;
    }

    $pdo->commit();

    echo json_encode([
        'success'  => true,
        'message'  => "$inserted product(s) inserted successfully",
        'inserted' => $inserted
    ]);

} catch (PDOException $e) {
    // Roll back on failure
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
