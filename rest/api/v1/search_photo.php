<?php
require "../../config/database.php";

$db = new Database();
$conn = $db->getConnection();

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
	case 'POST':
		if($_GET["action"] === "search_photo") {
			search_photo($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}

function search_photo($conn) {
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
	$letters = $data["letters"];
	if(!isset($letters)) {
		echo json_encode([
			"success" => false,
			"message" => "Données manquantes pour la recherche photo"
		]);
		exit;
	}
	if($letters === "") {
		echo json_encode([
			"success" => false,
			"message" => "Veuillez entrer une lettre"
		]);
		exit;
	}
	$query = "SELECT * FROM photos WHERE title LIKE :search";
	$search = "%" . $letters . "%";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":search", $search);
	$success = $stmt->execute();
	$photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec de la recherche photo"
		]);
		exit;
	}
	if (empty($photos)) {
		echo json_encode([
			"success" => false,
			"message" => "Élément non trouvé"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Photo trouvée",
		"photos_result" => $photos
	], JSON_PRETTY_PRINT);
}