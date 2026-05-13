<?php
// flights.php
require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? intval($_GET['id']) : null;

switch ($method) {
    case 'GET':
if ($id) {

    $stmt = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
    $stmt->execute([$id]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$flight) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Flight not found"
        ]);
        exit;
    }

    // airline join
    $stmtAir = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
    $stmtAir->execute([$flight['airline_id']]);
    $flight['airline'] = $stmtAir->fetch(PDO::FETCH_ASSOC) ?: null;

    echo json_encode([
        "success" => true,
        "data" => $flight
    ]);
    exit;
}

else {

    $stmt = $pdo->query('
        SELECT *
        FROM flights
        ORDER BY
            order_index IS NULL ASC,
            order_index ASC,
            id ASC
    ');

    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // collect airline ids (IMPORTANT optimization)
    $airlineIds = [];

    foreach ($flights as $f) {
        if (!empty($f['airline_id'])) {
            $airlineIds[] = $f['airline_id'];
        }
    }

    $airlineIds = array_unique($airlineIds);

    // fetch airlines in ONE query (fix N+1 problem)
    $airlinesMap = [];

    if (!empty($airlineIds)) {

        $in = implode(',', array_map('intval', $airlineIds));

        $stmtAir = $pdo->query("
            SELECT *
            FROM airlines
            WHERE id IN ($in)
        ");

        $airlines = $stmtAir->fetchAll(PDO::FETCH_ASSOC);

        foreach ($airlines as $air) {
            $airlinesMap[$air['id']] = $air;
        }
    }

    // attach airline data
    foreach ($flights as &$flight) {
        $flight['airline'] = $airlinesMap[$flight['airline_id']] ?? null;
    }

    echo json_encode([
        "success" => true,
        "data" => $flights
    ]);
    exit;
}
        break;

    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);

        $airlineId = isset($input['airline']) ? $input['airline']['id'] : null;
        $flightName = $input['flightName'] ?? null;
        $flightType = $input['flightType'] ?? null;
        $flightNo = $input['flightNo'] ?? null;
        $flightNo2 = $input['flightNo2'] ?? null;
        $departureTimeFirst = $input['departureTimeFirst'] ?? null;
        $arrivalTimeFirst = $input['arrivalTimeFirst'] ?? null;
        $departureTimeSecond = $input['departureTimeSecond'] ?? null;
        $arrivalTimeSecond = $input['arrivalTimeSecond'] ?? null;
        $fromFirstAirport = $input['fromFirstAirport'] ?? null;
        $toFirstAirport = $input['toFirstAirport'] ?? null;
        $fromSecondAirport = $input['fromSecondAirport'] ?? null;
        $toSecondAirport = $input['toSecondAirport'] ?? null;
        $checkInBaggage = $input['checkInBaggage'] ?? null;
        $cabinBaggage = $input['cabinBaggage'] ?? null;
        $meal01 = $input['meal01'] ?? null;
        $meal02 = $input['meal02'] ?? null;
        $createdAt = date('Y-m-d H:i:s');

        $stmt = $pdo->prepare('INSERT INTO flights (airline_id, flight_name, flight_type, flight_no, flight_no_2, departure_time_first, arrival_time_first, departure_time_second, arrival_time_second, from_first_airport, to_first_airport, from_second_airport, to_second_airport, check_in_baggage, cabin_baggage, meal01, meal02, created_at) VALUES (?, ?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$airlineId, $flightName, $flightType, $flightNo,  $flightNo2, $departureTimeFirst, $arrivalTimeFirst, $departureTimeSecond, $arrivalTimeSecond, $fromFirstAirport, $toFirstAirport, $fromSecondAirport, $toSecondAirport, $checkInBaggage, $cabinBaggage, $meal01, $meal02, $createdAt]);
        $newId = $pdo->lastInsertId();

        $stmt = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
        $stmt->execute([$newId]);
        sendJson($stmt->fetch());
        break;


case 'PUT':

    if (!$id) {
        sendJson("Flight ID is required", 400);
    }

    $input = json_decode(file_get_contents('php://input'), true);

    // Check flight exists
    $stmt = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
    $stmt->execute([$id]);

    $existingFlight = $stmt->fetch();

    if (!$existingFlight) {
        sendJson("Flight not found", 404);
    }

    $airlineId = isset($input['airline'])
        ? $input['airline']['id']
        : $existingFlight['airline_id'];

    $flightName = $input['flightName'] ?? $existingFlight['flight_name'];
    $flightType = $input['flightType'] ?? $existingFlight['flight_type'];

    $flightNo = $input['flightNo'] ?? $existingFlight['flight_no'];
    $flightNo2 = $input['flightNo2'] ?? $existingFlight['flight_no_2'];

    $departureTimeFirst = $input['departureTimeFirst'] ?? $existingFlight['departure_time_first'];
    $arrivalTimeFirst = $input['arrivalTimeFirst'] ?? $existingFlight['arrival_time_first'];

    $departureTimeSecond = $input['departureTimeSecond'] ?? $existingFlight['departure_time_second'];
    $arrivalTimeSecond = $input['arrivalTimeSecond'] ?? $existingFlight['arrival_time_second'];

    $fromFirstAirport = $input['fromFirstAirport'] ?? $existingFlight['from_first_airport'];
    $toFirstAirport = $input['toFirstAirport'] ?? $existingFlight['to_first_airport'];

    $fromSecondAirport = $input['fromSecondAirport'] ?? $existingFlight['from_second_airport'];
    $toSecondAirport = $input['toSecondAirport'] ?? $existingFlight['to_second_airport'];

    $checkInBaggage = $input['checkInBaggage'] ?? $existingFlight['check_in_baggage'];
    $cabinBaggage = $input['cabinBaggage'] ?? $existingFlight['cabin_baggage'];

    $meal01 = $input['meal01'] ?? $existingFlight['meal01'];
    $meal02 = $input['meal02'] ?? $existingFlight['meal02'];

    $stmt = $pdo->prepare('
        UPDATE flights SET
            airline_id = ?,
            flight_name = ?,
            flight_type = ?,
            flight_no = ?,
            flight_no_2 = ?,
            departure_time_first = ?,
            arrival_time_first = ?,
            departure_time_second = ?,
            arrival_time_second = ?,
            from_first_airport = ?,
            to_first_airport = ?,
            from_second_airport = ?,
            to_second_airport = ?,
            check_in_baggage = ?,
            cabin_baggage = ?,
            meal01 = ?,
            meal02 = ?
        WHERE id = ?
    ');

    $stmt->execute([
        $airlineId,
        $flightName,
        $flightType,
        $flightNo,
        $flightNo2,
        $departureTimeFirst,
        $arrivalTimeFirst,
        $departureTimeSecond,
        $arrivalTimeSecond,
        $fromFirstAirport,
        $toFirstAirport,
        $fromSecondAirport,
        $toSecondAirport,
        $checkInBaggage,
        $cabinBaggage,
        $meal01,
        $meal02,
        $id
    ]);

    // Return updated flight
    $stmt = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
    $stmt->execute([$id]);

    $updatedFlight = $stmt->fetch();

    // attach airline
    $stmtAir = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
    $stmtAir->execute([$updatedFlight['airline_id']]);

    $updatedFlight['airline'] = $stmtAir->fetch() ?: null;

    sendJson([
        "message" => "Flight updated successfully",
        "data" => $updatedFlight
    ]);

    break;

    case 'DELETE':
        if (!$id) {
            sendJson("Flight ID is required", 400);
        }
        $stmt = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            exit();
        }

        $stmt = $pdo->prepare('DELETE FROM flights WHERE id = ?');
        $stmt->execute([$id]);
        http_response_code(204);
        exit();
        break;

    default:
        sendJson("Method not allowed", 405);
        break;
}
?>
