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
		if($_GET["action"] === "create_album") {
			create_album($conn);
		}
		elseif($_GET["action"] === "get_albums") {
			get_albums($conn);
		}
		elseif($_GET["action"] === "get_album_full") {
			get_album_full($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}

function create_album($conn) {
	
	$owner_id = requireAuth();;


	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer un album"
		]);
		exit;
	}
	$title = trim($data["title"]);
	$description = trim($data["description"]);
	$creation_date = $data["creation_date"];
	$messages_allowed = $data["messages_allowed"];
	$restriction = $data["restriction"];

	if(!isset($title) || $title === "") {
		echo json_encode([
			"message" => "Titre manquant"
		]);	
		exit;
	}
	if(!isset($description) || $description === "") {
		echo json_encode([
			"message" => "Description manquante"
		]);	
		exit;
	}
	if(!isset($messages_allowed)) {
		echo json_encode([
			"message" => "Autorisation de messages manquante"
		]);	
		exit;
	}
	if(!isset($creation_date)) {
		echo json_encode([
			"message" => "Date de création manquante"
		]);	
		exit;
	}
	if(!isset($restriction)) {
		echo json_encode([
			"message" => "Restriction manquante"
		]);	
		exit;
	}
	//check if title already exist.
	$query = "SELECT title FROM albums WHERE title=:title";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":title", $title);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de vérification du titre"
		]);
		exit;
	}
	$existing_title = $stmt->fetch(PDO::FETCH_ASSOC);
	if($existing_title) {
		echo json_encode([
			"message" => "Ce titre est déjà utilisé"
		]);
		exit;
	}
	// create album.
	$query = "INSERT INTO albums (title, owner_id, description, creation_date, messages_allowed, restriction) VALUES (:title, :owner_id, :description, :creation_date, :messages_allowed, :restriction)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":owner_id", $owner_id);
	$stmt->bindParam(":description", $description);
	$stmt->bindParam(":creation_date", $creation_date);
	$stmt->bindParam(":messages_allowed", $messages_allowed);
	$stmt->bindParam(":restriction", $restriction);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la création d'album"
		]);
		exit;
	}
	$album_id = $conn->lastInsertId();
	echo json_encode([
		"success" => true,
		"message" => "Album créée",
		"album_id" => $album_id
	]);
}
function get_albums($conn) {
	
	requireAuth();

	$query = "SELECT * FROM albums WHERE restriction = 'public' ORDER BY creation_date DESC";
	$stmt = $conn->prepare($query);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec récupération des albums"
		]);
		exit;
	}
	$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode([
		"success" => true,
		"message" => "Albums trouvés",
		"albums" => $albums
	], JSON_PRETTY_PRINT);
}
function get_album_full($conn) {
	requireAuth();

	// get albums order by date
	$query = "SELECT * FROM albums WHERE restriction = 'public' ORDER BY creation_date DESC";
	$stmt = $conn->prepare($query);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec récupération des albums"
		]);
		exit;
	}
	
	$rows_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(empty($rows_albums)) {
		echo json_encode([
			"success" => false,
			"message" => "Aucun album public en votre possession."
		]);
		exit;
	}
	
	// get photos_id in albums
	$albums_ids = array_column($rows_albums, "id");
	if(empty($albums_ids)) {
		json_encode([
			"success" => false,
			"message" => "Aucun id d'album"
		]);
		exit;
	}
	$placeholders = implode(",", array_fill(0, count($albums_ids), "?"));
	$query = "SELECT * FROM photos_albums WHERE album_id IN ($placeholders)";
	$stmt = $conn->prepare($query);
	$success = $stmt->execute($albums_ids);
	if(!$success) {
		json_encode([
			"success" => false,
			"message" => "Échec de la récupération du contenu d'album"
		]);
		exit;
	}
	$rows_photos_albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// get photo data
	$photos_ids = array_column($rows_photos_albums, "photo_id");
	$placeholders = implode(",", array_fill(0, count($photos_ids), "?"));
	$query = "SELECT * FROM photos WHERE id IN ($placeholders)";
	$stmt = $conn->prepare($query);
	$success = $stmt->execute($photos_ids);
	if(!$success) {
		json_encode([
			"success" => false,
			"message" => "Échec de la récupération des données de photo"
		]);
		exit;
	}
	$rows_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// get albums_hashtags
	$placeholders = implode(",", array_fill(0, count($albums_ids), "?"));
	$query = "
		SELECT albums_hashtags.album_id, hashtags.id AS hashtag_id, hashtags.name AS hashtag_name
		FROM albums_hashtags
		INNER JOIN hashtags ON albums_hashtags.hashtag_id = hashtags.id
		WHERE albums_hashtags.album_id IN ($placeholders)
	";
	$stmt = $conn->prepare($query);
	$success = $stmt->execute($albums_ids);
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la récupération des hashtags d'albums"
		]);
		exit;
	}
	$rows_albums_hashtags = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// get photos_hashtags
	if(!empty($photos_ids)) {
		$placeholders = implode(",", array_fill(0, count($photos_ids), "?"));
		$query = "
			SELECT photos_hashtags.photo_id, hashtags.id AS hashtag_id, hashtags.name AS hashtag_name
			FROM photos_hashtags
			INNER JOIN hashtags ON photos_hashtags.hashtag_id = hashtags.id
			WHERE photos_hashtags.photo_id IN ($placeholders)
		";
		$stmt = $conn->prepare($query);
		$success = $stmt->execute($photos_ids);
		if(!$success) {
			echo json_encode([
				"success" => false,
				"message" => "Échec de la récupération des hashtags des photos"
			]);
			exit;
		}
		$rows_photos_hashtags = $stmt->fetchAll(PDO::FETCH_ASSOC);
	} else {
		$rows_photos_hashtags = [];
	}

	echo json_encode([
		"success" => true,
		"message" => "Albums trouvés",
		"rows_albums" => $rows_albums,
		"rows_photos_albums" => $rows_photos_albums,
		"rows_photos" => $rows_photos,
		"rows_albums_hashtags" => $rows_albums_hashtags,
		"rows_photos_hashtags" => $rows_photos_hashtags
	], JSON_PRETTY_PRINT);
}