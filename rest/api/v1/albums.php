<?php

require "../../config/database.php";

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
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}

function create_album($conn) {
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
	$owner_id = $_SESSION["user_id"];


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
	//check if title already exist.
	$query = "SELECT title FROM albums WHERE title=:title";
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
	// create album.
	$query = "INSERT INTO albums (title, owner_id, description, creation_date, messages_allowed) VALUES (:title, :owner_id, :description, :creation_date, :messages_allowed)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":title", $title);
	$stmt->bindParam(":owner_id", $owner_id);
	$stmt->bindParam(":description", $description);
	$stmt->bindParam(":creation_date", $creation_date);
	$stmt->bindParam(":messages_allowed", $messages_allowed);
	$success = $stmt->execute();
	$album_id = $conn->lastInsertId();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la création d'album"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Album créée",
		"album_id" => $album_id
	]);
}
function get_albums($conn) {
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
	$query = "SELECT * FROM albums ORDER BY creation_date DESC";
	$stmt = $conn->prepare($query);
	$success = $stmt->execute();
	$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!$success) {
		echo json_encode([
			"message" => "Échec récupération des albums"
		]);
		exit;
	}
	echo json_encode([
		"message" => "Albums trouvés",
		"albums" => $albums
	], JSON_PRETTY_PRINT);
}