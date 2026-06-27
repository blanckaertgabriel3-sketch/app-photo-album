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
		elseif($_GET["action"] === "search_album") {
			search_album($conn);
		}
		elseif($_GET["action"] === "update_album") {
			update_album($conn);
		}
		elseif($_GET["action"] === "delete_album") {
			delete_album($conn);
		}
		elseif($_GET["action"] === "get_album_hashtags") {
			get_album_hashtags($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}

function create_album($conn) {
	
	$owner_id = requireAuth($conn);;


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
	
	requireAuth($conn);

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
	requireAuth($conn);

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
			"message" => "Aucun album public existant."
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
function search_album($conn) {

	$user_id = requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);
	$letters = $data["letters"] ?? "";

	if($letters === "") {
		echo json_encode([
			"success" => false,
			"message" => "Veuillez entrer une lettre"
		]);
		exit;
	}

	$search = "%" . $letters . "%";

	$query = "
	SELECT DISTINCT a.*
	FROM albums a
	LEFT JOIN albums_collaborators ac
		ON a.id = ac.album_id
	WHERE
		a.title LIKE :search
		AND (
			a.owner_id = :user_id
			OR ac.user_id = :user_id
		)
	ORDER BY a.creation_date DESC
	";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":search", $search);
	$stmt->bindParam(":user_id", $user_id);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la recherche"
		]);
		exit;
	}

	$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if(empty($albums)) {
		echo json_encode([
			"success" => false,
			"message" => "Aucun album trouvé"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Albums trouvés",
		"albums_result" => $albums
	]);
}

function update_album($conn) {

	$owner_id = requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"success" => false,
			"message" => "JSON invalide pour modifier l'album"
		]);
		exit;
	}

	$album_id = $data["album_id"] ?? null;
	$title = trim($data["title"] ?? "");
	$description = trim($data["description"] ?? "");
	$messages_allowed = $data["messages_allowed"] ?? null;
	$restriction = $data["restriction"] ?? null;

	if(!$album_id) {
		echo json_encode([
			"success" => false,
			"message" => "album_id manquant"
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

	if(!isset($messages_allowed)) {
		echo json_encode([
			"success" => false,
			"message" => "messages_allowed manquant"
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

	// Vérifie que l'album appartient à l'utilisateur.
	$stmt = $conn->prepare("SELECT owner_id FROM albums WHERE id=:album_id");
	$stmt->bindParam(":album_id", $album_id);
	$stmt->execute();

	$album = $stmt->fetch(PDO::FETCH_ASSOC);

	if(!$album) {
		echo json_encode([
			"success" => false,
			"message" => "Album introuvable"
		]);
		exit;
	}

	if($album["owner_id"] != $owner_id) {
		echo json_encode([
			"success" => false,
			"message" => "Non autorisé"
		]);
		exit;
	}

	// Vérifie que le titre n'est pas déjà utilisé.
	$stmt = $conn->prepare("SELECT id FROM albums WHERE title=:title AND id != :album_id");
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":album_id", $album_id);
	$stmt->execute();

	if($stmt->fetch()) {
		echo json_encode([
			"success" => false,
			"message" => "Ce titre est déjà utilisé"
		]);
		exit;
	}

	$query = "UPDATE albums SET title=:title, description=:description, messages_allowed=:messages_allowed, restriction=:restriction WHERE id=:album_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":description", $description);
	$stmt->bindParam(":messages_allowed", $messages_allowed);
	$stmt->bindParam(":restriction", $restriction);
	$stmt->bindParam(":album_id", $album_id);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la modification"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Album modifié"
	]);
}

function delete_album($conn) {

	$owner_id = requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);
	$album_id = $data["album_id"] ?? null;

	if(!$album_id) {
		echo json_encode([
			"success" => false,
			"message" => "album_id manquant"
		]);
		exit;
	}

	// Vérifie que l'album appartient à l'utilisateur.
	$stmt = $conn->prepare("SELECT owner_id FROM albums WHERE id=:album_id");
	$stmt->bindParam(":album_id", $album_id);
	$stmt->execute();

	$album = $stmt->fetch(PDO::FETCH_ASSOC);

	if(!$album) {
		echo json_encode([
			"success" => false,
			"message" => "Album introuvable"
		]);
		exit;
	}

	if($album["owner_id"] != $owner_id) {
		echo json_encode([
			"success" => false,
			"message" => "Non autorisé"
		]);
		exit;
	}

	// ON DELETE CASCADE supprime automatiquement les relations.
	$stmt = $conn->prepare("DELETE FROM albums WHERE id=:album_id");
	$stmt->bindParam(":album_id", $album_id);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la suppression"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Album supprimé"
	]);
}

function get_album_hashtags($conn) {

	requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);
	$album_id = $data["album_id"] ?? null;

	if(!$album_id) {
		echo json_encode([
			"success" => false,
			"message" => "album_id manquant"
		]);
		exit;
	}

	$query = "SELECT hashtags.id, hashtags.name FROM albums_hashtags INNER JOIN hashtags ON albums_hashtags.hashtag_id = hashtags.id WHERE albums_hashtags.album_id = :album_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":album_id", $album_id);

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