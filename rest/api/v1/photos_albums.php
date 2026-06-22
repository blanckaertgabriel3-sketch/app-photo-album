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
		if($_GET["action"] === "photos_albums") {
			photos_albums($conn);
		}
		elseif($_GET["action"] === "get_photos_albums") {
			get_photos_albums($conn);
		}
		elseif($_GET["action"] === "sync_photos_albums") {
			sync_photos_albums($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}
function photos_albums($conn) {

	requireAuth($conn);

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
		"success" => true,
		"message" => "Les photos ont été ajoutée"
	]);
}
function get_photos_albums($conn) {

	requireAuth($conn);

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
	$query = "SELECT * FROM photos_albums pa JOIN photos p ON p.id = pa.photo_id WHERE pa.album_id=:album_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":album_id", $album_id);
	$success = $stmt->execute();
	if(!$success) {
		json_encode([
			"success" => false,
			"message" => "Échec de la récupération du contenu d'album"
		]);
	}
	$photos_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode([
		"success" => true,
		"message" => "Composition de l'album trouvé",
		"photos_albums" => $photos_albums
	]);
}
function sync_photos_albums($conn) {

	requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);

	$album_id = $data["album_id"] ?? null;
	$photos = $data["photos"] ?? [];

	if(!$album_id) {
		echo json_encode([
			"success" => false,
			"message" => "album_id manquant"
		]);
		exit;
	}

	try {

		$conn->beginTransaction();

		// Supprime les anciennes associations.
		$stmt = $conn->prepare("DELETE FROM photos_albums WHERE album_id=:album_id");
		$stmt->bindParam(":album_id", $album_id);
		$stmt->execute();

		// Ajoute les nouvelles associations.
		foreach($photos as $entry) {

			$photo_id = $entry["photo_id"] ?? null;
			$display_order = $entry["display_order"] ?? 0;

			if(!$photo_id) {
				continue;
			}

			$stmt = $conn->prepare("INSERT INTO photos_albums (photo_id, album_id, display_order) VALUES (:photo_id, :album_id, :display_order)");
			$stmt->bindParam(":photo_id", $photo_id);
			$stmt->bindParam(":album_id", $album_id);
			$stmt->bindParam(":display_order", $display_order);
			$stmt->execute();
		}

		$conn->commit();

		echo json_encode([
			"success" => true,
			"message" => "Photos de l'album synchronisées"
		]);

	} catch(Exception $e) {

		$conn->rollBack();

		echo json_encode([
			"success" => false,
			"message" => "Échec de la synchronisation des photos"
		]);
	}
}