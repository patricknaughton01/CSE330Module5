<?php
    require_once("helpers.php");
    header("Content-type: text/json");
    $_POST = json_decode((string) file_get_contents("php://input"), true);
    if(isset($_POST["action"]) && $_POST["action"] !== null){
        if(csrf_valid($_POST)){
            switch($_POST["action"]){
                case "get-username":
                    if(isset($_SESSION["username"])){
                        printf('{"status": "success", "username":"%s", "user_id":"%s"}',
                               $_SESSION["username"], $_SESSION["user_id"]);
                    }else{
                        echo '{"status": "success", "username":null, "user_id": null}';
                    }
                    exit;
                    break;
                case "logout":
                    session_destroy();
                    echo '{"status": "success"}';
                    break;
                default:
                    echo '{"status": "error", "type":"no-action"}';
                    exit;
            }
        }else{
            echo '{"status":"error", "type":"csrf"}';
            exit;
        }
    }else{
        echo '{"status": "error", "type":"no-action"}';
        exit;
    }
?>