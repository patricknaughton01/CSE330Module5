<?php
    require_once("helpers.php");
    header("Content-type: text/json");
    $_POST = json_decode((string) file_get_contents("php://input"), true);
    if(isset($_POST["action"]) && $_POST["action"] !== null){
        if(csrf_valid($_POST)){
            switch($_POST["action"]){
                case "get-username":
                    if(check_user($_POST) === "success"){
                        printf('{"status": "success", "username":"%s", "user_id":"%s"}',
                               $_SESSION["username"], $_SESSION["user-id"]);
                    }else{
                        echo '{"status": "success", "username":null, "user_id": null}';
                    }
                    exit;
                    break;
                case "register-user":
                    $username = (string) validate($_POST, "username");
                    if(!username_valid($username)){
                        echo '{"status": "error", "type": "registration", "message":"username-invalid"}';
                        exit;
                    }
                    $password = (string) validate($_POST, "password");
                    $cpassword = (string) validate($_POST, "cpassword");
                    $status = register($username, $password, $cpassword);
                    if($status === "success"){
                        echo '{"status": "success"}';
                    }else{
                        printf( '{"status": "error",
                        "type": "registration",
                        "message": "failed-to-register: %s",}', $status);
                    }
                    exit;
                    break;
                case "log-user-in":
                    $username = (string) validate($_POST, "username");
                    $password = (string) validate($_POST, "password");
                    if(!username_valid($username)){
                        echo '{"status": "error", "type":"login", "message": "username-invalid"}';
                        exit;
                    }
                    $status = check_user($_POST);
                    if($status === "success"){
                        echo '{"status": "success"}';
                        exit;
                    }else{
                        printf('{"status": "error", "type":"login", "message": "failed-login: %s"}', $status);
                    }
                    exit;
                    break;
                case "logout":
                    session_destroy();
                    session_start();
                    if(!isset($_SESSION["csrf"])){
                        $_SESSION["csrf"] = bin2hex(openssl_random_pseudo_bytes(32));
                    }
                    printf( '{"status": "success", "new-csrf": "%s"}', $_SESSION["csrf"]);
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