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
	case 'POST':
		create_photo($conn);
		break;
	
	default:
		echo json_encode(["message" => "Requête invalide"]);
		break;
}

function create_photo($conn) {
	session_start();
	if(!$_SESSION["user_id"]) {
		echo json_encode([
			"message" => "Utilisateur non connecté"
		]);
		exit;
	}
	$data = json_decode(file_get_contents("php://input"),true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer une photo"
		]);
		exit;
	}
	if (!isset($data["file_directory"])) {
    	echo json_encode([
			"success" => false, 
			"message" => "Données manquantes"]);
    	exit;
	}
	if (!isset($data["title"], $data["description"])) {
    	echo json_encode([
			"success" => false, 
			"message" => "Champs à remplir par l'utilisateur manquants"]);
    	exit;
	}
	$user_id = $_SESSION["user_id"];
	$title = $data["title"];
	$description = $data["description"];
	$file_directory = $data["file_directory"];	
	$query = "INSERT INTO photos (user_id, title, description, file_directory) VALUES (:user_id, :title, :description, :file_directory)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":user_id", $user_id);
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":description", $description);
	$stmt->bindParam(":file_directory", $file_directory);
	$success = $stmt->execute();
	
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la création de la photo"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Photo crée"
	]);
}