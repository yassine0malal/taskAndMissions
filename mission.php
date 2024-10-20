<?php 
session_start();
include_once 'header.php';
include_once 'menu.php';

// Database connection assumed to be included

if (isset($_SESSION['user_id']) and isset($_SESSION['userEmail'])) {
    $userId = $_SESSION['user_id'];
    $userEmail = $_SESSION['userEmail'];
} else {
    header("location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['createMiss'])) {
        $nom = htmlspecialchars($_POST['nom']);
        $description =htmlspecialchars($_POST['description']);

        $sql = "INSERT INTO missions (nom, description, user_id) VALUES (?,?,?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nom, $description, $userId);
        $result = $stmt->execute();
        if ($result) {
            header("location: mission.php");
        } else {
            var_dump("Error: " . $sql . "<br>" .$conn->error);
        }
    }

    // If the share form is submitted
    if (isset($_POST['shareMission'])) {
        $missionId = htmlspecialchars($_POST['mission_id']); // The mission to be shared
        $sharedUserId = htmlspecialchars($_POST['shared_user_id']); // The user selected to share the mission with
        $droit = htmlspecialchars($_POST['access_level']); // Access level: 'view' or 'edit'

        // Check if the mission is already shared with this user
        $sqlCheck = "SELECT * FROM shared_mission WHERE mission_id = ? AND user_partage_id = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("ii", $missionId, $sharedUserId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if (mysqli_num_rows($resultCheck) > 0) {
            // If already shared, update the access level
            $sqlUpdate = "UPDATE shared_mission SET droit='$droit' WHERE mission_id= ? AND user_partage_id= ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ii", $missionId, $sharedUserId);
            $stmtUpdate->execute();
            $resultUpdate = $stmtUpdate->get_result();
            if ($resultUpdate) {
                // header("location: mission.php");
                $stmtUpdate->close();
                echo '<script type="text/javascript">alert("Les droits d\'accès ont été effecter avec success!");</script>';
            } else {
                var_dump("Erreur lors de la mise à jour des droits: " . $conn->error);
            }
        } else {
            // If not already shared, insert a new record
            $sqlShare = "INSERT INTO shared_mission (mission_id, user_partage_id, droit) VALUES (?, ?,?)";
            $stmtShare = $conn->prepare($sqlShare);
            $stmtShare->bind_param("iis", $missionId, $sharedUserId, $droit);
            $stmtShare->execute();
            $stmtShare->close();
            $resultShare = $stmtShare->get_result();
            if ($resultShare) {
                header("location: mission.php");
            } else {
                var_dump("Erreur lors du partage de la mission: " . $conn->error);
            }
        }
    }
}

// Select user missions
$sql2 = "SELECT * FROM missions WHERE missions.user_id = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $userId);
$stmt2->execute();
$result2 = $stmt2->get_result();
$datas2 = mysqli_fetch_all($result2, MYSQLI_ASSOC);
// $result2 = mysqli_query($conn, $sql2);
// $datas2 = mysqli_fetch_all($result2, MYSQLI_ASSOC);

// Fetch users for sharing
$sqlUsers = "SELECT id, nom FROM users WHERE id !=?"; // Assuming you have a 'users' table
$stmtUsers = $conn->prepare($sqlUsers);
$stmtUsers->bind_param("i", $userId);
$stmtUsers->execute();
$resultUsers = $stmtUsers->get_result();
$users = mysqli_fetch_all($resultUsers, MYSQLI_ASSOC);
// $resultUsers = mysqli_query($conn, $sqlUsers);
// $users = mysqli_fetch_all($resultUsers, MYSQLI_ASSOC);

// If the modify form is submitted
if (isset($_POST['modifierMission'])) {
    $id = htmlspecialchars($_POST['id']);
    $nom = htmlspecialchars($_POST['nom']);
    $description = htmlspecialchars($_POST['description']);

    $sqlUpdate = "UPDATE missions SET nom= ?, description= ? WHERE id=?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ssi", $nom, $description, $id);
    $resultUpdate = $stmtUpdate->execute();
    $stmtUpdate->close();

    // $resultUpdate = mysqli_query($conn, $sqlUpdate);

    if ($resultUpdate) {
        header("location: mission.php");
    } else {
        var_dump("Error: " . $sqlUpdate . "<br>" . $conn->error);
    }
}

?>
<div class="container">
    <div class="container-flixed alert alert-primary p-5 m-5">
        <!-- <div class="alert alert-success" role="alert"> -->
            <form method="POST" class="m-5 p-5">
                <h3 class="text-center">Ajouter une Mission</h3>
                <div class="form-group">
                    <input type="text" class="form-control" name="nom" placeholder="Entrer le nom de la mission" required>
                </div>
                <br><br>
                <div class="form-group">
                    <input type="text" class="form-control" placeholder="Entrer la description de la mission" name="description" required>
                </div>
                <br><br>
                <div class="text-center">
                    <button type="submit" name="createMiss" class="btn btn-primary mb-2">Create</button>
                </div>
            </form>
        <!-- </div> -->
    </div>

    
    <!-- Display missions -->
     <h3 class="text-center text-primary" style="color:#0865C3;">Les Missions ont Deja lancer</h3>
    <div class="container-flixed alert alert-warning p-5 m-5">
        <?php foreach ($datas2 as $data2): ?>
            <!-- <div class="card"> -->
                <div class="card-body">
                    <h5 class="card-title text-center">La Mission : <?= $data2['nom'] ?></h5>
                    <p class="card-text text-center"><?= $data2['description'] ?></p>

                    <!-- Display Modify Form if 'modifier' button is clicked -->
                    <?php if (isset($_POST['modifier']) && $_POST['id'] == $data2['id']): ?>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="id" value="<?= $data2['id'] ?>">
                            <div class="form-group">
                                <label for="nom">Nom</label>
                                <input type="text" class="form-control" name="nom" value="<?= $data2['nom'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <input type="text" class="form-control" name="description" value="<?= $data2['description'] ?>" required>
                            </div>
                            <div class="text-center">
                                <button type="submit" name="modifierMission" class="btn btn-success">Enregistrer</button>
                                <button type="button" class="btn btn-secondary" onclick="window.location.href='mission.php'">Annuler</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <form action="" method="post" class="text-center ">
                            <input type="hidden" name="id" value="<?= $data2['id'] ?>">
                            <button type="submit" class="btn btn-danger me-4" name="supprimer">Supprimer</button>
                            <button type="submit" class="btn btn-warning me-4" name="modifier">Modifier</button>
                            <button type="button" class="btn btn-success me-4" onclick="document.getElementById('share-form-<?= $data2['id'] ?>').style.display='block';">Share</button>
                        </form>

                        <!-- Share Form -->
                        <div id="share-form-<?= $data2['id'] ?>" style="display:none; margin-top: 20px;">
                            <form method="POST">
                                <input type="hidden" name="mission_id" value="<?= $data2['id'] ?>">
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
                                <div class="text-center">
                                    <button type="submit" name="shareMission" class="btn btn-primary">Partager</button>
                                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('share-form-<?= $data2['id'] ?>').style.display='none';">Annuler</button>
                                </div>
                            </form>
                        </div>

                    <?php endif; ?>
                </div>
            <!-- </div> -->
             <br>
             <hr>
             <br>
        <?php endforeach; ?>
    </div>
</div>

<?php
// Delete Mission
if (isset($_POST['supprimer'])) {
    $id = $_POST['id'];
    $sql5 = "DELETE FROM missions WHERE id = ?";
    $stmt = $conn->prepare($sql5);
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    if ($result) {
        // header("location: mission.php");
    } else {
        var_dump("Error: " . $sql5 . "<br>" . $stmt->error);;
    }
}
include('footer.php');
?>
