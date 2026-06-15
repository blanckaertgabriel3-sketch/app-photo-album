<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");


// // header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Origin: http://localhost");
// header("Access-Control-Allow-Credentials: true");

// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");
// header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require "../../config/database.php";

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'POST':
		if($_GET['action'] === 'register') {
			register($conn);
		}
		elseif($_GET['action'] === 'login') {
			login($conn);
		}
		elseif($_GET['action'] === 'getUser') {
			getUser($conn);
		}
		elseif($_GET['action'] === 'search_users') {
			search_users($conn);
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
function search_users($conn) {
	
	if (!isset($_SESSION["user_id"])) {
		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		exit;
	}
	$query = "SELECT id FROM users WHERE id = :id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":id", $_SESSION["user_id"]);
	$stmt->execute();
	if (!$stmt->fetch()) {
		session_destroy();
		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		exit;
	}
	
	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "JSON invalide pour la recherche utilisateur"
		]);
		exit;
	}
	$letters = $data["letters"];
	if(!isset($letters)) {
		echo json_encode([
			"success" => false,
			"message" => "Données manquantes pour la recherche utilisateur"
		]);
		exit;
	}
	if($letters === "") {
		echo json_encode([
			"success" => false,
			"message" => "Veuillez entrer une lettre"
		]);
		exit;
	}
	$query = "SELECT * FROM users WHERE name LIKE :search";
	$search = "%" . $letters . "%";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":search", $search);
	$success = $stmt->execute();
	$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la recherche utilisateur"
		]);
		exit;
	}
	if (empty($users)) {
		echo json_encode([
			"success" => false,
			"message" => "Élément non trouvé"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Utilisateur trouvée",
		"users_result" => $users
	], JSON_PRETTY_PRINT);
}
function getUser($conn) {
	
	if (!isset($_SESSION["user_id"])) {
		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		exit;
	}

	$query = "SELECT id FROM users WHERE id = :id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":id", $_SESSION["user_id"]);
	$stmt->execute();

	if (!$stmt->fetch()) {
		session_destroy();

		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		exit;
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