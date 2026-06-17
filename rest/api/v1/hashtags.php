<?php
session_start();

require "../../config/header.php";
require "../../config/database.php";
require "../../middleware/auth.php";

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'POST':
		if($_GET["action"] === "create_hashtag") {
			create_hashtag($conn);
		}
		elseif($_GET["action"] === "get_hashtag") {
			get_hashtag($conn);
		}
		break;
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}
function create_hashtag($conn) {
	
	requireAuth();
	
	// get hashtag if exist
	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "JSON invalide pour créer un hashtag"
		]);
		exit;
	}
	$name =  $data["name"];
	if(!isset($name)) {
		echo json_encode([
			"message" => "Hashtag manquant dans le json"
		]);
		exit;
	}
	$query = "SELECT * FROM hashtags WHERE name=:name";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":name", $name);
	$found_success = $stmt->execute();
	if(!$found_success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec pour trouver un hashtag"
		]);
		exit;
	}
	$hashtag = $stmt->fetch(PDO::FETCH_ASSOC);
	if($hashtag) {
		echo json_encode([
			"success" => true,
			"message" => "Hashtag trouvé",
			"hashtag_id" => $hashtag["id"],
			"hashtag" => $hashtag
		]);
		exit;
	}
	// create a hashtag
	$query = "INSERT INTO hashtags(name) VALUES (:name)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":name", $name);
	$create_success = $stmt->execute();
	$hashtag_id = $conn->lastInsertId();
	if(!$create_success) {
		echo json_encode([
			"message" => "Échec de création d'hashtag"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Hashtag crée",
		"hashtag_id" => $hashtag_id
	]);
}
function get_hashtag($conn) {
	
	requireAuth();

	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour trouver un hashtag"
		]);
		exit;
	}
	$hashtag_name = $data["hashtag_name"];
	if(!isset($hashtag_name)) {
		echo json_encode([
			"message" => "hashtag_name manquant"
		]);
	}
	$query = "SELECT * FROM hashtags WHERE name=:hashtag_name";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":hashtag_name", $hashtag_name);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"message" => "Hashtag non trouvé"
		]);
		exit; 
	}
	$hashtag = $stmt->fetch(PDO::FETCH_ASSOC);
	echo json_encode([
		"success" => true,
		"message" => "Hashtag trouvé",
		"hashtag" => $hashtag
	]);
}