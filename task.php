<?php

$dbname = "database.sqlite";

$conn = new PDO("sqlite:$dbname");
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function response($status, $message, $data = [])
{
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method){
    case "POST":
        addTask($conn);
        break;
    case "GET":
        getTasks($conn);
    default:
    response(405, "Method not allowed");
    
}

function addTask($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data["employee"])){
        $stmt = $conn->prepare("INSERT INTO task (status, employee_id) values (:status, :employee_id)");
        $stmt->execute([":status" => "incomplete", ":employee_id" => $data["employee"]]);
    }
    else{
        response(400, "invalid input");
    }
    response(201, "task was created");

}

function getTasks($conn){
    $stmt = $conn->prepare("SELECT * FROM task");
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if($tasks)
    {
        response(200, "tasks found", $tasks);
    } else
    {
        response(404, "no tasks found");
    }

}