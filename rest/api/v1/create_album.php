<?php

require "../../config/database.php";

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'POST':
		create_album($conn);
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}

function create_album($conn) {
	session_start();
	if(!$_SESSION["user_id"]) {
		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		exit;
	}
	$owner_id = $_SESSION["user_id"];
	$data = json_decode(file_get_contents("php://input"), true);
	$title = $data["title"];

	if(!isset($title, $owner_id)) {
		echo json_encode([
			"message" => "Données manquantes pour créer un album"
		]);
		exit;
	}
	$query = "INSERT INTO albums (title, owner_id) VALUES (:title, :owner_id)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":owner_id", $owner_id);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"message" => "Échec de la création d'album"
		]);
		exit;
	}
	echo json_encode([
		"message" => "Album crée"
	]);
}