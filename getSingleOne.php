<?php
session_start();
include "header.php";
// include "menu.php";

if (isset($_SESSION['user_id']) and isset($_SESSION['userEmail'])) {
    $userId = $_SESSION['user_id'];
    $userEmail = $_SESSION['userEmail'];
} else {
    header("location: login.php");
    exit;
}

$sessionId = isset($_GET['id']) ? $_GET['id']:'';
if(isset($sessionId)){
    $sql = "SELECT * FROM missions WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii',$sessionId,$userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    // $stmt->close();

    $sql2  = "SELECT * FROM tasks WHERE mission_id = ? AND user_id = ?";
    $sstmt2 = $conn->prepare($sql2);
    $sstmt2->bind_param('ii', $sessionId, $userId);
    $sstmt2->execute();
    $result2 = $sstmt2->get_result();
    $row2 = $result2->fetch_all(MYSQLI_ASSOC);

    $sql3 = 'SELECT * FROM shared_mission WHERE mission_id = ?';
    $sstmt3 = $conn->prepare($sql3);
    $sstmt3->bind_param('i', $sessionId);
    $sstmt3->execute();
    $result3 = $sstmt3->get_result();
    $row3 = $result3->fetch_all(MYSQLI_ASSOC);
    // echo'<pre>';
    // var_dump($row3);
    // echo'</pre>';

    $sql22 = "SELECT * FROM operations WHERE missionID = ? ";
    $stmt22 = $conn->prepare($sql22);
    $stmt22->bind_param("i",$sessionId);
    $stmt22->execute();
    $result22 = $stmt22->get_result();
    $row22 = $result22->fetch_all(MYSQLI_ASSOC);
    //     echo'<pre>';
    // var_dump($row22);
    // echo'</pre>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
<br><br>
<div class="container mt-5 me-4">
        <div class="card">
        <div class="card-header"></div>
            <div class="card-body">
                <h5 class="card-title">Le nom de la mission : <span class="fw-bold text-success"><?=$row['nom']?></span></h5>
                <p class="card-text"><?=$row['description']?></p>
                <?php if(empty($row2)):?>
                    <div class="card-body">
                    <p class="card-text text-bg-warning rounded ">Pas de taches associe trouvee  </p>
                    </div>
                    <?php else: ?>
                        <h4 class="rounded text-bg-warning">Tasks ont trouvee </h4>
                <div  class="card text-start">
                    <?php foreach ($row2 as $ro): ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $ro['nom']; ?></h5>
                        <p class="card-text">Description : <?=$ro['description']?></p>
                        <p class="card-text">Preiorite : <?=$ro['priorite']?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?> 

                <div class="card-body">
                    <h4>Les membres qui ont modifier cette mission :</h4>
                    <?php if(empty($row22)):?>
                        <div class="card-body">
                            <p class="card-text text-bg-warning rounded ">Pas de membres trouvee  </p>
                        </div>
                        <?php else: ?>

                            <div class="card text-start">
                                <?php foreach ($row22 as $ro): ?>
                                <div class="card-body">
                                    <?php echo $ro['operation']; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>   
                            <?php endif; ?>
            </div>
            <a href="mission.php" class="btn btn-primary w-100">Back</a>
        </div>
<!-- 
        <div class="card">
        <div class="card-header"></div>
            <div class="card-body">
                <h5 class="card-title">Les operations sur les missions :</h5>
                <p class="card-text"> </p>
            </div>
        </div> -->

</div>
    
</body>
</html>

<?php
include "footer.php";
?> 