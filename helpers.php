<?php
    session_start();
    if(!isset($_SESSION["csrf"])){
        $_SESSION["csrf"] = bin2hex(openssl_random_pseudo_bytes(32));
    }


    //******************Database**************************//
    /*
     * Connect to the news database and output an error if there is one.
     *
     * @return mysqli connection to the news database.
     */
    function connect(){
        $uname_path = "/etc/330/calendar330/unrelated_info1";
        $pword_path = "/etc/330/calendar330/unrelated_info2";
        $uname = trim(file_get_contents($uname_path));
        $pword = trim(file_get_contents($pword_path));
        $mysqli = new mysqli('localhost', $uname, $pword, 'calendar');
        if($mysqli->connect_errno){
            printf("MySQL connection error: %s\n", $mysqli->connect_errno);
            exit;
        }
        return $mysqli;
    }
?>