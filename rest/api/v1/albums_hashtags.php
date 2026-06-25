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
		if($_GET["action"] === "albums_hashtags") {
			albums_hashtags($conn);
		}
		elseif($_GET["action"] === "sync_albums_hashtags") {
			sync_albums_hashtags($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}
function albums_hashtags($conn) {
	
	requireAuth($conn);
	
	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer lien album-hashtag"
		]);
		exit;
	}
	$hashtag_id =  $data["hashtag_id"];
	$album_id =  $data["album_id"];

	if(!isset($album_id)) {
		echo json_encode([
			"message" => "Album manquant pour le lien album-hashtag"
		]);
		exit;
	}
	if(!isset($hashtag_id)) {
		echo json_encode([
			"message" => "Hashtag manquant pour le lien album-hashtag"
		]);
		exit;
	}
	$query = "INSERT INTO albums_hashtags (hashtag_id, album_id) VALUES (:hashtag_id, :album_id)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":hashtag_id", $hashtag_id);
	$stmt->bindParam(":album_id", $album_id);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de lien album-hashtag"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Lien album-hashtag, réussi"
	]);
}
function sync_albums_hashtags($conn) {

	requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);

	$album_id = $data["album_id"] ?? null;
	$hashtag_names = $data["hashtag_names"] ?? [];

	if(!$album_id) {
		echo json_encode([
			"success" => false,
			"message" => "album_id manquant"
		]);
		exit;
	}

	try {

		$conn->beginTransaction();

		if(empty($hashtag_names)) {

			// Supprime toutes les associations.
			$stmt = $conn->prepare("DELETE FROM albums_hashtags WHERE album_id=:album_id");
			$stmt->bindParam(":album_id", $album_id);
			$stmt->execute();

		} else {

			// Supprime uniquement les hashtags retirés de la liste.
			$placeholders = implode(",", array_fill(0, count($hashtag_names), "?"));

			$query = "
				DELETE ah FROM albums_hashtags ah
				INNER JOIN hashtags h ON ah.hashtag_id = h.id
				WHERE ah.album_id = ?
				AND h.name NOT IN ($placeholders)
			";

			$stmt = $conn->prepare($query);

			$params = array_merge([$album_id], $hashtag_names);

			$stmt->execute($params);
		}
		// insérer les hashtags manquants
		if (!empty($hashtag_names)) {
			foreach ($hashtag_names as $name) {
				$stmt = $conn->prepare("
					INSERT IGNORE INTO albums_hashtags (album_id, hashtag_id)
					SELECT :album_id, id FROM hashtags WHERE name = :name
				");
				$stmt->bindParam(":album_id", $album_id);
				$stmt->bindParam(":name", $name);
				$stmt->execute();
			}
		}

		$conn->commit();

		echo json_encode([
			"success" => true,
			"message" => "Hashtags de l'album synchronisés"
		]);

	} catch(Exception $e) {

		$conn->rollBack();

		echo json_encode([
			"success" => false,
			"message" => "Échec de la synchronisation des hashtags"
		]);
	}
}