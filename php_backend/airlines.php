<?php
// airlines.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle PUT via POST with _method=PUT or check if 'id' is passed in POST
if ($method === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
    $method = 'PUT';
}

$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);
$action = isset($_GET['action']) ? $_GET['action'] : null;

// Helper to sanitize folder name
function sanitizeFolderName($input) {
    // Remove accents
    $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
    $str = strtr( $input, $unwanted_array );
    $str = preg_replace('/[^a-zA-Z0-9]/', '_', $str);
    return strtolower($str);
}

$base_upload_dir = __DIR__ . "/uploads/airlines/";
$base_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/uploads/airlines/";

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
            $stmt->execute([$id]);
            $airline = $stmt->fetch();
            if ($airline) {
                $stmtFlight = $pdo->prepare('SELECT * FROM flights WHERE airline_id = ?');
                $stmtFlight->execute([$airline['id']]);
                $airline['flights'] = $stmtFlight->fetchAll();
                sendJson($airline);
            } else {
                sendJson("Airline not found", 404);
            }
        } else {
            $stmt = $pdo->query('SELECT * FROM airlines');
            $airlines = $stmt->fetchAll();
            foreach ($airlines as &$airline) {
                $stmtFlight = $pdo->prepare('SELECT * FROM flights WHERE airline_id = ?');
                $stmtFlight->execute([$airline['id']]);
                $airline['flights'] = $stmtFlight->fetchAll();
            }
            sendJson($airlines);
        }
        break;

    case 'POST':
        if ($action === 'upload') {
            // General file upload endpoint
            if (isset($_FILES['file'])) {
                $fileName = basename($_FILES['file']['name']);
                $targetFile = __DIR__ . '/uploads/' . $fileName;
                move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
                
                $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/uploads/" . $fileName;
                sendJson(["url" => $url]);
            }
            sendJson("No file provided", 400);
        }

        // Create Airline
        $name = $_POST['name'] ?? null;
        if (!$name) {
            sendJson("Airline name is required", 400);
        }

        $folderName = sanitizeFolderName($name);
        $airlineFolderPath = $base_upload_dir . $folderName;

        if (!is_dir($airlineFolderPath)) {
            mkdir($airlineFolderPath, 0777, true);
        }

        $logoUrl = null;
        $iconUrl = null;

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoFileName = "logo_" . uniqid() . "_" . basename($_FILES['logo']['name']);
            $logoPath = $airlineFolderPath . "/" . $logoFileName;
            move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath);
            $logoUrl = $base_url . $folderName . "/" . $logoFileName;
        }

        if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
            $iconFileName = "icon_" . uniqid() . "_" . basename($_FILES['icon']['name']);
            $iconPath = $airlineFolderPath . "/" . $iconFileName;
            move_uploaded_file($_FILES['icon']['tmp_name'], $iconPath);
            $iconUrl = $base_url . $folderName . "/" . $iconFileName;
        }

        $createdAt = date('Y-m-d H:i:s');
        $updatedAt = $createdAt;

        $stmt = $pdo->prepare('INSERT INTO airlines (name, logo_url, icon_url, created_at, updated_at) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$name, $logoUrl, $iconUrl, $createdAt, $updatedAt]);
        
        $newId = $pdo->lastInsertId();
        $stmt = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
        $stmt->execute([$newId]);
        sendJson($stmt->fetch());
        break;

    case 'PUT':
        if (!$id) {
            sendJson("Airline ID is required", 400);
        }

        $stmt = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
        $stmt->execute([$id]);
        $airline = $stmt->fetch();
        if (!$airline) {
            sendJson("Airline not found", 404);
        }

        $name = $_POST['name'] ?? $airline['name'];
        $logoUrl = $airline['logo_url'];
        $iconUrl = $airline['icon_url'];

        $folderName = sanitizeFolderName($name);
        $airlineFolderPath = $base_upload_dir . $folderName;

        if (!is_dir($airlineFolderPath)) {
            mkdir($airlineFolderPath, 0777, true);
        }

        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoFileName = "logo_" . uniqid() . "_" . basename($_FILES['logo']['name']);
            $logoPath = $airlineFolderPath . "/" . $logoFileName;
            move_uploaded_file($_FILES['logo']['tmp_name'], $logoPath);
            $logoUrl = $base_url . $folderName . "/" . $logoFileName;
        }

        if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
            $iconFileName = "icon_" . uniqid() . "_" . basename($_FILES['icon']['name']);
            $iconPath = $airlineFolderPath . "/" . $iconFileName;
            move_uploaded_file($_FILES['icon']['tmp_name'], $iconPath);
            $iconUrl = $base_url . $folderName . "/" . $iconFileName;
        }

        $updatedAt = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('UPDATE airlines SET name = ?, logo_url = ?, icon_url = ?, updated_at = ? WHERE id = ?');
        $stmt->execute([$name, $logoUrl, $iconUrl, $updatedAt, $id]);

        $stmt = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
        $stmt->execute([$id]);
        sendJson($stmt->fetch());
        break;

    case 'DELETE':
        if (!$id) {
            sendJson("Airline ID is required", 400);
        }
        $stmt = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            sendJson("Airline not found", 404);
        }

        $stmt = $pdo->prepare('DELETE FROM airlines WHERE id = ?');
        $stmt->execute([$id]);
        sendJson("Airline deleted successfully");
        break;

    default:
        sendJson("Method not allowed", 405);
        break;
}
?>
