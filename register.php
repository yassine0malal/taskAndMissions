
<?php
session_start();
    include 'header.php';
    if (empty($_SESSION['csrf_token']) ) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        
        $cle = $_SESSION['csrf_token'];
        echo $_SESSION['csrf_token'] ;
}
    ?>
    <style>
        *{
            padding: 0px;
            margin: 0px;
            font-family: sans-serif;
        }
        .form-warp{
            width: 320px;
            background-color:#3E3E3E;
            padding: 40px 20px;
            box-sizing: border-box;
            position:fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%,-50%);
            border-radius: 6px;
        }
        h1{
            text-align: center;
            color: #fff;
            font-weight: normal;
        }
        input{
            width: 100%;
            background: none;
            border: 1px solid #fff;
            border-radius: 3px;
            padding: 6px 20px;
            box-sizing:border-box;
            margin-bottom:20px ;
            font-size: 16px;
            margin: 10px auto;
            padding: 10px;
            color: #fff;
        }
        input[type="submit"]{
            background: #F1B253;
            border: 0;
            cursor: pointer;
        }
        input[type="submit"]:hover{
            background:#FD9101 ;
        }
        span{
            color: crimson;
            font-size: 18px;
            display: block;
            margin-bottom: 10px;
            text-align: center;
            margin: 5px auto;
        }
    </style>

    <div class="form-warp">
        <form action="" method="post">
            <h1>Sign Up</h1>
            <input type="text" name="nom" placeholder="entrer le nom ">
            <input type="text" name="email" placeholder=" entrer l'email ">
            <input type="password" name="password" placeholder=" entrer le mot de passe ">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">
            <input type="submit" value="Sign Up" name="signUp">
            <a href="login.php" class="btn btn-success w-100 ">Log In</a>
        </form>
    </div>

    <?php
    if ($_SERVER['REQUEST_METHOD']==='POST'){
        $csrf = $_POST['csrf_token'];
        // var_dump($csrf);

     
        $nom = htmlspecialchars(trim($_POST['nom']), ENT_QUOTES, 'UTF-8');
        $email = htmlspecialchars(trim($_POST['email']), ENT_QUOTES, 'UTF-8');
        $password = $_POST['password'];
        
        $errors = [];   
        if (empty($nom)){
            $errors[] = "le prenom est obligatoire";
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "A valid email is required.";
        }else{
            $sql = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if (mysqli_num_rows($result) > 0) {
                $errors[] = "This email is already registered.";
            }
        }

        if (empty($password)){
            $errors[] = "le mot de passe est obligatoire";
        }
        if (empty($errors)) {
            $droit = "user";
            unset($_SESSION['csrf_token']);
            unset($csrf);
            $password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (nom, email, password,etat,droit) VALUES ('$nom', '$email', '$password','desactiver','$droit')";
            $result = mysqli_query($conn, $sql);
            if ($result){
                header("Location: login.php");
            }
        }else{
            ?>
            <div class="alert alert-danger" role="alert">
            <?php
            foreach ($errors as $error){
                echo "<span>$error</span>";
            }
            ?>
            </div>
            <?php
        }
    }

    ?>

  

