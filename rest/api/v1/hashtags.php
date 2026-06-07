<?php

require "../../config/database.php";

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
	session_start();
	if(!$_SESSION["user_id"]) {
		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		exit;
	}
	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer un hashtag"
		]);
		exit;
	}
	$name =  $data["name"];
	if(!isset($name)) {
		echo json_encode([
			"message" => "Hashtag manquant"
		]);
		exit;
	}
	$query = "INSERT INTO hashtags(name) VALUES (:name)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":name", $name);
	$success = $stmt->execute();
	$hashtag_id = $conn->lastInsertId();
	if(!$success) {
		echo json_encode([
			"message" => "Échec de création d'hashtag"
		]);
		exit;
	}
	echo json_encode([
		"message" => "Hashtag crée",
		"hashtag_id" => $hashtag_id
	]);
}
function get_hashtag($conn) {
	session_start();
	if(!$_SESSION["user_id"]) {
		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		exit;
	}
	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer un hashtag"
		]);
		exit;
	}
	$hashtag_id = $data["hashtag_id"];
	if(!isset($hashtag_id)) {
		echo json_encode([
			"message" => "hashtag_id manquant"
		]);
	}
	$query = "SELECT * FROM hashtags WHERE id=:hashtag_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":hashtag_id", $hashtag_id);
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