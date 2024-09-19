<?php
header("Content-Type: application/json");

// Database connection
$dbname = "database.sqlite";

$conn = new PDO("sqlite:$dbname");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Helper function for JSON response
function response($status, $message, $data = [])
{
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

// Handling HTTP request methods
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Add employee
        addEmployee($conn);
        break;
    case 'PUT':
        // Edit employee
        editEmployee($conn);
        break;
    case 'GET':
        // Search employees
        searchEmployees($conn);
        break;
    case 'DELETE':
        // Delete employee
        deleteEmployee($conn);
        break;
    default:
        response(405, 'Method not allowed');
}

function addEmployee($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['first_name'], $data['last_name'], $data['salary'], $data['image'], $data['manager'])) {
        $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, salary, image, manager) VALUES 
        (:first_name, :last_name, :salary, :image, :manager)");
        $stmt->execute([
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':salary' => $data['salary'],
            ':image' => $data['image'],
            ':manager' => $data['manager']
        ]);
        response(201, 'Employee added successfully');
    } else {
        response(400, 'Invalid input');
    }
}

function editEmployee($conn)
{
    parse_str(file_get_contents("php://input"), $_PUT);

    if (isset($_PUT['id'], $_PUT['first_name'], $_PUT['last_name'], $_PUT['salary'], $_PUT['image'], $_PUT['manager'])) {
        $stmt = $conn->prepare("UPDATE employees SET first_name = :first_name, last_name = :last_name, salary = :salary, image = :image, manager = :manager WHERE id = :id");
        $stmt->execute([
            ':id' => $_PUT['id'],
            ':first_name' => $_PUT['first_name'],
            ':last_name' => $_PUT['last_name'],
            ':salary' => $_PUT['salary'],
            ':image' => $_PUT['image'],
            ':manager' => $_PUT['manager']
        ]);
        response(200, 'Employee updated successfully');
    } else {
        response(400, 'Invalid input');
    }
}

function searchEmployees($conn)
{
    $query = isset($_GET['query']) ? $_GET['query'] : '';

    $stmt = $conn->prepare("SELECT * FROM employees WHERE first_name LIKE :query OR last_name LIKE :query");
    $stmt->execute([':query' => "%$query%"]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($employees) {
        response(200, 'Employees found', $employees);
    } else {
        response(404, 'No employees found');
    }
}

function deleteEmployee($conn)
{
    parse_str(file_get_contents("php://input"), $_DELETE);

    if (isset($_DELETE['id'])) {
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = :id");
        $stmt->execute([':id' => $_DELETE['id']]);
        response(200, 'Employee deleted successfully');
    } else {
        response(400, 'Invalid input');
    }
}

?>