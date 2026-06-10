<?php

header("Access-Control-Allow-Origin: *");
require "../../config/database.php";

$db = new Database();
$conn = $db->getConnection();
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

$allowed_size = 500000;
$file = $_FILES['file'];
if (!isset($file)) {
    echo json_encode([
        "success" => false,
        "message" => "Aucun fichier reçu"
    ]);
    exit;
}
if ($file['error'] !== 0) {
    echo json_encode([
        "success" => false,
        "message" => "Erreur upload"
    ]);
    exit;
}
if ($file["size"] > $allowed_size) {
    echo json_encode([
        "success" => false,
        "message" => "La taille de fichier autorisé est de " . $allowed_size . " octets"
    ]);
    exit;
}


$uploadDir = realpath(__DIR__ . "/uploads") . "/";
$extension = pathinfo($file["name"], PATHINFO_EXTENSION);
$filename = time() . "_" . uniqid() . "_" . mt_rand(1000, 9999) . "." . $extension;
$targetPath = $uploadDir . $filename;

if (move_uploaded_file($file["tmp_name"], $targetPath)) {
    echo json_encode([
        "success" => true,
        "message" => "Upload réussi",
        "targetPath" => "../../rest/api/v1/uploads/" . $filename
    ]);
}else {
    echo json_encode([
        "success" => false,
        "message" => "Échec upload"
    ]);
}