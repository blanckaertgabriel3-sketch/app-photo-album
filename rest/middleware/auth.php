<?php

function requireAuth(PDO $conn): int
{
    if (empty($_SESSION["user_id"])) {
        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Non connecté"
        ]);
        exit;
    }

    $user_id = (int)$_SESSION["user_id"];

    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);

    if (!$stmt->fetch()) {
        session_unset();
        session_destroy();

        http_response_code(401);

        echo json_encode([
            "success" => false,
            "message" => "Session invalide"
        ]);

        exit;
    }

    return $user_id;
}