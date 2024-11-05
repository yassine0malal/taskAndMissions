<?php
    session_start();
    include 'header.php';
    include 'security.php';

$token = generateCsrfToken();
storeCsrfToken($conn,$token);

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
        <form  method="post" action="index.php">
            <h1>Log In</h1>
            <input type="hidden" name="csrf_token" value="<?= $token; ?>">

            <input type="text" name="email" placeholder=" entrer l'email ">
            <input type="password" name="password" required placeholder=" entrer le mot de passe ">
            <input type="submit" value="Log In"  name="logIn">
            <p style="color:white">you don't have an account ?  &nbsp;<a href="register.php" required name="SignUp" name="signUp">Sign Up</a></p>
        </form>
    </div>

   
