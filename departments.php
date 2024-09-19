<?php
header("Content-Type: application/json");

// Include database connection
$dbname = "database.sqlite";

$conn = new PDO("sqlite:$dbname");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Handling HTTP request methods

function response($status, $message,
    $data = []
) {
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Add department
        addDepartment($conn);
        break;
    case 'PUT':
        // Edit department
        editDepartment($conn);
        break;
    case 'GET':
        // Search departments
        searchDepartments($conn);
        break;
    case 'DELETE':
        // Delete department
        deleteDepartment($conn);
        break;
    default:
        response(405, 'Method not allowed');
}

// Add Department
function addDepartment($conn)
{
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['name'])) {
        $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (:name)");
        $stmt->execute([':name' => $data['name']]);
        response(201, 'Department added successfully');
    } else {
        response(400, 'Invalid input');
    }
}

// Edit Department
function editDepartment($conn)
{
    parse_str(file_get_contents("php://input"), $_PUT);

    if (isset($_PUT['id'], $_PUT['name'])) {
        $stmt = $conn->prepare("UPDATE departments SET name = :name WHERE id = :id");
        $stmt->execute([':id' => $_PUT['id'], ':name' => $_PUT['name']]);
        response(200, 'Department updated successfully');
    } else {
        response(400, 'Invalid input');
    }
}

// Search Departments and display employee count and salary sum
function searchDepartments($conn)
{
    $stmt = $conn->prepare("
        SELECT d.id, d.name, COUNT(e.id) AS employee_count, SUM(e.salary) AS total_salary
        FROM departments d
        LEFT JOIN employees e ON d.id = e.department_id
        GROUP BY d.id
    ");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($departments) {
        response(200, 'Departments found', $departments);
    } else {
        response(404, 'No departments found');
    }
}

// Delete Department (only if no employees are assigned)
function deleteDepartment($conn)
{
    parse_str(file_get_contents("php://input"), $_DELETE);

    if (isset($_DELETE['id'])) {
        $stmt = $conn->prepare("SELECT COUNT(*) AS employee_count FROM employees WHERE department_id = :id");
        $stmt->execute([':id' => $_DELETE['id']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['employee_count'] > 0) {
            response(400, 'Cannot delete department with employees assigned to it');
        } else {
            $stmt = $conn->prepare("DELETE FROM departments WHERE id = :id");
            $stmt->execute([':id' => $_DELETE['id']]);
            response(200, 'Department deleted successfully');
        }
    } else {
        response(400, 'Invalid input');
    }
}
