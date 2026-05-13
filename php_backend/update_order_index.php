<?php

require_once 'db.php';

header('Content-Type: application/json');

$table = $_GET['table'] ?? null;

// নিরাপত্তা: allowed tables only
$allowedTables = ['airlines', 'flights'];

if (!in_array($table, $allowedTables)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid table"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid data"
    ]);
    exit;
}

try {

    $caseQuery = "";
    $ids = [];

    foreach ($data as $item) {

        if (!isset($item['id'])) continue;

        $id = (int)$item['id'];
        $orderIndex = isset($item['order_index']) ? (int)$item['order_index'] : 0;

        $caseQuery .= " WHEN {$id} THEN {$orderIndex} ";
        $ids[] = $id;
    }

    if (empty($ids)) {
        echo json_encode([
            "success" => false,
            "message" => "No valid items"
        ]);
        exit;
    }

    $idsString = implode(',', $ids);

    $sql = "
        UPDATE {$table}
        SET order_index = CASE id
            {$caseQuery}
        END
        WHERE id IN ({$idsString})
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Order updated successfully",
        "table" => $table
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>