<?php
session_start();

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
		if($_GET["action"] === "create") {
			create_photo($conn);
		}
		elseif($_GET["action"] === "upload") {
			upload($conn);
		}
		elseif($_GET["action"] === "search") {
			search_photo($conn);
		}
		elseif($_GET["action"] === "get_photo") {
			get_photo($conn);
		}
		break;
	
	default:
		echo json_encode(["message" => "Requête invalide"]);
		break;
}

function create_photo($conn) {
	
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
	$data = json_decode(file_get_contents("php://input"),true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer une photo"
		]);
		exit;
	}
	$user_id = $_SESSION["user_id"];
	$title = trim($data["title"]);
	$description = $data["description"];
	$file_directory = $data["file_directory"];
	$import_date = $data["import_date"];
	$messages_allowed = $data["messages_allowed"];
	if (!isset($file_directory)) {
    	echo json_encode([
			"success" => false, 
			"message" => "Données manquantes"]);
    	exit;
	}
	if(!isset($title) || $title === "") {
		echo json_encode([
			"message" => "Titre manquant"
		]);	
		exit;
	}
	if (!isset($description)) {
    	echo json_encode([
			"success" => false, 
			"message" => "Description manquante"
		]);
    	exit;
	}
	if (!isset($import_date)) {
    	echo json_encode([
			"success" => false, 
			"message" => "Date d'import manquante"
		]);
    	exit;
	}
	if (!isset($messages_allowed)) {
    	echo json_encode([
			"success" => false, 
			"message" => "Autorisation de messages manquante"
		]);
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
	$query = "INSERT INTO photos (user_id, title, description, file_directory, import_date, messages_allowed) VALUES (:user_id, :title, :description, :file_directory, :import_date, :messages_allowed)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":user_id", $user_id);
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":description", $description);
	$stmt->bindParam(":file_directory", $file_directory);
	$stmt->bindParam(":import_date", $import_date);
	$stmt->bindParam(":messages_allowed", $messages_allowed);
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
		"message" => "Photo créée"
	]);
}
function upload ($conn) {
	
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

	$allowed_size = 500000;
	$file = $_FILES['file'];
	if (!isset($file)) {
		echo json_encode([
			"success" => false,
			"message" => "Aucun fichier reçu"
		]);
		exit;
	}
	if ($file['error'] !== 0) {
		echo json_encode([
			"success" => false,
			"message" => "Erreur upload"
		]);
		exit;
	}
	if ($file["size"] > $allowed_size) {
		echo json_encode([
			"success" => false,
			"message" => "La taille de fichier autorisé est de " . $allowed_size . " octets"
		]);
		exit;
	}


	$uploadDir = realpath(__DIR__ . "/uploads") . "/";
	$extension = pathinfo($file["name"], PATHINFO_EXTENSION);
	$filename = time() . "_" . uniqid() . "_" . mt_rand(1000, 9999) . "." . $extension;
	$targetPath = $uploadDir . $filename;

	if (move_uploaded_file($file["tmp_name"], $targetPath)) {
		echo json_encode([
			"success" => true,
			"message" => "Upload réussi",
			"targetPath" => "../../rest/api/v1/uploads/" . $filename
		]);
	}else {
		echo json_encode([
			"success" => false,
			"message" => "Échec upload"
		]);
	}
}
function search_photo($conn) {
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
	$letters = $data["letters"];
	if(!isset($letters)) {
		echo json_encode([
			"success" => false,
			"message" => "Données manquantes pour la recherche photo"
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
	$query = "SELECT * FROM photos WHERE title LIKE :search";
	$search = "%" . $letters . "%";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":search", $search);
	$success = $stmt->execute();
	$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la recherche photo"
		]);
		exit;
	}
	if (empty($photos)) {
		echo json_encode([
			"success" => false,
			"message" => "Élément non trouvé"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Photo trouvée",
		"photos_result" => $photos
	], JSON_PRETTY_PRINT);
}
function get_photo($conn) {
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
			"success" => false,
			"message" => "JSON invalide pour l'obtension de la photo"
		]);
		exit;
	}
	$photo_id = $data["photo_id"];
	if(!isset($photo_id)) {
		echo json_encode([
			"success" => false,
			"message" => "photo_id manquant, pour l'obtension de la photo"
		]);
		exit;
	}
	$query = "SELECT * FROM photos WHERE id=:photo_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":photo_id", $photo_id);
	$success = $stmt->execute();
	$photo = $stmt->fetch(PDO::FETCH_ASSOC);
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec pour l'obtension de la photo"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Photo trouvée",
		"photo" => $photo
	]);
}