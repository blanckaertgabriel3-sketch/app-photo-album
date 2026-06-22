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
		if($_GET["action"] === "photos_hashtags") {
			photos_hashtags($conn);
		}
		elseif($_GET["action"] === "sync_photos_hashtags") {
			sync_photos_hashtags($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}
function photos_hashtags($conn) {
	requireAuth($conn);
	
	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer lien photos_albums"
		]);
		exit;
	}
	$hashtag_id =  $data["hashtag_id"];
	$photo_id =  $data["photo_id"];

	if(!isset($photo_id)) {
		echo json_encode([
			"message" => "Photo manquante pour le lien photos_albums"
		]);
		exit;
	}
	if(!isset($hashtag_id)) {
		echo json_encode([
			"message" => "Hashtag manquant pour le lien photos_albums"
		]);
		exit;
	}
	$query = "INSERT INTO photos_hashtags (hashtag_id, photo_id) VALUES (:hashtag_id, :photo_id)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":hashtag_id", $hashtag_id);
	$stmt->bindParam(":photo_id", $photo_id);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de lien photos_albums"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Lien photos_albums, réussi"
	]);
}
function sync_photos_hashtags($conn) {

	requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);

	$photo_id = $data["photo_id"] ?? null;
	$hashtag_names = $data["hashtag_names"] ?? [];

	if(!$photo_id) {
		echo json_encode([
			"success" => false,
			"message" => "photo_id manquant"
		]);
		exit;
	}

	try {

		$conn->beginTransaction();

		// Supprime les anciennes associations.
		$stmt = $conn->prepare("DELETE FROM photos_hashtags WHERE photo_id=:photo_id");
		$stmt->bindParam(":photo_id", $photo_id);
		$stmt->execute();

		// Ajoute les nouvelles associations.
		foreach($hashtag_names as $name) {

			$name = trim($name);

			if($name === "") {
				continue;
			}

			$stmt = $conn->prepare("SELECT id FROM hashtags WHERE name=:name");
			$stmt->bindParam(":name", $name);
			$stmt->execute();

			$hashtag = $stmt->fetch(PDO::FETCH_ASSOC);

			if(!$hashtag) {

				$stmt = $conn->prepare("INSERT INTO hashtags (name) VALUES (:name)");
				$stmt->bindParam(":name", $name);
				$stmt->execute();

				$hashtag_id = $conn->lastInsertId();

			} else {

				$hashtag_id = $hashtag["id"];
			}

			$stmt = $conn->prepare("INSERT IGNORE INTO photos_hashtags (photo_id, hashtag_id) VALUES (:photo_id, :hashtag_id)");
			$stmt->bindParam(":photo_id", $photo_id);
			$stmt->bindParam(":hashtag_id", $hashtag_id);
			$stmt->execute();
		}

		$conn->commit();

		echo json_encode([
			"success" => true,
			"message" => "Hashtags de la photo synchronisés"
		]);

	} catch(Exception $e) {

		$conn->rollBack();

		echo json_encode([
			"success" => false,
			"message" => "Échec de la synchronisation"
		]);
	}
}