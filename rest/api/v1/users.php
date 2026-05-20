<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

require "../../config/database.php";

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'GET':
		if(isset($_GET['id'])) {
			getUser($_GET['id'], $conn);
		} else {
			getUsers($conn);
		}
		break;
	case 'POST':
		createUser($conn);
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
function getUser($id, $conn) {
		$query = "SELECT * FROM users WHERE id=:id";
		$stmt = $conn->prepare($query);
		$stmt->bindParam(':id',$id);
		$stmt->execute();
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		echo json_encode([$user?: ["message"=>"User not found"]]);
}
function createUser($conn) {
		$data = json_decode(file_get_contents("php://input"),true);
		if(isset($data['name']) && isset($data['password'])) {
			$query = "INSERT INTO users (name, password) VALUES (:name,:password)";
			$stmt = $conn->prepare($query);
			$stmt->bindParam(':name',$data['name']);
			$stmt->bindParam('password',$data['password']);
			$stmt->execute();
			echo json_encode(["message"=>"User added"]);
		}else {
			echo json_encode(["message"=>"Invalid data for creating user"]);
		}

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