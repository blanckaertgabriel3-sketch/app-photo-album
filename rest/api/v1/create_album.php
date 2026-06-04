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
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer une photo"
		]);
		exit;
	}
	$title = $data["title"];

	if(!isset($title)) {
		echo json_encode([
			"message" => "Données manquantes pour créer un album"
		]);
		exit;
	}
	//check if title already exist.
	$query = "SELECT title FROM albums WHERE title=:title";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":title", $title);
	$stmt->execute();
	$existing_title = $stmt->fetch(PDO::FETCH_ASSOC);
	if($existing_title) {
		echo json_encode([
			"message" => "Ce titre est déjà utilisé"
		]);
		exit;
	}
	//create album.
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