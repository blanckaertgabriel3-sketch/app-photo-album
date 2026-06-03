<?php

header("Access-Control-Allow-Origin: *");

session_start();
if(!$_SESSION["user_id"]) {
    echo json_encode([
        "message" => "Utilisateur non connecté"
    ]);
    exit;
}

$allowed_size = 500000;
$file = $_FILES['file'];
if (!isset($file)) {
    echo json_encode([
        "message" => "Aucun fichier reçu"
    ]);
    exit;
}
if ($file['error'] !== 0) {
    echo json_encode([
        "message" => "Erreur upload"
    ]);
    exit;
}
if ($file["size"] > $allowed_size) {
    echo json_encode([
        "message" => "La taille de fichier autorisé est de " . $allowed_size . " octets"
    ]);
    exit;
}

$uploadDir = __DIR__ . "/uploads/";

$filename = basename($file["name"]);
$targetPath = $uploadDir . $filename;
if (move_uploaded_file($file["tmp_name"], $targetPath)) {
    echo json_encode([
        "success" => true,
        "message" => "Upload réussi",
        "targetPath" => $targetPath
        
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Échec upload"
    ]);
}