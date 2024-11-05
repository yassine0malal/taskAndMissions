<?php
include "condb.php";

function generateCsrfToken() {
return bin2hex(random_bytes(32)); // Génère une chaîne hexadécimale de 64 caractères
}






function storeCsrfToken($conn, $token) {
    $stmt = $conn->prepare("INSERT INTO tokens (nom) VALUES (?)");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    // $stmt->close();
}






function verifyCsrfToken($conn, $token) {
    $stmt = $conn->prepare("SELECT * FROM tokens WHERE nom = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $csrfTokenRecord = $stmt->get_result();
    if ($csrfTokenRecord->num_rows > 0) {
    $data = $csrfTokenRecord->fetch_assoc();

    // Optionnel : supprimer le token après utilisation pour éviter les réutilisations
    // $id=$data['id'];
    $stmt = $conn->prepare("DELETE FROM tokens WHERE id = ?");
    $stmt->bind_param("s",$data['id']);
    $stmt->execute();

    return true; // Token valide
    } else {
    return false; // Token invalide
    }
}
