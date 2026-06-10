<?php

require "../../config/database.php";

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
	case 'POST':
		if($_GET["action"] === "albums_hashtags") {
			albums_hashtags($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}
function albums_hashtags($conn) {
	session_start();

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
			"message" => "Échec de lien album-hashtag"
		]);
		exit;
	}
	echo json_encode([
		"message" => "Lien album-hashtag, réussi"
	]);
}