<?php
    require_once("helpers.php");
    header("Content-type: text/json");
    $_POST = json_decode((string) file_get_contents("php://input"), true);
    if(isset($_POST["action"]) && $_POST["action"] !== null){

    }else{
        echo '{"status": "error"}';
        exit;
    }
?>