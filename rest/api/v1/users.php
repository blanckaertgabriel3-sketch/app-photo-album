<?php

// header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");

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
		}
		elseif($_GET['action'] === 'getUser') {
			getUser($conn);
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
		echo json_encode(["message"=>"Requête invalide"]);
		break;
}
function getUser($conn) {
	session_start();
	if(!$_SESSION["user_id"]) {
		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		return;
	}
	$user_id = $_SESSION['user_id'];
	if(!$user_id) {
		echo json_encode([
			"success" => false,
			"message" => "Cookie utilisateur non trouvé"
		]);
		return;
	}
	$query = "SELECT * FROM users WHERE id=:user_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":user_id", $user_id);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	if(!$user) {
		echo json_encode([
			"success" => false,
			"message" => "Utilisateur non trouvé"
		]);
		return;
	}

	echo json_encode([
		"success" => true,
		"message" => "Utilisateur trouvé",
		"user" => $user
	]);
}
function login($conn) {
	$data = json_decode(file_get_contents("php://input"), true);
	$name = $data["name"];
	$password = $data["password"];
	$query = "SELECT * FROM users WHERE name=:name";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(':name', $name);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);
	if($user && password_verify($password, $user["password"])) {
		//add user id in a new session
		session_start();
		$_SESSION['user_id'] = $user["id"];
		echo json_encode([
			"success" => true,
			"user" => $user,
			"message" => "Vous êtres connecté"
		]);
	}else {
		echo json_encode([
			"success" => false,
			"message" => "Mauvais mot de passe ou nom d'utilisateur"
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
				"message" => "Utilisateur créée"
				]);
		}else {
			echo json_encode([
				"success" => false,
				"message" => "Données invalide pour créer un utilisateur"
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
			"message" => "Utilisateur déjà existant"
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
			echo json_encode(["message"=>"Utilisateur modifié"]);
		}else {
			echo json_encode(["message"=>"Données invalide pour modification utilisateur"]);
		}
}
function deleteUser($conn) {
	$data = json_decode(file_get_contents("php://input"),true);
	if(isset($data['id'])) {
		$query = "DELETE FROM users WHERE id=:id";
		$stmt = $conn->prepare($query);
		$stmt->bindParam(':id',$data['id']);
		$stmt->execute();
		echo json_encode(["message"=>"Utilisateur supprimé"]);
	} else {
		echo json_encode(["message"=>"Données involide pour supprimer l'utilisateur"]);
	}
}