<?php

function requireAuth(): int
{
	if (empty($_SESSION["user_id"])) {
		http_response_code(401);

		echo json_encode([
			"success" => false,
			"message" => "Unauthorized"
		]);

		exit;
	}

	return (int) $_SESSION["user_id"];
}

// if (!isset($_SESSION["user_id"])) {
// 	echo json_encode([
// 		"success" => false,
// 		"message" => "Utilisateur non connecté"
// 	]);
// 	exit;
// }
// $query = "SELECT id FROM users WHERE id = :id";
// $stmt = $conn->prepare($query);
// $stmt->bindParam(":id", $_SESSION["user_id"]);
// $stmt->execute();
// if (!$stmt->fetch()) {
// 	session_destroy();
// 	echo json_encode([
// 		"success" => false,
// 		"message" => "Utilisateur non connecté"
// 	]);
// 	exit;
// }