<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Max-Age: 3600");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$conn = pg_connect("host=localhost port=5432 dbname=project user=postgres password=1234");

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Ошибка подключения к PostgreSQL"]);
    exit;
}

try {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'getAllPartners':
            $result = pg_query($conn, "SELECT * FROM partners ORDER BY name");
            if (!$result) throw new Exception(pg_last_error($conn));
            echo json_encode(pg_fetch_all($result) ?: []);
            break;

        case 'addPartner':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["error" => "Метод не поддерживается"]);
                exit;
            }

            $required = ['name', 'rating'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Не заполнено поле: $field"]);
                    exit;
                }
            }

            $type_id = isset($input['type_id']) ? (int)$input['type_id'] : null;

            if ($type_id !== null) {
                $type_check = pg_query_params($conn, "SELECT 1 FROM partner_types WHERE id = $1", [$type_id]);
                if (pg_num_rows($type_check) === 0) {
                    http_response_code(400);
                    echo json_encode(["error" => "Неверный тип партнера"]);
                    exit;
                }
            }

            $result = pg_query_params($conn,
                "INSERT INTO partners (name, type_id, rating, address, director_name, phone, email) 
                 VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING *",
                [
                    $input['name'],
                    $type_id,
                    $input['rating'],
                    $input['address'] ?? null,
                    $input['director_name'] ?? null,
                    $input['phone'] ?? null,
                    $input['email'] ?? null
                ]
            );

            if (!$result) {
                http_response_code(500);
                echo json_encode(["error" => pg_last_error($conn)]);
                exit;
            }

            http_response_code(201);
            echo json_encode(pg_fetch_assoc($result));
            break;

        case 'getPartnerById':
            $id = $_GET['id'] ?? null;
            if (!$id || !is_numeric($id)) {
                throw new Exception("Неверный ID партнёра");
            }

            $query = "SELECT id, name, type_id, rating, address, director_name, phone, email 
                      FROM partners 
                      WHERE id = $1";
            $result = pg_query_params($conn, $query, [$id]);

            if (!$result) {
                throw new Exception(pg_last_error($conn));
            }

            $data = pg_fetch_assoc($result);
            if (!$data) {
                throw new Exception("Партнёр с ID $id не найден");
            }

            echo json_encode($data);
            break;

        case 'updatePartner':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(["error" => "Метод не поддерживается"]);
                exit;
            }

            $required = ['id', 'name', 'rating'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Не заполнено обязательное поле: $field"]);
                    exit;
                }
            }

            $check = pg_query_params($conn, "SELECT 1 FROM partners WHERE id = $1", [$input['id']]);
            if (pg_num_rows($check) === 0) {
                http_response_code(404);
                echo json_encode(["error" => "Партнер не найден"]);
                exit;
            }

            $result = pg_query_params($conn,
                "UPDATE partners SET 
                    name = $1, 
                    type_id = $2, 
                    rating = $3, 
                    address = $4, 
                    director_name = $5, 
                    phone = $6, 
                    email = $7 
                WHERE id = $8 RETURNING *",
                [
                    $input['name'],
                    $input['type_id'] ?? null,
                    $input['rating'],
                    $input['address'] ?? null,
                    $input['director_name'] ?? null,
                    $input['phone'] ?? null,
                    $input['email'] ?? null,
                    $input['id']
                ]
            );

            if (!$result) {
                http_response_code(500);
                echo json_encode(["error" => pg_last_error($conn)]);
                exit;
            }

            echo json_encode(pg_fetch_assoc($result));
            break;

        case 'deletePartner':
            $id = $_GET['id'] ?? null;
            if (!$id || !is_numeric($id)) {
                http_response_code(400);
                echo json_encode(["error" => "Неверный ID"]);
                exit;
            }

            $result = pg_query_params($conn, "DELETE FROM partners WHERE id = $1 RETURNING id", [$id]);
            if (!$result) {
                http_response_code(500);
                echo json_encode(["error" => pg_last_error($conn)]);
                exit;
            }

            $deleted = pg_fetch_assoc($result);
            echo json_encode(["success" => (bool)$deleted]);
            break;

        default:
            http_response_code(400);
            echo json_encode(["error" => "Неизвестное действие"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}

pg_close($conn);
