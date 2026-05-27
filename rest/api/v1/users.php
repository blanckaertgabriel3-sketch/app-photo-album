<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json");

require "../../config/database.php";

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'GET':
		if($_GET['action'] === 'login') {
			login($conn);
		}else {
			getUsers($conn);
		}
		break;
	case 'POST':
		if($_GET['action'] === 'register') {
			register($conn);
		}
		elseif($_GET['action'] === 'login') {
			login($conn);
		}
		break;
	case 'PUT':
		updateUser($conn);
		break;
	case 'DELETE':
		deleteUser($conn);
		break;	
	default:
		echo json_encode(["message"=>"Invalid request"]);
		break;
}
function getUsers($conn) {
	$query = "SELECT * FROM users";
	$stmt = $conn->prepare($query);
	$stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode([$users]);
}
function login($conn) {
	$data = json_decode(file_get_contents("php://input"), true);
	$name = $data["name"];
	$password = $data["password"];
	$query = "SELECT * FROM users WHERE name=:name";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(':name', $name);
	$stmt->execute();
	$user = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if($user && password_verify($password, $user["password"])) {
		echo json_encode([
			"success" => true,
			"user" => $user,
			"message" => "You are connected"
		]);
	}else {
		echo json_encode([
			"success" => false,
			"message" => "Incorrect name or password"
		]);
	}
}
function createUser($conn) {
		$data = json_decode(file_get_contents("php://input"),true);
		$name = $data["name"];
		$password = $data["password"];
		$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
		if(isset($name) && isset($password)) {
			$query = "INSERT INTO users (name, password) VALUES (:name,:password)";
			$stmt = $conn->prepare($query);
			$stmt->bindParam(':name',$name);
			$stmt->bindParam(':password',$hashedPassword);
			$stmt->execute();
			echo json_encode([
				"success" => true,
				"message" => "User added"
				]);
		}else {
			echo json_encode([
				"success" => false,
				"message" => "Invalid data for creating user"
				]);
		}

}
function register($conn) {
	$data = json_decode(file_get_contents("php://input"), true);
	$name = $data["name"];
	$query = "SELECT * FROM users WHERE name=:name";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(':name', $name);
	$stmt->execute();
	$user = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if($user) {
		echo json_encode([
			"message" => "User already exist"
		]);
		return;
	}
	createUser($conn);
}
function updateUser($conn) {
		$data = json_decode(file_get_contents("php://input"),true);
		if(isset($data['id']) && isset($data['name']) && isset($data['password'])) {
			$query = "UPDATE users SET name=:name, password=:password WHERE id=:id ";
			$stmt = $conn->prepare($query);
			$stmt->bindParam(':id',$data['id']);
			$stmt->bindParam(':name',$data['name']);
			$stmt->bindParam(':password',$data['password']);
			$stmt->execute();
			echo json_encode(["message"=>"User updated"]);
		}else {
			echo json_encode(["message"=>"Invalid data for updating"]);
		}
}
function deleteUser($conn) {
	$data = json_decode(file_get_contents("php://input"),true);
	if(isset($data['id'])) {
		$query = "DELETE FROM users WHERE id=:id";
		$stmt = $conn->prepare($query);
		$stmt->bindParam(':id',$data['id']);
		$stmt->execute();
		echo json_encode(["message"=>"User deleted"]);
	} else {
		echo json_encode(["message"=>"Invalid data for deleting the user."]);
	}
}