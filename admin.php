<?php
session_start();

// Vérification si l'utilisateur connecté est un administrateur
if ( $_SESSION['userDroit'] != 'admin' and $_SESSION['userDroit'] != 'admin') {
    header('Location: login.php');
    exit;
}
include 'header.php';
include 'menu.php';

// Activer ou désactiver le compte utilisateur
if (isset($_POST['toggle_status'])) {
    $userId = $_POST['user_id'];
    $newStatus = $_POST['new_status'];
    $sqlUpdateStatus = "UPDATE users SET etat = ? WHERE id = ?";
    $stmt = $conn->prepare($sqlUpdateStatus);
    $stmt->bind_param("ii", $newStatus, $userId);
    
    if ($stmt->execute()) {
        echo '<script>alert("Le statut de l\'utilisateur a été mis à jour avec succès.");</script>';
    } else {
        echo '<script>alert("Erreur lors de la mise à jour du statut de l\'utilisateur.");</script>';
    }
    $stmt->close();
}

// Récupérer la liste des utilisateurs
$role = 'admin';
$sqlUsers = "SELECT id, nom, email, etat FROM users WHERE droit != ?";
$stmt = $conn->prepare($sqlUsers);
$stmt->bind_param("s",$role);
$stmt->execute();
$resultUsers = $stmt->get_result();

// $resultUsers = $conn->query($sqlUsers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  
<style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            height: 100vh; /* Full height */
            position: fixed; /* Stay in place */
            top: 56px; /* Below the header */
            left: 0;
            padding-top: 20px;
            background-color: #343a40;
        }
        .sidebar a {
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            display: block;
            transition-duration: 0,5s;
            transition-property: transform;
        }
        .sidebar a:hover {
            background-color: #495057;
            transition:.5s ease all;
        }
        .content {
    margin-top: 80px; /* Ajustement si la barre est en position fixed */
    margin-left: 200px; /* Compense la largeur de la barre latérale */
    padding: 20px;}
        .header {
            background-color: #209EF7;
            color: white;
            padding: 15px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
    </style>
    </head>
    <body>

    <div class="sidebar">
    <h4 class="text-white text-center p-2 m-2">Menu</h4>
    <a href="index.php?pp=admin">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house-check" viewBox="0 0 16 16">
  <path d="M7.293 1.5a1 1 0 0 1 1.414 0L11 3.793V2.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v3.293l2.354 2.353a.5.5 0 0 1-.708.708L8 2.207l-5 5V13.5a.5.5 0 0 0 .5.5h4a.5.5 0 0 1 0 1h-4A1.5 1.5 0 0 1 2 13.5V8.207l-.646.647a.5.5 0 1 1-.708-.708z"/>
  <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.707l.547.547 1.17-1.951a.5.5 0 1 1 .858.514"/>
</svg> Home
    </a>
    <a href="index.php?pp=operation">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/>
</svg> Operation
</a>
</div>

<div class="header">
    <div class="container d-flex justify-content-between align-items-center">
        <h2>Espace de l'admin</h2>
        <a href="logout.php" class="btn btn-dark">Déconnexion</a>
    </div>
</div>

</body>
</html>


<div class="container mt-5"style="margin-left: 200px;">
    <br><br><br><br>
    <?php
    // var_dump($_SESSION);
    ?>
    <h2 class="text-center">Gestion des Utilisateurs</h2>
    
    <!-- Liste des utilisateurs et option d'activer/désactiver -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID Utilisateur</th>
                <th>Nom</th>
                <th>Email</th>
                <th>État</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $resultUsers->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['nom']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['etat'] == 1 ? 'Activé' : 'Désactivé' ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                        <input type="hidden" name="new_status" value="<?= $user['etat'] == 1 ? 0 : 1 ?>">
                        <button type="submit" name="toggle_status" class="btn <?= $user['etat'] == 1 ? 'btn-danger' : 'btn-success' ?>">
                            <?= $user['etat'] == 1 ? 'Désactiver' : 'Activer' ?>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php
// include 'footer.php';
$conn->close();
?>


