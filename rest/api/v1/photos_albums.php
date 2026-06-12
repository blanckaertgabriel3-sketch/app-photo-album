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
		if($_GET["action"] === "photos_albums") {
			photos_albums($conn);
		}
		elseif($_GET["action"] === "get_photos_albums") {
			get_photos_albums($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}
function photos_albums($conn) {
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
			"message" => "json invalide pour l'ajout des photos à l'album"
		]);
		exit;
	}
	$photo_id = $data["photo_id"];
	$album_id = $data["album_id"];
	$display_order = $data["display_order"];
	if(!isset($photo_id)) {
		echo json_encode([
			"message" => "Photo manquante"
		]);
		exit;
	}
	if(!isset($album_id)) {
		echo json_encode([
			"message" => "Album manquant"
		]);
		exit;
	}
	if(!isset($display_order)) {
		echo json_encode([
			"message" => "Ordre d'affichage manquant"
		]);
		exit;
	}
	$query = "INSERT INTO photos_albums (photo_id, album_id, display_order) VALUES (:photo_id, :album_id, :display_order)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":photo_id", $photo_id);
	$stmt->bindParam(":album_id", $album_id);
	$stmt->bindParam(":display_order", $display_order);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"message" => "Échec de l'ajout des photos à l'album"
		]);
		exit;
	}
	echo json_encode([
		"message" => "Les photos ont été ajoutée"
	]);
}
function get_photos_albums($conn) {
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
		json_encode([
			"success" => false,
			"message" => "Données invalide pour la récupération du contenu d'album"
		]);
		exit;
	}
	$album_id = $data["album_id"];
	if(!isset($album_id)) {
		json_encode([
			"success" => false,
			"message" => "album_id manquant, pour la récupération du contenu d'album"
		]);
		exit;
	}
	$query = "SELECT * FROM photos_albums WHERE album_id=:album_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":album_id", $album_id);
	$success = $stmt->execute();
	$photos_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!$success) {
		json_encode([
			"success" => false,
			"message" => "Échec de la récupération du contenu d'album"
		]);
	}
	echo json_encode([
		"success" => true,
		"message" => "Composition de l'album trouvé",
		"photos_albums" => $photos_albums
	]);
}