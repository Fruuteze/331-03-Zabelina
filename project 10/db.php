<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

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

     case 'import':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Метод не поддерживается']);
                exit;
            }

            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error' => 'Файл не загружен']);
                exit;
            }

            $fileTmpPath = $_FILES['file']['tmp_name'];
            $fileName = $_FILES['file']['name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExt, ['xlsx', 'xls'])) {
                http_response_code(400);
                echo json_encode(['error' => "Неверный формат файла: .$fileExt"]);
                exit;
            }

          try {
    $spreadsheet = IOFactory::load($fileTmpPath);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    $fileName = $_FILES['file']['name'];
    $fileBaseName = mb_strtolower(pathinfo($fileName, PATHINFO_FILENAME)); 

    switch ($fileBaseName) {
        case 'product_type_import':
            importProductTypes($rows, $conn);
            echo json_encode(['success' => true, 'message' => 'Типы продукции импортированы']);
            break;

        case 'material_type_import':
            importDefects($rows, $conn);
            echo json_encode(['success' => true, 'message' => 'Проценты дефектов импортированы']);
            break;

        case 'partner_products_import':
            importSales($rows, $conn);
            echo json_encode(['success' => true, 'message' => 'Продажи импортированы']);
            break;

        case 'partners_import':
            importPartners($rows, $conn);
            echo json_encode(['success' => true, 'message' => 'Партнёры импортированы']);
            break;

        case 'products_import':
            importProducts($rows, $conn);
            echo json_encode(['success' => true, 'message' => 'Продукция импортирована']);
            break;

        default:
            echo json_encode(['error' => 'Не удалось определить тип данных в файле по имени файла. Переименуйте файл в соответствии с ожидаемыми названиями.']);
            break;
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка при чтении файла: ' . $e->getMessage()]);
}


            break;


case 'getPartnerPurchaseTotalsByProductPrice':
    $query = "
        SELECT 
            p.id,
            p.name,
            COALESCE(SUM(sh.amount * pr.minimale_price), 0) AS total_spent
        FROM partner p
        LEFT JOIN sales_history sh ON sh.partner_id = p.id
        LEFT JOIN product pr ON sh.product_id = pr.id
        GROUP BY p.id, p.name
        ORDER BY total_spent DESC
    ";

    $result = pg_query($conn, $query);
    if (!$result) {
        http_response_code(500);
        echo json_encode(['error' => pg_last_error($conn)]);
        exit;
    }

    $totals = pg_fetch_all($result) ?: [];

    echo json_encode([
        'success' => true,
        'totals' => $totals
    ]);
    break;

        case 'getAllSalesHistory':
    $query = "
        SELECT 
            sh.id, 
            sh.sale_date, 
            sh.amount, 
            p.name AS partner_name,
            pr.name AS product_name,
            pr.article,
            pr.minimale_price,
            pt.name AS product_type,
            pt.coefficient
        FROM sales_history sh
        JOIN partner p ON sh.partner_id = p.id
        LEFT JOIN product pr ON sh.product_id = pr.id
        LEFT JOIN product_type pt ON pr.material_id = pt.id
    ";

    $params = [];
    $where = [];

    if (!empty($_GET['dateFrom'])) {
        $where[] = "sh.sale_date >= $" . (count($params) + 1);
        $params[] = $_GET['dateFrom'];
    }

    if (!empty($_GET['dateTo'])) {
        $where[] = "sh.sale_date <= $" . (count($params) + 1);
        $params[] = $_GET['dateTo'];
    }

    if (!empty($_GET['minAmount'])) {
        $where[] = "sh.amount >= $" . (count($params) + 1);
        $params[] = $_GET['minAmount'];
    }

    if (!empty($_GET['maxAmount'])) {
        $where[] = "sh.amount <= $" . (count($params) + 1);
        $params[] = $_GET['maxAmount'];
    }

    if (!empty($_GET['partnerId'])) {
        $where[] = "sh.partner_id = $" . (count($params) + 1);
        $params[] = $_GET['partnerId'];
    }

    if (!empty($where)) {
        $query .= " WHERE " . implode(" AND ", $where);
    }

    $query .= " ORDER BY sh.sale_date DESC";

    $result = pg_query_params($conn, $query, $params);
    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => pg_last_error($conn)]);
        exit;
    }

    $sales = pg_fetch_all($result) ?: [];

    $summaryQuery = "
        SELECT 
            COUNT(*) AS count,
            SUM(sh.amount) AS total_amount,
            AVG(sh.amount) AS avg_amount,
            MIN(sh.sale_date) AS first_date,
            MAX(sh.sale_date) AS last_date
        FROM sales_history sh
    ";

    if (!empty($where)) {
        $summaryQuery .= " WHERE " . implode(" AND ", $where);
    }

    $summaryResult = pg_query_params($conn, $summaryQuery, $params);
    $summary = pg_fetch_assoc($summaryResult) ?: [
        'count' => 0,
        'total_amount' => 0,
        'avg_amount' => 0,
        'first_date' => null,
        'last_date' => null
    ];

    echo json_encode([
        'success' => true,
        'sales' => $sales,
        'summary' => $summary
    ]);
    break;

        case 'getAllPartners':
            $result = pg_query($conn, "SELECT * FROM partner ORDER BY name");
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

            $result = pg_query_params($conn,
                "INSERT INTO partner (name, type, rating, address, director_name, phone_number, email, inn) 
                 VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING *",
                [
                    $input['name'],
                    $input['type'] ?? null,
                    $input['rating'],
                    $input['address'] ?? null,
                    $input['director_name'] ?? null,
                    $input['phone_number'] ?? null,
                    $input['email'] ?? null,
                    $input['inn'] ?? null
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

            $query = "SELECT * FROM partner WHERE id = $1";
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

            $check = pg_query_params($conn, "SELECT 1 FROM partner WHERE id = $1", [$input['id']]);
            if (pg_num_rows($check) === 0) {
                http_response_code(404);
                echo json_encode(["error" => "Партнер не найден"]);
                exit;
            }

            $result = pg_query_params($conn,
                "UPDATE partner SET 
                    name = $1, 
                    type = $2, 
                    rating = $3, 
                    address = $4, 
                    director_name = $5, 
                    phone_number = $6, 
                    email = $7, 
                    inn = $8 
                 WHERE id = $9 RETURNING *",
                [
                    $input['name'],
                    $input['type'] ?? null,
                    $input['rating'],
                    $input['address'] ?? null,
                    $input['director_name'] ?? null,
                    $input['phone_number'] ?? null,
                    $input['email'] ?? null,
                    $input['inn'] ?? null,
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

            $result = pg_query_params($conn, "DELETE FROM partner WHERE id = $1 RETURNING id", [$id]);
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

function importPartners($rows, $conn) {
    foreach (array_slice($rows, 1) as $r) {
        if (empty($r[1])) continue;

        $inn = trim($r[6] ?? '');
        if ($inn === '') $inn = null;

        pg_query_params($conn, "
            INSERT INTO partner (type, name, director_name, email, phone_number, address, inn, rating)
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
        ", [
            $r[0] ?? null,
            $r[1] ?? null,
            $r[2] ?? null,
            $r[3] ?? null,
            $r[4] ?? null,
            $r[5] ?? null,
            $inn,
            isset($r[7]) ? floatval($r[7]) : null
        ]);
    }
}

function importProducts($rows, $conn) {
    foreach (array_slice($rows, 1) as $r) {
        if (empty($r[1])) continue;

        $typeRes = pg_query_params($conn, "SELECT id FROM product_type WHERE name = $1", [$r[0]]);
        $typeId = pg_fetch_result($typeRes, 0, 0) ?? null;
        if (!$typeId) continue;

        pg_query_params($conn, "
            INSERT INTO product (name, material_id, article, minimale_price)
            VALUES ($1, $2, $3, $4)
        ", [
            $r[1],
            $typeId,
            $r[2] ?? null,
            isset($r[3]) ? floatval(str_replace(',', '.', $r[3])) : null
        ]);
    }
}

function importProductTypes($rows, $conn) {
    foreach (array_slice($rows, 1) as $r) {
        if (empty($r[0])) continue;
        pg_query_params($conn, "
            INSERT INTO product_type (name, coefficient)
            VALUES ($1, $2)
            ON CONFLICT (name) DO NOTHING
        ", [$r[0], floatval(str_replace(',', '.', $r[1] ?? '0'))]);
    }
}

function importSales($rows, $conn) {
    foreach (array_slice($rows, 1) as $r) {
        if (empty($r[0]) || empty($r[1])) continue;

        $partner = pg_query_params($conn, "SELECT id FROM partner WHERE name = $1", [$r[1]]);
        $partnerId = pg_fetch_result($partner, 0, 0) ?? null;

        $product = pg_query_params($conn, "SELECT id FROM product WHERE name = $1", [$r[0]]);
        $productId = pg_fetch_result($product, 0, 0) ?? null;

        if (!$partnerId || !$productId) continue;

        $timestamp = strtotime($r[3] ?? '');
        if ($timestamp === false) continue;
        $date = date('Y-m-d', $timestamp);

        pg_query_params($conn, "
            INSERT INTO sales_history (partner_id, product_id, amount, sale_date)
            VALUES ($1, $2, $3, $4)
        ", [$partnerId, $productId, floatval($r[2]), $date]);
    }
}

function importDefects($rows, $conn) {
    pg_query($conn, "CREATE TABLE IF NOT EXISTS material_defect (
        id SERIAL PRIMARY KEY,
        material_type VARCHAR(255),
        defect_percent NUMERIC(5,2)
    )");

    foreach (array_slice($rows, 1) as $r) {
        if (empty($r[0])) continue;
        $percent = floatval(str_replace(['%', ','], ['', '.'], $r[1]));
        pg_query_params($conn, "
            INSERT INTO material_defect (material_type, defect_percent)
            VALUES ($1, $2)
        ", [$r[0], $percent]);
    }
}



pg_close($conn);
