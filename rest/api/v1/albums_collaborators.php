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
		if($_GET["action"] === "add_collaborator") {
			add_collaborator($conn);
		}
		elseif($_GET["action"] === "remove_collaborator") {
			remove_collaborator($conn);
		}
		elseif($_GET["action"] === "get_collaborators") {
			get_collaborators($conn);
		}
		elseif($_GET["action"] === "sync_collaborators") {
			sync_collaborators($conn);
		}
		break;
	
	default:
		echo json_encode(["message" => "Requête invalide"]);
		break;
}

function add_collaborator($conn) {

	requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);

	$album_id = $data["album_id"] ?? null;
	$username = $data["username"] ?? null;

	if(!$album_id || !$username) {
		echo json_encode([
			"success" => false,
			"message" => "Données manquantes"
		]);
		exit;
	}

	$stmt = $conn->prepare("SELECT id FROM users WHERE name=:username");
	$stmt->bindParam(":username", $username);
	$stmt->execute();

	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if(!$user) {
		echo json_encode([
			"success" => false,
			"message" => "Utilisateur introuvable"
		]);
		exit;
	}

	$stmt = $conn->prepare("INSERT IGNORE INTO albums_collaborators (album_id, user_id) VALUES (:album_id, :user_id)");
	$stmt->bindParam(":album_id", $album_id);
	$stmt->bindParam(":user_id", $user["id"]);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec d'ajout du collaborateur"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Collaborateur ajouté"
	]);
}

function remove_collaborator($conn) {

	requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);

	$album_id = $data["album_id"] ?? null;
	$username = $data["username"] ?? null;

	if(!$album_id || !$username) {
		echo json_encode([
			"success" => false,
			"message" => "Données manquantes"
		]);
		exit;
	}

	$stmt = $conn->prepare("SELECT id FROM users WHERE name=:username");
	$stmt->bindParam(":username", $username);
	$stmt->execute();

	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if(!$user) {
		echo json_encode([
			"success" => false,
			"message" => "Utilisateur introuvable"
		]);
		exit;
	}

	$stmt = $conn->prepare("DELETE FROM albums_collaborators WHERE album_id=:album_id AND user_id=:user_id");
	$stmt->bindParam(":album_id", $album_id);
	$stmt->bindParam(":user_id", $user["id"]);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la suppression du collaborateur"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Collaborateur retiré"
	]);
}

function get_collaborators($conn) {

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

	$query = "SELECT users.id, users.name FROM albums_collaborators INNER JOIN users ON albums_collaborators.user_id = users.id WHERE albums_collaborators.album_id = :album_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":album_id", $album_id);

	if(!$stmt->execute()) {
		echo json_encode([
			"success" => false,
			"message" => "Échec récupération collaborateurs"
		]);
		exit;
	}

	echo json_encode([
		"success" => true,
		"message" => "Collaborateurs trouvés",
		"collaborators" => $stmt->fetchAll(PDO::FETCH_ASSOC)
	]);
}

function sync_collaborators($conn) {

	requireAuth($conn);

	$data = json_decode(file_get_contents("php://input"), true);

	$album_id = $data["album_id"] ?? null;
	$usernames = $data["usernames"] ?? [];

	if(!$album_id) {
		echo json_encode([
			"success" => false,
			"message" => "album_id manquant"
		]);
		exit;
	}

	try {

		$conn->beginTransaction();

		// Supprime les anciens collaborateurs.
		$stmt = $conn->prepare("DELETE FROM albums_collaborators WHERE album_id=:album_id");
		$stmt->bindParam(":album_id", $album_id);
		$stmt->execute();

		// Ajoute les nouveaux collaborateurs.
		foreach($usernames as $username) {

			$stmt = $conn->prepare("SELECT id FROM users WHERE name=:username");
			$stmt->bindParam(":username", $username);
			$stmt->execute();

			$user = $stmt->fetch(PDO::FETCH_ASSOC);

			if(!$user) {
				continue;
			}

			$stmt = $conn->prepare("INSERT IGNORE INTO albums_collaborators (album_id, user_id) VALUES (:album_id, :user_id)");
			$stmt->bindParam(":album_id", $album_id);
			$stmt->bindParam(":user_id", $user["id"]);
			$stmt->execute();
		}

		$conn->commit();

		echo json_encode([
			"success" => true,
			"message" => "Collaborateurs synchronisés"
		]);

	} catch(Exception $e) {

		$conn->rollBack();

		echo json_encode([
			"success" => false,
			"message" => "Échec de la synchronisation des collaborateurs"
		]);
	}
}