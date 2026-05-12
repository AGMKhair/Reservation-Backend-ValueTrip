<?php
// bookings.php

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

            $stmt = $pdo->prepare('SELECT * FROM bookings WHERE id = ?');
            $stmt->execute([$id]);

            $booking = $stmt->fetch();

            if (!$booking) {
                sendJson([
                    "message" => "Booking not found"
                ], 404);
            }

            // Get tickets
            $stmtTickets = $pdo->prepare(
                'SELECT * FROM tickets WHERE booking_id = ?'
            );

            $stmtTickets->execute([$booking['id']]);

            $booking['tickets'] = $stmtTickets->fetchAll();

            sendJson($booking);
        }

        // All bookings
        $stmt = $pdo->query(
            'SELECT * FROM bookings ORDER BY id DESC'
        );

        $bookings = $stmt->fetchAll();

        foreach ($bookings as &$booking) {

            $stmtTickets = $pdo->prepare(
                'SELECT * FROM tickets WHERE booking_id = ?'
            );

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

        $createdAt = date('Y-m-d H:i:s');

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
                created_at
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
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

        // Read raw body
        $rawInput = file_get_contents("php://input");

        // Try JSON decode
        $input = json_decode($rawInput, true);

        // Fallback for x-www-form-urlencoded
        if (!$input) {
            parse_str($rawInput, $input);
        }

        // Final fallback
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
            ?? $bookingType
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
            ?? $booking['departure_01'];

        $landing01 = $input['landing01']
            ?? $input['landing_01']
            ?? $booking['landing_01'];

        $departure02 = $input['departure02']
            ?? $input['departure_02']
            ?? $booking['departure_02'];

        $landing02 = $input['landing02']
            ?? $input['landing_02']
            ?? $booking['landing_02'];

        $flightId = $input['flightId']
            ?? $input['flight_id']
            ?? $booking['flight_id'];

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

        // Return updated booking
        $stmt = $pdo->prepare(
            'SELECT * FROM bookings WHERE id = ?'
        );

        $stmt->execute([$id]);

        $updatedBooking = $stmt->fetch();

        // Get tickets
        $stmtTickets = $pdo->prepare(
            'SELECT * FROM tickets WHERE booking_id = ?'
        );

        $stmtTickets->execute([$id]);

        $updatedBooking['tickets'] = $stmtTickets->fetchAll();

        sendJson([
            "message" => "Booking updated successfully",
            "data" => $updatedBooking
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

        try {

            $pdo->beginTransaction();

            // Delete tickets first
            $stmt = $pdo->prepare(
                'DELETE FROM tickets WHERE booking_id = ?'
            );

            $stmt->execute([$id]);

            // Delete booking
            $stmt = $pdo->prepare(
                'DELETE FROM bookings WHERE id = ?'
            );

            $stmt->execute([$id]);

            $pdo->commit();

            sendJson([
                "message" => "Booking deleted successfully"
            ]);

        } catch (Exception $e) {

            $pdo->rollBack();

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