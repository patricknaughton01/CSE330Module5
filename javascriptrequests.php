<?php
    require_once("helpers.php");
    header("Content-type: text/json");
    $_POST = json_decode((string) file_get_contents("php://input"), true);
    if(isset($_POST["action"]) && $_POST["action"] !== null){
        if(csrf_valid($_POST)){
            switch($_POST["action"]){
                case "create-event":
                    if(check_user($_POST) !== "success"){
                        echo '{"status":"error", "type": "create-event", "message": "failed-login"}';
                        exit;
                    }
                    $title = (string) validate($_POST, "title");
                    $location = (string) validate($_POST, "location");
                    $description = (string) validate($_POST, "description");
                    $start_time = (string) validate($_POST, "start-time");
                    $end_time = (string) validate($_POST, "end-time");
                    printf('{"status":"success",
                           "title":"%s",
                           "location":"%s",
                           "description":"%s",
                           "start-time":"%s",
                           "end-time":"%s"}',
                           $title, $location, $description, $start_time, $end_time);
                    exit;
                    break;
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
                case "get-events":
                    if(check_user($_POST) !== "success"){
                        echo '{"status": "error", "type":"get-events", "message":"failed-login"}';
                        exit;
                    }
                    $month = (int) validate($_POST, "month");
                    $year = (int) validate($_POST, "year");
                    $min_month = $month-1;
                    $max_month = $month + 1;
                    $min_time_str = ((string)$year).'-'.((string)$min_month).'01 00:00:00';
                    $tmp = ((string)$year).'-'.((string)$max_month).'-01';
                    $max_time_str =
                        ((string)$year).'-'.((string)$max_month).'-'.date('t', strtotime($tmp)).' 23:59:59';
                    $sqli = connect();
                    $stmt = prepare_query($sqli, "select
                            id, title, start_time, end_time, description, location from
                            events where user_id=? and ((start_time between ? and ?)
                            or (end_time between ? and ?))");
                    $stmt->bind_param("sssss", $_SESSION["user-id"],
                                      $min_time_str, $max_time_str,
                                      $min_time_str, $max_time_str);
                    $stmt->execute();
                    $stmt->bind_result($id, $title, $start_time, $end_time, $desc, $location);
                    $r_str = '{"status":"success", "events":{';
                    $count = 0;
                    while($stmt->fetch()){
                        if($count > 0){
                            $r_str = $r_str.',';
                        }
                        $r_str = $r_str.sprintf('"%d":{
                                          "title":"%s",
                                          "start_time":"%s",
                                          "end_time":"%s",
                                          "desc":"%s",
                                          "location":"%s"}',
                                          $id, $title, $start_time,
                                          $end_time, $desc, $location);
                        $count++;
                    }
                    $r_str = $r_str."}}";
                    echo $r_str;
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