<?php
session_start();

include 'header.php';
include 'menu.php';
include "security.php";

$token = generateCsrfToken();
storeCsrfToken($conn,$token);

// Database connection assumed to be included

if (isset($_SESSION['user_id']) && isset($_SESSION['userEmail'])) {
    $userId = htmlspecialchars($_SESSION['user_id']);
    $userEmail = htmlspecialchars($_SESSION['userEmail']);

    // Fetch tasks for the logged-in user
    $sql = "SELECT * FROM tasks WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $datas = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch users to share tasks with (excluding the current user)
    $sqlUsers = "SELECT id, nom FROM users WHERE id != ?";
    $stmtUsers = $conn->prepare($sqlUsers);
    $stmtUsers->bind_param("i", $userId);
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
    $users = $resultUsers->fetch_all(MYSQLI_ASSOC);

   
} else {
    header("location: login.php");
    exit;
}
?>

<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>
<?php if(empty($_GET)): ?>
<div class="container">
    <div class="container-flixed alert alert-primary p-5 m-5">
            <form action="task.php" method="POST" class="m-5 p-5">
                <h3 class="text-center">Ajouter une Task</h3>
                <div class="form-group">
                    <input type="text" class="form-control" name="nom" placeholder="Entrer le nom de la tache" required>
                </div>
                <br><br>
                <input type="hidden" name="form_type" value="createTask">

                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Entrer la description de la tache" name="description" required>
                </div>
                <br><br>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Entrer le resultat de la tache" name="result" required>
                </div>
                <br><br>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Entrer la priorite de la tache" name="priorite" required>
                </div>
                <input type="hidden" name="csrf_token_create_task" value="<?=$token  ?>">
                <br><br>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary mb-2" name="createTask">Create</button>
                </div>
            </form>
    </div>


    <!-- Display user's tasks -->
    <div class="container-flixed alert alert-secondary p-5 m-5 text-center">
        <div class="row "style="margin-left: 330px;">
            <div class="col-sm-6">
                <?php foreach ($datas as $data): ?>
                    <div class="card">
                        <div class="card-body">
                            <a class="text-decoration-none" href="task.php?id=<?=$data['id']?>"><h5 class="card-title"><?= $data['nom'] ?></h5></a>
                            <p class="card-text"><?= $data['description']?></p>
                            <div class="text-center">
                                <form method="post">
                                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                    <input type="hidden" name="nn" value="<?= $data['nom'] ?>">
                                    <input type="hidden" name="csrf_token_supprimer" value="<?=$token?>">
                                    <button type="submit" class="btn btn-danger" name="envoyer">Supprimer</button>
                                    <button type="button" class="btn btn-warning" onclick="document.getElementById('edit-form-<?= $data['id'] ?>').style.display='block';">Modifie</button>
                                    <button type="button" class="btn btn-success" onclick="document.getElementById('share-form-<?= $data['id'] ?>').style.display='block';">Share</button>
                                </form>

                                <!-- Edit Form -->
                                <div id="edit-form-<?= $data['id'] ?>" style="display:none; margin-top: 20px;">
                                    <form method="POST">
                                        <input type="hidden" name="task_id" value="<?= $data['id'] ?>">
                                        <div class="form-group">
                                            <label for="nom">Nom</label>
                                            <input type="text" class="form-control" name="nom" value="<?= $data['nom'] ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="description">Description</label>
                                            <input type="text" class="form-control" name="description" value="<?= $data['description'] ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="result">Résultat</label>
                                            <input type="text" class="form-control" name="result" value="<?= $data['resultat'] ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="priorite">Priorité</label>
                                            <input type="text" class="form-control" name="priorite" value="<?= $data['priorite'] ?>" required>
                                        </div>
                                        <input type="hidden" name="csrf_token_edit_task" value="<?=$token?>">
                                        <div class="text-center">
                                            <button type="submit" name="updateTask" class="btn btn-success">Enregistrer</button>
                                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('edit-form-<?= $data['id'] ?>').style.display='none';">Annuler</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Share Form -->
                                <div id="share-form-<?= $data['id'] ?>" style="display:none; margin-top: 20px;">
                                    <form method="POST">
                                        <input type="hidden" name="task_id" value="<?= $data['id'] ?>">
                                        <div class="form-group">
                                            <label for="shared_user_id">Sélectionner un utilisateur pour partager :</label>
                                            <select name="shared_user_id" class="form-control" required>
                                                <option value="">-- Choisir un utilisateur --</option>
                                                <?php foreach ($users as $user): ?>
                                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nom']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="access_level">Droit d'accès :</label>
                                            <select name="access_level" class="form-control" required>
                                                <option value="view">Consultation</option>
                                                <option value="edit">Modification</option>
                                            </select>
                                        </div>
                                        <input type="hidden" name="csrf_token_share_task" value="<?=$token?>">
                                        <div class="text-center">
                                            <button type="submit" name="shareTask" class="btn btn-primary">Partager</button>
                                            <button type="button" class="btn btn-secondary" onclick="document.getElementById('share-form-<?= $data['id'] ?>').style.display='none';">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php else: ?>

<?php 
    $taskId = isset($_GET['id']) ? $_GET['id']:'';
    if(isset($taskId)){

        $sql222 = "SELECT * FROM tasks WHERE id = ? and user_id = ?";
        $stmt222 = $conn->prepare($sql222);
        $stmt222->bind_param('ii',$taskId,$userId);
        $stmt222->execute();
        $result222 = $stmt222->get_result();
        $row222 = $result222->fetch_assoc();
        if(empty($row222)){
        $row222['error'] = "this is an invalid id please don't play with this things ";
        }

        $sql22 = "SELECT * FROM operations WHERE taskID = ? ";
        $stmt22 = $conn->prepare($sql22);
        $stmt22->bind_param("i",$taskId);
        $stmt22->execute();
        $result22 = $stmt22->get_result();
        $row22 = $result22->fetch_all(MYSQLI_ASSOC);
    }    
?>
    <br><br>
<div class="container mt-5 me-1 ">
        <div class="card">
        <div class="card-header"></div>
            <div class="card-body">
                <?php if(!isset($row222['error'])):?>
                <h5 class="card-title">Le nom de la mission : <span class="fw-bold text-success"><?=$row222['nom']?></span></h5>
                <p class="card-text"> <strong>Description</strong> : <?=$row222['description']?></p>
                <p class="card-text"> <strong>resultat</strong> : <?=$row222['resultat']?></p>
                <p class="card-text"> <strong>priorite</strong> : <?=$row222['priorite']?></p>
                <?php else: ?>
                <p><?=$row222['error'];?></p>
                <?php endif;?>
            </div>
            <hr>
            <div class="card-body">
                <h4>les operations sur cette task </h4>
                <?php if(empty($row22)):?>
                    <h6>il y' a pas d'operations</h6>
                <?php else:?>
                <ul>
                <?php foreach($row22 as $value):?>
                <li> <?=$value['operation'] ?></li>
                <?php endforeach;?>
                </ul>
                <?php endif;?>
            </div>
            <a href="task.php" class="btn btn-primary">Back</a>
        </div>
</div>

<?php endif; ?>

    <?php
    // Handle task creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (isset($_POST['createTask']) && $_POST['form_type'] === 'createTask') {
            $csrf_token_create_task = $_POST['csrf_token_create_task'];
            if (verifyCsrfToken($conn,$csrf_token_create_task)) {

            
            $nom = htmlspecialchars($_POST['nom'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
            $result = htmlspecialchars($_POST['result'], ENT_QUOTES, 'UTF-8');
            $priorite = htmlspecialchars($_POST['priorite'], ENT_QUOTES, 'UTF-8');

            // Insert task
            $sql = "INSERT INTO tasks (nom, description, resultat, priorite, user_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nom, $description, $result, $priorite, $userId);
            $stmt->execute();
            $stmt->close();

            $operation = 'create new task that called ' . $nom;
            $sql22 = "INSERT INTO operations (user_id ,operation ) VALUES (?,?) ";
            $stmt22 = $conn->prepare($sql22);
            $stmt22->bind_param("is", $userId, $operation);
            $stmt22->execute();
            $stmt22->close();
            
            
            
        }else{
            header("loction: logout.php");
            exit;
        }
    }

        // Handle task updating
        if (isset($_POST['updateTask'])) {

            $csrf_token_edit_task = $_POST['csrf_token_edit_task'];
            if (verifyCsrfToken($conn,$csrf_token_edit_task)) {
                
            $taskId = $_POST['task_id'];
            $nom = htmlspecialchars($_POST['nom'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8');
            $result = htmlspecialchars($_POST['result'], ENT_QUOTES, 'UTF-8');
            $priorite = htmlspecialchars($_POST['priorite'], ENT_QUOTES, 'UTF-8');

            $operation = 'update task to  ' . $nom;
            $sql22 = "INSERT INTO operations (user_id ,operation ) VALUES (?,?) ";
            $stmt22 = $conn->prepare($sql22);
            $stmt22->bind_param("is", $userId, $operation);
            $stmt22->execute();
            $stmt22->close();

            // Update task
            $sqlUpdate = "UPDATE tasks SET nom = ?, description = ?, resultat = ?, priorite = ? WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ssssi", $nom, $description, $result, $priorite, $taskId);
            $stmtUpdate->execute();
            $stmtUpdate->close();

          
        }else{
            header("location: logout.php");
            exit;
        }
    }

        // Handle task sharing (Insert or Update)
        if (isset($_POST['shareTask'])) {
            $csrf_token_share_task = $_POST['csrf_token_share_task'];
            if (verifyCsrfToken($conn,$csrf_token_share_task)) {

            $taskId = $_POST['task_id'];
            $sharedUserId = $_POST['shared_user_id'];
            $accessLevel = $_POST['access_level'];

            // Check if the task has already been shared with this user
            $sqlCheck = "SELECT * FROM shared_tasks WHERE task_id = ? AND user_partage_id = ?";
            $stmtCheck = $conn->prepare($sqlCheck);
            $stmtCheck->bind_param("ii", $taskId, $sharedUserId);
            $stmtCheck->execute();
            $resultCheck = $stmtCheck->get_result();

            if ($resultCheck->num_rows > 0) {
                // Task already shared, update the access level
                $sqlUpdate = "UPDATE shared_tasks SET droit = ? WHERE task_id = ? AND user_partage_id = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                $stmtUpdate->bind_param("sii", $accessLevel, $taskId, $sharedUserId);
                $stmtUpdate->execute();
                $stmtUpdate->close();
                // register the operation 
                $operation = 'update sharing task to id  ' . $sharedUserId;
                $sql22 = "INSERT INTO operations (user_id ,operation ) VALUES (?,?) ";
                $stmt22 = $conn->prepare($sql22);
                $stmt22->bind_param("is", $userId, $operation);
                $stmt22->execute();
                $stmt22->close();
                echo '<script type="text/javascript">alert("Les droits d\'accès ont été mis à jour avec success!");</script>';
            } else {
                // Task not yet shared, insert a new record
                $operation = 'share task user  ' . $userEmail . ' to user id ' . $sharedUserId;
                $sql22 = "INSERT INTO operations (user_id ,operation ) VALUES (?,?) ";
                $stmt22 = $conn->prepare($sql22);
                $stmt22->bind_param("is", $userId, $operation);
                $stmt22->execute();
                $stmt22->close();

                $sqlShare = "INSERT INTO shared_tasks (task_id, user_partage_id, droit) VALUES (?, ?, ?)";
                $stmtShare = $conn->prepare($sqlShare);
                $stmtShare->bind_param("iis", $taskId, $sharedUserId, $accessLevel);
                $stmtShare->execute();
                $stmtShare->close();
                echo '<script type="text/javascript">alert("Les droits d\'accès ont été partager avec success!");</script>';
            }

            $stmtCheck->close();
            
           
        }else{
            // header('location: logout.php');
            unset($_SESSION['user_id']);
            die("connection fialed");
            exit;
        }
    }
    }

    // Handle task deletion
    if (isset($_POST['envoyer'])) {
        $csrf_token_supprimer_task = $_POST['csrf_token_supprimer'];
        if (verifyCsrfToken($conn,$csrf_token_supprimer_task)) {
        $id = $_POST['id'];
        $nomc = $_POST['nn'];

        $sql = "DELETE FROM tasks WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $operation = 'deleted task  that called  ' . $nomc;
        $sql22 = "INSERT INTO operations (user_id ,operation ) VALUES (?,?) ";
        $stmt22 = $conn->prepare($sql22);
        $stmt22->bind_param("is", $userId, $operation);
        $stmt22->execute();
        $stmt22->close();
    }else{
        header("location: logout.php");
        exit();
    }
}
    ?>
</div>

<?php
include 'footer.php';
?>


 