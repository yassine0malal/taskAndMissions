<?php
session_start();
include 'header.php';
include 'menu.php';
include 'security.php';


?>

<?php


if ($_SERVER['REQUEST_METHOD'] === 'POST' and empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {

    $csrf_token = $_POST['csrf_token'];
    if (verifyCsrfToken($conn,$csrf_token)) {

    $userEmail = $conn->real_escape_string($_POST['email']);
 
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();
    

    $csrf=$_POST['csrf_token'];
    
    // $sql1= "SELECT nom , UNIX_TIMESTAMP(created_at) AS time_in_seconds  FROM tokens";
    // $result1 = mysqli_query($conn, $sql1);
    // $datas = mysqli_fetch_all($result1, MYSQLI_ASSOC);

        // foreach($datas as $row){
        //     $rowGet= $row['nom']??'null';
        //     $time = $row['time_in_seconds']??0;
        //     // echo "ggg " . $rowGet." ggg <br>";
        // }
    // }
        // $currentTimeInSeconds = time();
        // $timOfSession = $currentTimeInSeconds - $time;
        
                // var_dump($time,$currentTimeInSeconds,$timOfSession);
                // exit();

                // $sqlrestet = "DELETE FROM tokens";
                // $resultreset = mysqli_query($conn, $sqlrestet);
                // unset($_SESSION['csrf_token']);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // var_dump($user);
        $stmt->close();
        if($user['etat'] != 1)
        {
            ?>
            <div class="alert alert-danger" role="alert">
            <?php
            echo "<span> Your account is not active yet. </span>";
            ?>
            </div>
            <?php
            
        }

        // Verify password and create session  and also verify csrf token
        if (password_verify($password, $user['password']) and $user['etat'] == 1 ) {
        
            // $_SESSION['depart']='active';

            $_SESSION['user_id'] = htmlspecialchars($user['id']);
            $_SESSION['userEmail'] = htmlspecialchars($user['email']);


            if($user['email'] === 'admine@gmail.com' and $user['droit'] === 'admin'){
                $_SESSION['userDroit'] = htmlspecialchars($user['droit']);
                header("Location: admin.php");
            }else{
                $_SESSION['userDroit'] = $user['droit'];
                header("Location: index.php");
            }
            
        }else{        
            header("location: login.php");
        }
    
    } else {
    ?>
    <div class="alert alert-danger">
    <?php
    echo "<span> User not found. </span>";
    header("location: login.php");
} 
?>
</div>

<?php
    }else{
        header("location: logout.php");
    }
}
   ?>




<?php

$page = '';
    // Récupérez la page demandée
    $allowed_pages = ['task', 'mission', 'index','user','link', 'operation','admin'];
    $page = isset($_GET['pp']) && in_array($_GET['pp'], $allowed_pages) ? $_GET['pp'] : 'index';

    switch ($page) {
        case 'task':
            header("location: task.php");
            break;
        case 'mission':
            header("location: mission.php");
            // include_once 'mission.php';
            break;
        case 'user':
            header('location: user.php');
            break;
        case 'link':
            header('location: linkTaskToMission.php');
            break;
        case 'operation':
            header('location: operation.php');
            break;
        case 'admin':
            header('location: admin.php');
            break;
        default:
            include_once 'index.php';
            break;
    }


include 'footer.php';
?>
