<?php
session_start();
session_unset();
unset($_SESSION['csrf_token']);
session_destroy();
header("Location: login.php");
exit();
?>
