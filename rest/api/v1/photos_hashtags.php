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
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}
function photos_hashtags($conn) {
	requireAuth();
	
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