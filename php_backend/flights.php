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
            $flight = $stmt->fetch();
            if ($flight) {
                // Fetch airline relation
                $stmtAir = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
                $stmtAir->execute([$flight['airline_id']]);
                $flight['airline'] = $stmtAir->fetch() ?: null;
                sendJson($flight);
            } else {
                http_response_code(404);
                exit();
            }
        } else {
            $stmt = $pdo->query('SELECT * FROM flights');
            $flights = $stmt->fetchAll();
            foreach ($flights as &$flight) {
                $stmtAir = $pdo->prepare('SELECT * FROM airlines WHERE id = ?');
                $stmtAir->execute([$flight['airline_id']]);
                $flight['airline'] = $stmtAir->fetch() ?: null;
            }
            sendJson($flights);
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
        
        $stmt = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            http_response_code(404);
            exit();
        }

        $flightName = $input['flightName'] ?? null;
        $flightType = $input['flightType'] ?? null;
        $airlineId = isset($input['airline']) ? $input['airline']['id'] : null;
        $meal01 = $input['meal01'] ?? null;
        $meal02 = $input['meal02'] ?? null;

        $stmt = $pdo->prepare('UPDATE flights SET flight_name = ?, flight_type = ?, airline_id = ?, meal01 = ?, meal02 = ? WHERE id = ?');
        $stmt->execute([$flightName, $flightType, $airlineId, $meal01, $meal02, $id]);

        $stmt = $pdo->prepare('SELECT * FROM flights WHERE id = ?');
        $stmt->execute([$id]);
        sendJson($stmt->fetch());
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
