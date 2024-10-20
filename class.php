<?php
if (isset($_POST["logIn"]))
{
    $login = $_POST["email"];
    $password = $_POST["password"];

    function login ($login, $password)
    {
        if ($login  === '')
        {
            echo'<script> alert("Veuillez entrer votre login"); </script>';

        }else
        {
            $str = ";!?><#:|$%^&*(){}[]'\/,";
            for($i=0 ; $i<strlen($login); $i++){
                for($j=0 ; $j<strlen($str); $j++){
                if($login[$i] === $str[$j]){
                    echo'<script> alert("il y a la chaine de caractere  (' .$login[$i].')");</script>';
                    break;
                }
            }
        }
    }
}
    login($login, $password);

    $sql= "SELECT users.email,users.password FROM users WHERE email = '$login'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    if($result->num_rows>0){
        $sql = "SELECT users.esm";
    }

}
?>