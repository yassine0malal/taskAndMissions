<?php
session_start();
include 'header.php';
include 'menu.php';
include 'security.php';

$token = generateCsrfToken();
storeCsrfToken($conn,$token);

// Database connection assumed to be included

if (isset($_SESSION['user_id']) && isset($_SESSION['userEmail'])) {
    $userId = $_SESSION['user_id'];
    $userEmail = $_SESSION['userEmail'];

    // Fetch missions for the logged-in user
    $sqlMissions = "SELECT * FROM missions WHERE user_id = ?";
    $stmtMissions = $conn->prepare($sqlMissions);
    $stmtMissions->bind_param("i", $userId);
    $stmtMissions->execute();
    $resultMissions = $stmtMissions->get_result();
    $missions = $resultMissions->fetch_all(MYSQLI_ASSOC);

    // Fetch tasks for each mission
    $missionsWithTasks = [];
    foreach ($missions as $mission) {
        $missionId = $mission['id'];
        $sqlTasks = "SELECT * FROM tasks WHERE mission_id = ?";
        $stmtTasks = $conn->prepare($sqlTasks);
        $stmtTasks->bind_param("i", $missionId);
        $stmtTasks->execute();
        $resultTasks = $stmtTasks->get_result();
        $tasks = $resultTasks->fetch_all(MYSQLI_ASSOC);

        // Store mission with its tasks
        $missionsWithTasks[] = [
            'mission' => $mission,
            'tasks' => $tasks
        ];
    }

    // Fetch shared tasks
    $sqlSharedTasks = "SELECT tasks.*, shared_tasks.droit FROM shared_tasks JOIN tasks ON shared_tasks.task_id = tasks.id WHERE shared_tasks.user_partage_id = ?";
    $stmtSharedTasks = $conn->prepare($sqlSharedTasks);
    $stmtSharedTasks->bind_param("i", $userId);
    $stmtSharedTasks->execute();
    $resultSharedTasks = $stmtSharedTasks->get_result();
    $sharedTasks = $resultSharedTasks->fetch_all(MYSQLI_ASSOC);

    // Fetch shared missions
    $sqlSharedMissions = "SELECT missions.*, shared_mission.droit FROM shared_mission JOIN missions ON shared_mission.mission_id = missions.id WHERE shared_mission.user_partage_id = ?";
    $stmtSharedMissions = $conn->prepare($sqlSharedMissions);
    $stmtSharedMissions->bind_param("i", $userId);
    $stmtSharedMissions->execute();
    $resultSharedMissions = $stmtSharedMissions->get_result();
    $sharedMissions = $resultSharedMissions->fetch_all(MYSQLI_ASSOC);
} else {
    header("location: login.php");
    // exit;
}

function storeOperation($conn , $event){
   
}
?>

<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>


<div class="container mt-5 pt-5">
    <h3 class="text-center text-info">Missions de l'utilisateur</h3>
    
    <!-- Buttons for shared tasks and shared missions -->
    <div class="text-center mb-4">
        <button class="btn btn-primary" onclick="document.getElementById('shared-tasks').style.display='block';">Afficher les Tâches Partagées avec vous </button>
        <button class="btn btn-secondary" onclick="document.getElementById('shared-missions').style.display='block';">Afficher les Missions Partagées avec vous</button>
    </div>

    <!-- Display shared tasks -->
    <div id="shared-tasks" style="display:none;margin-left :200px;" class="alert alert-warning">
        <h4>Tâches Partagées</h4>
        <?php if (empty($sharedTasks)): ?>
            <p>Aucune tâche partagée trouvée.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($sharedTasks as $task): ?>
                    <li>
                        <strong><?= htmlspecialchars($task['nom']) ?></strong>: <?= htmlspecialchars($task['description']) ?> (Droit: <?= htmlspecialchars($task['droit']) ?>)
                        <?php if ($task['droit'] == 'edit'): ?>
                            <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('edit-task-form-<?= $task['id'] ?>').style.display='block';">Modifier</button>
                            <div id="edit-task-form-<?= $task['id'] ?>" style="display:none; margin-top: 10px;">
                                <form method="POST">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <div class="form-group">
                                        <label for="task_name">Nom de la Tâche:</label>
                                        <input type="text" class="form-control" name="task_name" value="<?= htmlspecialchars($task['nom']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="task_description">Description de la Tâche:</label>
                                        <input type="text" class="form-control" name="task_description" value="<?= htmlspecialchars($task['description']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="task_result">Résultat:</label>
                                        <input type="text" class="form-control" name="task_result" value="<?= htmlspecialchars($task['resultat']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="task_priority">Priorité:</label>
                                        <input type="text" class="form-control" name="task_priority" value="<?= htmlspecialchars($task['priorite']) ?>" required>
                                    </div>
                                    <input type="hidden" name="csrf_token_edit_task_partage" value="<?=$token?>">
                                    <button type="submit" name="updateTask" class="btn btn-success">Enregistrer</button>
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-task-form-<?= $task['id'] ?>').style.display='none';">Annuler</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- Display shared missions -->
    <div id="shared-missions" style="display:none; margin-left :200px;" class="alert alert-info">
        <h4>Missions Partagées</h4>
        <?php if (empty($sharedMissions)): ?>
            <p>Aucune mission partagée trouvée.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($sharedMissions as $mission): ?>
                    <li>
                        <strong><?= htmlspecialchars($mission['nom']) ?></strong>: <?= htmlspecialchars($mission['description']) ?> (Droit: <?= htmlspecialchars($mission['droit']) ?>)
                        <?php if ($mission['droit'] == 'edit'): ?>
                            <button type="button" class="btn btn-warning btn-sm" onclick="document.getElementById('edit-form-<?= $mission['id'] ?>').style.display='block';">Modifier</button>
                            <div id="edit-form-<?= $mission['id'] ?>" style="display:none; margin-top: 10px;">
                                <form method="POST">
                                    <input type="hidden" name="mission_id" value="<?= $mission['id'] ?>">
                                    <div class="form-group">
                                        <label for="mission_name">Nom de la Mission:</label>
                                        <input type="text" class="form-control" name="mission_name" value="<?= htmlspecialchars($mission['nom']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="mission_description">Description de la Mission:</label>
                                        <input type="text" class="form-control" name="mission_description" value="<?= htmlspecialchars($mission['description']) ?>" required>
                                    </div>
                                    <input type="hidden" name="csrf_token_share_mision" value="<?=$token?>">
                                    <button type="submit" name="updateMission" class="btn btn-success">Enregistrer</button>
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-form-<?= $mission['id'] ?>').style.display='none';">Annuler</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <h3 class="text-center text-info">Votre Missions Personnelles</h3>

    <!-- Display user's tasks -->
    <div class="container"style="margin-left: 50px;">
        <div class="row">
            <div class="col-sm-12">
                <?php foreach ($missionsWithTasks as $entry): ?>
                    <div class="card mb-3 alert alert-primary">
                        <div class="card-body">
                            <h5 class="card-title"><strong>Le Nom de La mission : </strong><?= htmlspecialchars($entry['mission']['nom']) ?></h5>
                            <p class="card-text"><strong>La description de La mission : </strong><?= htmlspecialchars($entry['mission']['description']) ?></p>
                            <div class="alert alert-secondary">
                                <h6 class="text-primary">Tâches Associées:</h6>
                                <?php if (empty($entry['tasks'])): ?>
                                    <p>Aucune tâche trouvée pour cette mission.</p>
                                <?php else: ?>
                                    <ul>
                                        <?php foreach ($entry['tasks'] as $task): ?>
                                            <li>
                                                <strong><?= htmlspecialchars($task['nom']) ?>&nbsp;&nbsp;</strong>: <?= htmlspecialchars($task['description']) ?> 
                                                &nbsp;&nbsp;(Priorité: <?= htmlspecialchars($task['priorite']) ?>)
                                                
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
        </div>
    </div>
</div>

<?php
// Handle mission update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateMission'])) {

    $csrf_token_share_mision = $_POST['csrf_token_share_mision'];
    if (verifyCsrfToken($conn,$csrf_token_share_mision)) {

    $missionId = $_POST['mission_id'];
    $missionName = htmlspecialchars($_POST['mission_name'], ENT_QUOTES, 'UTF-8');
    $missionDescription = htmlspecialchars($_POST['mission_description'], ENT_QUOTES, 'UTF-8');

    // Update the mission
    $sqlUsers = "SELECT * FROM users WHERE id = ?";
    $stmtUsers = $conn->prepare($sqlUsers);
    $stmtUsers->bind_param("i", $userId);
    $stmtUsers->execute();
    $res = $stmtUsers->get_result();
    $row = $res->fetch_assoc();

    $operationTaskOther = "the user ".$row['nom']." who edit this mission to "."'".$missionName."'";

    
    $sqlUpdateMission = "UPDATE missions SET nom = ?, description = ? WHERE id = ?";
    $stmtUpdateMission = $conn->prepare($sqlUpdateMission);
    $stmtUpdateMission->bind_param("ssi", $missionName, $missionDescription, $missionId);
    
    if ($stmtUpdateMission->execute()) {
        echo '<script type="text/javascript">alert("Mission mise à jour avec succès!");</script>';
        $sqlOperationTask= "INSERT INTO operations (user_id,operation,missionID) values (?,?,?)";
        $stmtOperationTask = $conn->prepare($sqlOperationTask);
        $stmtOperationTask->bind_param("isi",$userId, $operationTaskOther,$missionId);
        $stmtOperationTask->execute();
    } else {
        echo '<script type="text/javascript">alert("Erreur lors de la mise à jour de la mission.");</script>';
    }

    $stmtUpdateMission->close();
    
 
    }else{
        header('location: logout.php');
    }
}

// Handle task update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['updateTask'])) {

    $csrf_token_edit_task_partage = $_POST['csrf_token_edit_task_partage'];
    if (verifyCsrfToken($conn,$csrf_token_edit_task_partage)) {
        
    $taskId = $_POST['task_id'];
    $taskName = htmlspecialchars($_POST['task_name'], ENT_QUOTES, 'UTF-8');
    $taskDescription = htmlspecialchars($_POST['task_description'], ENT_QUOTES, 'UTF-8');
    $taskResult = htmlspecialchars($_POST['task_result'], ENT_QUOTES, 'UTF-8');
    $taskPriority = htmlspecialchars($_POST['task_priority'], ENT_QUOTES, 'UTF-8');

    // Update the task and store the operation 

    $sqlUpdateTask = "UPDATE tasks SET nom = ?, description = ?, resultat = ?, priorite = ? WHERE id = ?";
    $stmtUpdateTask = $conn->prepare($sqlUpdateTask);
    $stmtUpdateTask->bind_param("ssssi", $taskName, $taskDescription, $taskResult, $taskPriority, $taskId);

    $sqlUsers = "SELECT * FROM users WHERE id = ?";
    $stmtUsers = $conn->prepare($sqlUsers);
    $stmtUsers->bind_param("i", $userId);
    $stmtUsers->execute();
    $res = $stmtUsers->get_result();
    $row = $res->fetch_assoc();
    // var_dump($row);

    $operationTaskOther = "the user ".$row['nom']." who edit this task to "."'".$taskName."'";

    
    if ($stmtUpdateTask->execute()) {
        echo '<script type="text/javascript">alert("Tâche mise à jour avec succès!");</script>';
        
        $sqlOperationTask= "INSERT INTO operations (user_id,operation,taskID) values (?,?,?)";
        $stmtOperationTask = $conn->prepare($sqlOperationTask);
        $stmtOperationTask->bind_param("isi",$userId, $operationTaskOther,$taskId,);
        $stmtOperationTask->execute();
    } else {
        echo '<script type="text/javascript">alert("Erreur lors de la mise à jour de la tâche.");</script>';
    }
    $stmtUpdateTask->close();

    
    // Reload the page after update
    header('location: ' . $_SERVER['PHP_SELF']);
    exit;
    }else{
        header('location: logout.php');
        exit;
    }
}
?>

<?php
// include 'footer.php';
?>
