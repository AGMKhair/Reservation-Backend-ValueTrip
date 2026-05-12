<?php
// bookings.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle PUT via POST with _method=PUT
if ($method === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'PUT') {
    $method = 'PUT';
}

// Handle DELETE via POST with _method=DELETE
if ($method === 'POST' && isset($_POST['_method']) && strtoupper($_POST['_method']) === 'DELETE') {
    $method = 'DELETE';
}

$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : null);

switch ($method) {

    /*
    |--------------------------------------------------------------------------
    | GET BOOKINGS
    |--------------------------------------------------------------------------
    */
    case 'GET':

        // Single booking
        if ($id) {

            $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
            $stmt->execute([$id]);

            $booking = $stmt->fetch();

            if (!$booking) {
                sendJson("Booking not found", 404);
            }

            $stmtTickets = $pdo->prepare('SELECT * FROM tickets WHERE booking_id = ?');
            $stmtTickets->execute([$booking['id']]);

            $booking['tickets'] = $stmtTickets->fetchAll();

            sendJson($booking);
        }

        // All bookings
        $stmt = $pdo->query('SELECT * FROM bookings ORDER BY id DESC');
        $bookings = $stmt->fetchAll();

        foreach ($bookings as &$booking) {

            $stmtTickets = $pdo->prepare('SELECT * FROM tickets WHERE booking_id = ?');
            $stmtTickets->execute([$booking['id']]);

            $booking['tickets'] = $stmtTickets->fetchAll();
        }

        sendJson($bookings);

        break;

    /*
    |--------------------------------------------------------------------------
    | CREATE BOOKING
    |--------------------------------------------------------------------------
    */
    case 'POST':

        $input = json_decode(file_get_contents('php://input'), true);

        // fallback for form-data
        if (!$input) {
            $input = $_POST;
        }

        $passengerName = $input['passengerName'] ?? null;
        $passengerType = $input['passengerType'] ?? null;
        $itineraryReference = $input['itineraryReference'] ?? null;
        $bookingType = $input['bookingType'] ?? null;

        // fallback like spring boot logic
        $flightType = $input['flightType'] ?? $bookingType ?? null;

        $isSynced = isset($input['isSynced'])
            ? (int)$input['isSynced']
            : 0;

        $departure01 = $input['departure01'] ?? null;
        $landing01 = $input['landing01'] ?? null;

        $departure02 = $input['departure02'] ?? null;
        $landing02 = $input['landing02'] ?? null;

        $flightId = $input['flightId'] ?? null;

        $createdAt = date('Y-m-d H:i:s');

        $sql = '
            INSERT INTO bookings (
                passenger_name,
                passenger_type,
                itinerary_reference,
                booking_type,
                flight_type,
                is_synced,
                departure_01,
                landing_01,
                departure_02,
                landing_02,
                flight_id,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ';

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            $passengerName,
            $passengerType,
            $itineraryReference,
            $bookingType,
            $flightType,
            $isSynced,
            $departure01,
            $landing01,
            $departure02,
            $landing02,
            $flightId,
            $createdAt
        ]);

        $newId = $pdo->lastInsertId();

        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$newId]);

        $booking = $stmt->fetch();

        sendJson([
            "message" => "Booking created successfully",
            "data" => $booking
        ]);

        break;

    /*
    |--------------------------------------------------------------------------
    | UPDATE BOOKING
    |--------------------------------------------------------------------------
    */
    case 'PUT':

        if (!$id) {
            sendJson("Booking ID is required", 400);
        }

        // check booking exists
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$id]);

        $booking = $stmt->fetch();

        if (!$booking) {
            sendJson("Booking not found", 404);
        }

        $input = $_POST;

        // support raw json put
        if (empty($input)) {
            parse_str(file_get_contents("php://input"), $input);
        }

        $passengerName = $input['passengerName'] ?? $booking['passenger_name'];
        $passengerType = $input['passengerType'] ?? $booking['passenger_type'];
        $itineraryReference = $input['itineraryReference'] ?? $booking['itinerary_reference'];
        $bookingType = $input['bookingType'] ?? $booking['booking_type'];

        $flightType = $input['flightType']
            ?? $bookingType
            ?? $booking['flight_type'];

        $isSynced = isset($input['isSynced'])
            ? (int)$input['isSynced']
            : $booking['is_synced'];

        $departure01 = $input['departure01'] ?? $booking['departure_01'];
        $landing01 = $input['landing01'] ?? $booking['landing_01'];

        $departure02 = $input['departure02'] ?? $booking['departure_02'];
        $landing02 = $input['landing02'] ?? $booking['landing_02'];

        $flightId = $input['flightId'] ?? $booking['flight_id'];

        $stmt = $pdo->prepare('
            UPDATE bookings
            SET
                passenger_name = ?,
                passenger_type = ?,
                itinerary_reference = ?,
                booking_type = ?,
                flight_type = ?,
                is_synced = ?,
                departure_01 = ?,
                landing_01 = ?,
                departure_02 = ?,
                landing_02 = ?,
                flight_id = ?
            WHERE id = ?
        ');

        $stmt->execute([
            $passengerName,
            $passengerType,
            $itineraryReference,
            $bookingType,
            $flightType,
            $isSynced,
            $departure01,
            $landing01,
            $departure02,
            $landing02,
            $flightId,
            $id
        ]);

        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$id]);

        sendJson([
            "message" => "Booking updated successfully",
            "data" => $stmt->fetch()
        ]);

        break;

    /*
    |--------------------------------------------------------------------------
    | DELETE BOOKING
    |--------------------------------------------------------------------------
    */
    case 'DELETE':

        if (!$id) {
            sendJson("Booking ID is required", 400);
        }

        // check exists
        $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
        $stmt->execute([$id]);

        $booking = $stmt->fetch();

        if (!$booking) {
            sendJson("Booking not found", 404);
        }

        // delete tickets first
        $stmt = $pdo->prepare('DELETE FROM tickets WHERE booking_id = ?');
        $stmt->execute([$id]);

        // delete booking
        $stmt = $pdo->prepare('DELETE FROM bookings WHERE id = ?');
        $stmt->execute([$id]);

        sendJson([
            "message" => "Booking deleted successfully"
        ]);

        break;

    default:
        sendJson("Method not allowed", 405);
        break;
}
?>