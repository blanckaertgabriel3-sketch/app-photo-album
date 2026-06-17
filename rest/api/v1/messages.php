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
		if($_GET["action"] === "create") {
			create($conn);
		}
		elseif($_GET["action"] === "get_message") {
			get_message($conn);
		}
		break;
	
	default:
		echo json_encode([
			"message" => "Requête invalide"
		]);
		break;
}
function create($conn) {
	
	requireAuth();
	
	$author_id =  $_SESSION["user_id"];
	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer un message"
		]);
		exit;
	}
	$message =  $data["message"];
	$creation_date =  $data["creation_date"];
	$invitation_format =  $data["invitation_format"];

	if(!isset($message)) {
		echo json_encode([
			"message" => "message manquant"
		]);
		exit;
	}
	if(!isset($creation_date)) {
		echo json_encode([
			"message" => "Date de création manquante pour créer un message"
		]);
		exit;
	}
	if(!isset($invitation_format)) {
		echo json_encode([
			"message" => "Format du message manquant"
		]);
		exit;
	}
	$query = "INSERT INTO messages (author_id, message, creation_date, invitation_format) VALUES (:author_id, :message, :creation_date, :invitation_format)";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":author_id", $author_id);
	$stmt->bindParam(":message", $message);
	$stmt->bindParam(":creation_date", $creation_date);
	$stmt->bindParam(":invitation_format", $invitation_format);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec création message"
		]);
		exit;
	}
	echo json_encode([
		"success" => true,
		"message" => "Message créée"
	]);
}
function get_message($conn) {
	
	requireAuth();

	$data = json_decode(file_get_contents("php://input"), true);
	if(!$data) {
		echo json_encode([
			"message" => "json invalide pour créer lien message"
		]);
		exit;
	}
	$message_id =  $data["message_id"];

	if(!isset($message_id)) {
		echo json_encode([
			"message" => "identifiant message manquant"
		]);
		exit;
	}
	$query = "SELECT * FROM messages WHERE id=:message_id";
	$stmt = $conn->prepare($query);
	$stmt->bindParam(":message_id", $message_id);
	$success = $stmt->execute();
	if(!$success) {
		echo json_encode([
			"success" => false,
			"message" => "Échec récupération message"
		]);
		exit;
	}
	$message_data = $stmt->fetch(PDO::FETCH_ASSOC);
	echo json_encode([
		"success" => true,
		"message" => "Message trouvé",
		"message_data" => $message_data
	], JSON_PRETTY_PRINT);
}