<?php
session_start();
include 'header.php';
include 'menu.php';
?>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = $conn->real_escape_string($_POST['email']);
    if(!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        header("location: login.php");
    }
    $password = htmlspecialchars($_POST['password'], ENT_QUOTES, 'UTF-8');

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    $csrf=$_POST['csrf_token'];
    // var_dump($csrf);
    // var_dump("compare");

    $sql1= "SELECT nom , UNIX_TIMESTAMP(created_at) AS time_in_seconds FROM tokens";
    $result1 = mysqli_query($conn, $sql1);
    $datas = mysqli_fetch_all($result1, MYSQLI_ASSOC);
        foreach($datas as $row){
            $rowGet= $row['nom'];
            $time = $row['time_in_seconds'];
        }

        // var_dump($time);
        $currentTimeInSeconds = time();
        $timOfSession = $currentTimeInSeconds - $time;
        // var_dump($currentTimeInSeconds);
        // var_dump($timOfSession);
    
        

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
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
        if (password_verify($password, $user['password']) and $user['etat'] == 1 and $csrf === $rowGet  and $timOfSession < 10) {
            $sqlrestet = "DELETE FROM tokens";
            $resultreset = mysqli_query($conn, $sqlrestet);
            $_SESSION['depart']='active';

            $_SESSION['user_id'] = htmlspecialchars($user['id']);
            $_SESSION['userEmail'] = htmlspecialchars($user['email']);
            unset($_SESSION['csrf_token']);

            if($user['email'] === 'admine@gmail.com' and $user['droit'] === 'admin'){
                $_SESSION['userDroit'] = htmlspecialchars($user['droit']);
                header("Location: admin.php");
            }else{
                $_SESSION['userDroit'] = $user['droit'];
                // header("Location: index.php");
            }
            
        } else if($_SESSION['depart'] === 'active'){  
            header("location: index.php");
        }
        else{        
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
}
   ?>




<?php
// Vérifiez si l'utilisateur est connecté
// if (isset($_SESSION['user_id']) && isset($_SESSION['userEmail'])) {
//     $userId = $_SESSION['user_id'];
//     $userEmail = $_SESSION['userEmail'];
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
            include_once 'user.php';
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
// }else
// {
//     header("location: login.php");
//     exit;
// }

include 'footer.php';
?>
