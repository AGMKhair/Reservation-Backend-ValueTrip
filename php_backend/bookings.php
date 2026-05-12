<?php

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

// Handle PUT via POST
if (
    $method === 'POST' &&
    isset($_POST['_method']) &&
    strtoupper($_POST['_method']) === 'PUT'
) {
    $method = 'PUT';
}

// Handle DELETE via POST
if (
    $method === 'POST' &&
    isset($_POST['_method']) &&
    strtoupper($_POST['_method']) === 'DELETE'
) {
    $method = 'DELETE';
}

$id = isset($_GET['id'])
    ? intval($_GET['id'])
    : (isset($_POST['id']) ? intval($_POST['id']) : null);

switch ($method) {

    /*
    |--------------------------------------------------------------------------
    | GET BOOKINGS
    |--------------------------------------------------------------------------
    */
    case 'GET':

        // Single booking
        if ($id) {

            $stmt = $pdo->prepare(
                'SELECT * FROM bookings WHERE id = ?'
            );

            $stmt->execute([$id]);

            $booking = $stmt->fetch();

            if (!$booking) {
                sendJson([
                    "message" => "Booking not found"
                ], 404);
            }

            sendJson($booking);
        }

        // All bookings
        $stmt = $pdo->query(
            'SELECT * FROM bookings ORDER BY id DESC'
        );

        $bookings = $stmt->fetchAll();

        sendJson($bookings);

        break;

    /*
    |--------------------------------------------------------------------------
    | CREATE BOOKING
    |--------------------------------------------------------------------------
    */
    case 'POST':

        $rawInput = file_get_contents("php://input");

        $input = json_decode($rawInput, true);

        // fallback form-data
        if (!$input) {
            $input = $_POST;
        }

        $passengerName = $input['passengerName']
            ?? $input['passenger_name']
            ?? null;

        $passengerType = $input['passengerType']
            ?? $input['passenger_type']
            ?? null;

        $itineraryReference = $input['itineraryReference']
            ?? $input['itinerary_reference']
            ?? null;

        $bookingType = $input['bookingType']
            ?? $input['booking_type']
            ?? null;

        $flightType = $input['flightType']
            ?? $input['flight_type']
            ?? $bookingType
            ?? null;

        $isSynced = isset($input['isSynced'])
            ? (int)$input['isSynced']
            : (
                isset($input['is_synced'])
                    ? (int)$input['is_synced']
                    : 0
            );

        $departure01 = $input['departure01']
            ?? $input['departure_01']
            ?? null;

        $landing01 = $input['landing01']
            ?? $input['landing_01']
            ?? null;

        $departure02 = $input['departure02']
            ?? $input['departure_02']
            ?? null;

        $landing02 = $input['landing02']
            ?? $input['landing_02']
            ?? null;

        $flightId = $input['flightId']
            ?? $input['flight_id']
            ?? null;

        // TICKET DATA
        $ticketNo = null;
        $issuedAt = null;

        if (
            isset($input['tickets']) &&
            is_array($input['tickets']) &&
            count($input['tickets']) > 0
        ) {

            $firstTicket = $input['tickets'][0];

            $ticketNo = $firstTicket['ticket_no']
                ?? $firstTicket['ticketNo']
                ?? null;

            $issuedAt = $firstTicket['issued_at']
                ?? $firstTicket['issuedAt']
                ?? null;
        }

        // direct field support
        $ticketNo = $input['ticket_no']
            ?? $input['ticketNo']
            ?? $ticketNo;

        $issuedAt = $input['issued_at']
            ?? $input['issuedAt']
            ?? $issuedAt;

        $createdAt = $input['created_at']
            ?? date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('
            INSERT INTO bookings (
                passenger_name,
                passenger_type,
                itinerary_reference,
                booking_type,
                flight_type,
                is_synced,
                departure01,
                landing01,
                departure02,
                landing02,
                flight_id,
                ticket_no,
                issued_at,
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $ticketNo,
            $issuedAt,
            $createdAt
        ]);

        $newId = $pdo->lastInsertId();

        $stmt = $pdo->prepare(
            'SELECT * FROM bookings WHERE id = ?'
        );

        $stmt->execute([$newId]);

        sendJson([
            "message" => "Booking created successfully",
            "data" => $stmt->fetch()
        ]);

        break;

    /*
    |--------------------------------------------------------------------------
    | UPDATE BOOKING
    |--------------------------------------------------------------------------
    */
    case 'PUT':

        if (!$id) {
            sendJson([
                "message" => "Booking ID is required"
            ], 400);
        }

        // Check booking exists
        $stmt = $pdo->prepare(
            'SELECT * FROM bookings WHERE id = ?'
        );

        $stmt->execute([$id]);

        $booking = $stmt->fetch();

        if (!$booking) {
            sendJson([
                "message" => "Booking not found"
            ], 404);
        }

        $rawInput = file_get_contents("php://input");

        $input = json_decode($rawInput, true);

        if (!$input) {
            parse_str($rawInput, $input);
        }

        if (!$input) {
            $input = $_POST;
        }

        $passengerName = $input['passengerName']
            ?? $input['passenger_name']
            ?? $booking['passenger_name'];

        $passengerType = $input['passengerType']
            ?? $input['passenger_type']
            ?? $booking['passenger_type'];

        $itineraryReference = $input['itineraryReference']
            ?? $input['itinerary_reference']
            ?? $booking['itinerary_reference'];

        $bookingType = $input['bookingType']
            ?? $input['booking_type']
            ?? $booking['booking_type'];

        $flightType = $input['flightType']
            ?? $input['flight_type']
            ?? $booking['flight_type'];

        $isSynced = isset($input['isSynced'])
            ? (int)$input['isSynced']
            : (
                isset($input['is_synced'])
                    ? (int)$input['is_synced']
                    : $booking['is_synced']
            );

        $departure01 = $input['departure01']
            ?? $input['departure_01']
            ?? $booking['departure01'];

        $landing01 = $input['landing01']
            ?? $input['landing_01']
            ?? $booking['landing01'];

        $departure02 = $input['departure02']
            ?? $input['departure_02']
            ?? $booking['departure02'];

        $landing02 = $input['landing02']
            ?? $input['landing_02']
            ?? $booking['landing02'];

        $flightId = $input['flightId']
            ?? $input['flight_id']
            ?? $booking['flight_id'];

        // TICKET UPDATE
        $ticketNo = $booking['ticket_no'];
        $issuedAt = $booking['issued_at'];

        if (
            isset($input['tickets']) &&
            is_array($input['tickets']) &&
            count($input['tickets']) > 0
        ) {

            $firstTicket = $input['tickets'][0];

            $ticketNo = $firstTicket['ticket_no']
                ?? $firstTicket['ticketNo']
                ?? $ticketNo;

            $issuedAt = $firstTicket['issued_at']
                ?? $firstTicket['issuedAt']
                ?? $issuedAt;
        }

        $ticketNo = $input['ticket_no']
            ?? $input['ticketNo']
            ?? $ticketNo;

        $issuedAt = $input['issued_at']
            ?? $input['issuedAt']
            ?? $issuedAt;

        $stmt = $pdo->prepare('
            UPDATE bookings
            SET
                passenger_name = ?,
                passenger_type = ?,
                itinerary_reference = ?,
                booking_type = ?,
                flight_type = ?,
                is_synced = ?,
                departure01 = ?,
                landing01 = ?,
                departure02 = ?,
                landing02 = ?,
                flight_id = ?,
                ticket_no = ?,
                issued_at = ?
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
            $ticketNo,
            $issuedAt,
            $id
        ]);

        $stmt = $pdo->prepare(
            'SELECT * FROM bookings WHERE id = ?'
        );

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
            sendJson([
                "message" => "Booking ID is required"
            ], 400);
        }

        $stmt = $pdo->prepare(
            'SELECT * FROM bookings WHERE id = ?'
        );

        $stmt->execute([$id]);

        $booking = $stmt->fetch();

        if (!$booking) {
            sendJson([
                "message" => "Booking not found"
            ], 404);
        }

        try {

            $stmt = $pdo->prepare(
                'DELETE FROM bookings WHERE id = ?'
            );

            $stmt->execute([$id]);

            sendJson([
                "message" => "Booking deleted successfully"
            ]);

        } catch (Exception $e) {

            sendJson([
                "message" => "Delete failed",
                "error" => $e->getMessage()
            ], 500);
        }

        break;

    default:

        sendJson([
            "message" => "Method not allowed"
        ], 405);

        break;
}
?>