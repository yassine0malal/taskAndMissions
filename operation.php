<?php
session_start();
include 'header.php';
// include 'menu';
if($_SESSION['user_id'] == 16 and $_SESSION['userEmail'] === 'admine@gmail.com' and $_SESSION['userDroit'] === 'admin'){
    $userIdConn = $_SESSION['user_id'];
    $userEmailConn = $_SESSION['userEmail'];
    $userDroitConn = $_SESSION['userDroit'];

}else{
    header("location: login.php");
    exit;
}

// start the code 
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
<?php
// Récupérer les missions partagées
$sqlSharedMissions = "
    SELECT sm.mission_id, sm.user_partage_id, sm.droit, m.nom as mission_name, u.nom as user_name
    FROM shared_mission sm
    JOIN missions m ON sm.mission_id = m.id
    JOIN users u ON sm.user_partage_id = u.id";

$resultSharedMissions = $conn->query($sqlSharedMissions);

// Récupérer les tâches partagées
$sqlSharedTasks = "
    SELECT st.task_id, st.user_partage_id, st.droit, t.nom as task_name, u.nom as user_name
    FROM shared_tasks st
    JOIN tasks t ON st.task_id = t.id
    JOIN users u ON st.user_partage_id = u.id";
$resultSharedTasks = $conn->query($sqlSharedTasks);

// Récupérer des operations 
$sqlOperations = "SELECT * FROM operations";
$resultOperations = $conn->query($sqlOperations);
// $datasOfresultOperations = $resultOperations->fetch_all(MYSQLI_ASSOC);

?>

<br><br><br><br>
<div class="container mt-5" style="margin-left: 200px;">
  

 <!-- Surveillance des missions partagées -->
 <h3 class="mt-5">Modifications sur les Missions Partagées</h3>
    <table class="table table-bordered table-active">
        <thead>
            <tr>
                <th>ID Mission</th>
                <th>Nom de la Mission</th>
                <th>Utilisateur Qui a Reçu la Mission</th>
                <th>Droit Attribué</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($mission = $resultSharedMissions->fetch_assoc()): ?>
            <tr>
                <td><?= $mission['mission_id'] ?></td>
                <td><?= htmlspecialchars($mission['mission_name']) ?></td>
                <td><?= htmlspecialchars($mission['user_name']) ?></td>
                <td><?= htmlspecialchars($mission['droit']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    
   
  <!-- Surveillance des tâches partagées -->
  <h3 class="mt-5" >Modifications sur les Tâches Partagées</h3>
    <table class="table table-bordered table-active">
        <thead>
            <tr>
                <th>ID Tâche</th>
                <th>Nom de la Tâche</th>
                <th>Utilisateur Qui a Reçu la Tâche</th>
                <th>Droit Attribué</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($task = $resultSharedTasks->fetch_assoc()): ?>
            <tr>
                <td><?= $task['task_id'] ?></td>
                <td><?= htmlspecialchars($task['task_name']) ?></td>
                <td><?= htmlspecialchars($task['user_name']) ?></td>
                <td><?= htmlspecialchars($task['droit']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
  <!-- Surveillance des operations sur le missions et les taches : -->
  <h3 class="mt-5" >Modifications des operations des utilisateurs </h3>
    <table class="table table-bordered table-active">
        <thead>
            <tr>
                <th>ID de la personne qui fait l'operation</th>
                <th>Nom de l'operation </th>
                <th>la date et l'heure de l'operation </th>
            </tr>
        </thead>
        <tbody>
            <?php while ($operation = $resultOperations->fetch_assoc()): ?>
            <tr>
                <td><?= $operation['user_id'] ?></td>
                <td><?= htmlspecialchars($operation['operation']) ?></td>
                <td><?= htmlspecialchars($operation['dateheur']) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>