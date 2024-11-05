<?php
session_start();
include 'header.php';
include 'menu.php';
include 'security.php';

$token = generateCsrfToken();
storeCsrfToken($conn,$token);

// Check if user is logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['userEmail'])) {
    $userId = htmlspecialchars($_SESSION['user_id']);
    $userEmail = htmlspecialchars($_SESSION['userEmail']);
// var_dump($userId);
    // Fetch tasks for the logged-in user
    $sqlTasks = "SELECT * FROM tasks WHERE user_id = ?";
    $stmtTasks = $conn->prepare($sqlTasks);
    $stmtTasks->bind_param("i", $userId);
    $stmtTasks->execute();
    $resultTasks = $stmtTasks->get_result();
    $tasks = $resultTasks->fetch_all(MYSQLI_ASSOC);
    
    // $resultTasks = mysqli_query($conn, $sqlTasks);
    // $tasks = mysqli_fetch_all($resultTasks, MYSQLI_ASSOC);

    // Fetch missions for the select dropdown
    $sqlMissions = "SELECT * FROM missions WHERE user_id = ?";
    $stmtMissions = $conn->prepare($sqlMissions);
    $stmtMissions->bind_param("i", $userId);
    $stmtMissions->execute();
    $resultMissions = $stmtMissions->get_result();
    $missions = $resultMissions->fetch_all(MYSQLI_ASSOC);

    // $resultMissions = mysqli_query($conn, $sqlMissions);
    // $missions = mysqli_fetch_all($resultMissions, MYSQLI_ASSOC);
} else {
    header('location: login.php');
    exit;
}

?>

<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

<br><br><br><br>
<div class="container" style="margin-left: 50px;">
    <div class="container alert alert-primary p-5 m-5">
        <h3 class="text-center">Votre Tâches</h3>

        <!-- Display user's tasks -->
        <div class="row">
            <div class="col-sm-12">
                <?php foreach ($tasks as $task): ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title"><strong>Le nom : </strong><?= htmlspecialchars($task['nom']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($task['description']) ?></p>
                            <p class="card-text"><strong>Résultat : </strong> <?= htmlspecialchars($task['resultat']) ?></p>
                            <p class="card-text"><strong>Priorité : </strong> <?= htmlspecialchars($task['priorite']) ?></p>

                            <!-- Button to associate the task with a mission -->
                            <button type="button" class="btn btn-warning" onclick="document.getElementById('associate-form-<?= $task['id'] ?>').style.display='block';">Associer à une mission</button>

                            <!-- Association Form -->
                            <div id="associate-form-<?= $task['id'] ?>" style="display:none; margin-top: 20px;">
                                <form method="POST">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <div class="form-group">
                                        <label for="mission_id">Sélectionnez une mission :</label>
                                        <select name="mission_id" class="form-control" required>
                                            <option value="">-- Choisir une mission --</option>
                                            <?php foreach ($missions as $mission): ?>
                                                <option value="<?= $mission['id'] ?>"><?= htmlspecialchars($mission['nom']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <input type="hidden" name="csrf_assosie" value="<?=$token?>">
                                    <div class="text-center">
                                        <button type="submit" name="associateTask" class="btn btn-primary">Associer</button>
                                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('associate-form-<?= $task['id'] ?>').style.display='none';">Annuler</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <?php
    // Handle task association with mission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['associateTask'])) {
        $csrf_assosie = $_POST['csrf_assosie'];
        if (verifyCsrfToken($conn,$csrf_assosie)) {

        $taskId = htmlspecialchars($_POST['task_id']);
        $missionId = htmlspecialchars($_POST['mission_id']);

        // Update task to associate with the selected mission
        $sqlUpdate = "UPDATE tasks SET mission_id = ? WHERE id = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ii", $missionId, $taskId);
        
        if ($stmtUpdate->execute()) {
            echo '<script type="text/javascript">alert("Tâche associée avec succès!");</script>';
        } else {
            echo '<script type="text/javascript">alert("Erreur lors de l\'association de la tâche.");</script>';
        }

        $stmtUpdate->close();

        // Reload the page to reflect changes
        // header('location: ' . $_SERVER['PHP_SELF']);
        // exit;
    }else{
        header('location: logout.php');
        exit;
    }
}
    ?>
</div>

<?php
include 'footer.php';
?>
