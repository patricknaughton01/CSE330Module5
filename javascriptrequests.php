<?php
    require_once("helpers.php");
    header("Content-type: text/json");
    $_POST = json_decode((string) file_get_contents("php://input"), true);
    if(isset($_POST["action"]) && $_POST["action"] !== null){
        if(csrf_valid($_POST)){

        }else{
            echo '{"status":"error", "type":"csrf"}';
            exit;
        }
    }else{
        echo '{"status": "error", "type":"no-action"}';
        exit;
    }
?>