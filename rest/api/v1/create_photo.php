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
	$user_id = $_SESSION["user_id"];
	$title = $data["title"];
	$description = $data["description"];
	$file_directory = $data["file_directory"];	
	if (!isset($file_directory)) {
    	echo json_encode([
			"success" => false, 
			"message" => "Données manquantes"]);
    	exit;
	}
	if (!isset($title, $description)) {
    	echo json_encode([
			"success" => false, 
			"message" => "Champs à remplir par l'utilisateur manquants"]);
    	exit;
	}
	//check if title already exist.
	$query = "SELECT title FROM photos WHERE title=:title";
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
	//create photo.
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