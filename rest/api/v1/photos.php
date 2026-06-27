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
		elseif($_GET["action"] === "update_photo") {
			update_photo($conn);
		}
		elseif($_GET["action"] === "delete_photo") {
			delete_photo($conn);
		}
		elseif($_GET["action"] === "get_photo_hashtags") {
			get_photo_hashtags($conn);
		}
		break;
	
	default:
		echo json_encode(["message" => "Requête invalide"]);
		break;
}

function create_photo($conn) {
	
	$user_id = requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"),true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer une photo"
		]);
		exit;
	}
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
	
	requireAuth($conn);

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
	$allowed_types = [
		"image/jpeg",
		"image/png",
		"image/webp",
		"image/gif"
	];

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mime_type = $finfo->file($file["tmp_name"]);

	if (!in_array($mime_type, $allowed_types, true)) {
		echo json_encode([
			"success" => false,
			"message" => "Type de fichier interdit"
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
	$user_id = requireAuth($conn);
	
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
	$query = "SELECT * FROM photos WHERE title LIKE :search AND user_id = :user_id";
	$search = "%" . $letters . "%";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":search", $search);
	$stmt->bindParam(":user_id", $user_id);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la recherche photo"
		]);
		exit;
	}
	$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
	requireAuth($conn);
	
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
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec pour l'obtension de la photo"
		]);
		exit;
	}
	$photo = $stmt->fetch(PDO::FETCH_ASSOC);
	echo json_encode([
		"success" => true,
		"message" => "Photo trouvée",
		"photo" => $photo
	]);
}
function update_photo($conn) {

	$user_id = requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"success" => false,
			"message" => "JSON invalide pour modifier la photo"
		]);
		exit;
	}

	$photo_id = $data["photo_id"] ?? null;
	$title = trim($data["title"] ?? "");
	$description = trim($data["description"] ?? "");
	$messages_allowed = $data["messages_allowed"] ?? null;
	$restriction = $data["restriction"] ?? null;

	if(!$photo_id) {
		echo json_encode([
			"success" => false,
			"message" => "photo_id manquant"
		]);
		exit;
	}

	if($title === "") {
		echo json_encode([
			"success" => false,
			"message" => "Titre manquant"
		]);
		exit;
	}

	if(!isset($restriction)) {
		echo json_encode([
			"success" => false,
			"message" => "restriction manquante"
		]);
		exit;
	}

	// Vérifie que la photo appartient à l'utilisateur.
	$stmt = $conn->prepare("SELECT user_id FROM photos WHERE id=:photo_id");
	$stmt->bindParam(":photo_id", $photo_id);
	$stmt->execute();

	$photo = $stmt->fetch(PDO::FETCH_ASSOC);

	if(!$photo) {
		echo json_encode([
			"success" => false,
			"message" => "Photo introuvable"
		]);
		exit;
	}

	// Vérifie que le titre n'est pas déjà utilisé.
	$stmt = $conn->prepare("SELECT id FROM photos WHERE title=:title AND id != :photo_id");
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":photo_id", $photo_id);
	$stmt->execute();

	if($stmt->fetch()) {
		echo json_encode([
			"success" => false,
			"message" => "Ce titre est déjà utilisé"
		]);
		exit;
	}

	$query = "UPDATE photos SET title=:title, description=:description, messages_allowed=:messages_allowed, restriction=:restriction WHERE id=:photo_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":description", $description);
	$stmt->bindParam(":messages_allowed", $messages_allowed);
	$stmt->bindParam(":restriction", $restriction);
	$stmt->bindParam(":photo_id", $photo_id);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la modification"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Photo modifiée"
	]);
}

function delete_photo($conn) {

	$user_id = requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);
	$photo_id = $data["photo_id"] ?? null;

	if(!$photo_id) {
		echo json_encode([
			"success" => false,
			"message" => "photo_id manquant"
		]);
		exit;
	}

	// Vérifie que la photo appartient à l'utilisateur.
	$stmt = $conn->prepare("SELECT user_id FROM photos WHERE id=:photo_id");
	$stmt->bindParam(":photo_id", $photo_id);
	$stmt->execute();

	$photo = $stmt->fetch(PDO::FETCH_ASSOC);

	if(!$photo) {
		echo json_encode([
			"success" => false,
			"message" => "Photo introuvable"
		]);
		exit;
	}

	// ON DELETE CASCADE supprime automatiquement les relations.
	$stmt = $conn->prepare("DELETE FROM photos WHERE id=:photo_id");
	$stmt->bindParam(":photo_id", $photo_id);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la suppression"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Photo supprimée"
	]);
}

function get_photo_hashtags($conn) {

	requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);
	$photo_id = $data["photo_id"] ?? null;

	if(!$photo_id) {
		echo json_encode([
			"success" => false,
			"message" => "photo_id manquant"
		]);
		exit;
	}

	$query = "SELECT hashtags.id, hashtags.name FROM photos_hashtags INNER JOIN hashtags ON photos_hashtags.hashtag_id = hashtags.id WHERE photos_hashtags.photo_id = :photo_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":photo_id", $photo_id);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec récupération hashtags"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Hashtags trouvés",
		"hashtags" => $stmt->fetchAll(PDO::FETCH_ASSOC)
	]);
}